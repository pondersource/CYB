<?php

namespace App\Connectors\LetsPeppol;

use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Crypt\{RSA, Random};
use phpseclib3\File\X509;

class KeyStore
{
    public const NAME = 'Let\'s Peppol';
    
    private const IDENTITY_FILE = __DIR__.'/AS4DirectIdentity.json';
	private const KEYSTORE_FILE = __DIR__.'/AS4DirectIdentity.p12';
    private const KEYSTORE_PASSWORD = 'keystorefilemustbeinaccessible';
    
    private Settings $settings;

    public function __construct()
    {
        $this->settings = new Settings(self::IDENTITY_FILE);
    }

    public function getInfo(): array
    {
        $this->ensureIdentity();

        return [
            'identity_scheme' => $this->settings['identity_scheme'],
            'identity_value' => $this->settings['identity_value'],
            'certificate' => $this->settings['certificate'],
        ];
    }

    public function issueCertificate(string $name, string $public_key): string
    {
        $private_key = $this->getPrivateKey();
        $public_key = RSA::loadPublicKey($public_key);
        
        $subject = new X509();
		$subject->setPublicKey($public_key);
		$subject->setDN('/CN='.$name);

		$issuer = new X509();
		$issuer->setPrivateKey($private_key);
		$issuer->setDN('/CN='.self::NAME);

		$x509 = new X509();
		$result = $x509->sign($issuer, $subject); 
		$certificate = $x509->saveX509($result);

        return $certificate;
    }

    public function getCertificate(): X509
    {
        $this->ensureIdentity();

        $cert = new X509();
		$cert->loadX509($this->settings['certificate']);

        return $cert;
    }

    public function getPrivateKey(): AsymmetricKey
    {
        $this->ensureIdentity();

        $key_store_content = file_get_contents(self::KEYSTORE_FILE);

        if (!openssl_pkcs12_read($key_store_content, $cert_info, self::KEYSTORE_PASSWORD)) {
            throw new \Exception('Error: Unable to read the key store.');
        }

        $private_key = RSA::loadPrivateKey($cert_info['pkey']);

        return $private_key;
    }

    private function ensureIdentity()
    {
        if (!isset($this->settings['certificate'])) {
            $this->generateIdentity();
        }
    }

    private function generateIdentity()
    {
		$privateKey = RSA::createKey(2048)->withPadding(RSA::ENCRYPTION_OAEP);
		$publicKey = $privateKey->getPublicKey();
		
		$subject = new X509();
		$subject->setPublicKey($publicKey);
		$subject->setDN('/CN='.self::NAME);

		$issuer = new X509();
		$issuer->setPrivateKey($privateKey);
		$issuer->setDN('/CN='.self::NAME);

		$x509 = new X509();
		$result = $x509->sign($issuer, $subject); 
		$certificate = $x509->saveX509($result);

		$keystore_content = null;

		if (!openssl_pkcs12_export($certificate, $keystore_content, $privateKey->__toString(), self::KEYSTORE_PASSWORD)) {
			throw new Exception("Error Processing Request", 1);
		}

        file_put_contents(self::KEYSTORE_FILE, $keystore_content);

        $this->settings['identity_scheme'] = 'iso6523-actorid-upis';
        $this->settings['identity_value'] = uniqid('as4direct-');
        $this->settings['certificate'] = $certificate;
        $this->settings->save();
	}
    
    public static function verify(string $public_key, string $message, string $signature): bool
    {
        return self::publicKeyFromString($public_key)->verify($message, $signature);
    }

    public static function publicKeyFromString(string $public_key): AsymmetricKey
    {
        return RSA::loadPublicKey($public_key);
    }

    public static function certificateFromString(string $certificate): X509
    {
        $cert = new X509();
		$cert->loadX509($certificate);

        return $cert;
    }
}