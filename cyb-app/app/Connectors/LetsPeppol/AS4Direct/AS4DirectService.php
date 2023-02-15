<?php

namespace App\Connectors\LetsPeppol\AS4Direct;

use App\Connectors\LetsPeppol\KeyStore;
use App\Connectors\LetsPeppol\LetsPeppolService;
use App\Connectors\LetsPeppol\Models\Identity;
use App\Core\Helper;
use App\Core\Settings;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerBuilder;
use OCA\PeppolNext\PonderSource\EBBP\MessagePartNRInformation;
use OCA\PeppolNext\PonderSource\EBMS\MessageInfo;
use OCA\PeppolNext\PonderSource\EBMS\Messaging;
use OCA\PeppolNext\PonderSource\EBMS\Receipt;
use OCA\PeppolNext\PonderSource\EBMS\SignalMessage;
use OCA\PeppolNext\PonderSource\Envelope\Body;
use OCA\PeppolNext\PonderSource\Envelope\Envelope;
use OCA\PeppolNext\PonderSource\Envelope\Header;
use OCA\PeppolNext\PonderSource\SBD\StandardBusinessDocument;
use OCA\PeppolNext\PonderSource\WSSec\CanonicalizationMethod\C14NExclusive;
use OCA\PeppolNext\PonderSource\WSSec\DigestMethod\SHA256;
use OCA\PeppolNext\PonderSource\WSSec\DSigReference;
use OCA\PeppolNext\PonderSource\WSSec\Security;
use OCA\PeppolNext\PonderSource\WSSec\SignatureMethod\RsaSha256;
use OCA\PeppolNext\PonderSource\WSSec\Transform;

Helper::include_once(__DIR__.'/../PonderSource');

class AS4DirectService
{
	private KeyStore $keyStore;

	public function __construct()
    {
        $this->keyStore = new KeyStore();
    }

    public function getInfo(): array
    {
		$info = $this->keyStore->getInfo();

		$info['endpoint'] = route('connector.lets_peppol.as4-direct.endpoint');

        return info;
    }

    public function endpointMessage(Request $request)
    {
        list($raw_envelope, $raw_payload) = $this->getEnvelopeAndPayload($request);
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
		$envelope = $serializer->deserialize($raw_envelope, 'OCA\PeppolNext\PonderSource\Envelope\Envelope::class', 'xml');

		list($sender, $receiver) = $envelope->getHeader()->getMessaging()->getUserMessage()->getPeppolSenderAndReceiver();

		if ($sender == null || $receiver == null) {
            throw new \Exception('Sender or receiver not specified');
		}

		$sender_identity = null;

        try {
            $sender_identity = Identity::query()
                    ->where('identifier_value', $sender->getValue())
                    ->where('identifier_scheme', $sender->getType())
                    ->first();
        } catch (\Exception $e) {
			throw new \Exception('Sender not recognized');
        }

		$info = $this->keyStore->getInfo();

		$receiver_identity = null;

		if ($receiver->getType() === $info['identity_scheme'] && $receiver->getValue() === $info['identity_value']) {
			// We are the receiver
		}
		else {
			try {
				$receiver_identity = Identity::query()
						->where('identifier_value', $receiver->getValue())
						->where('identifier_scheme', $receiver->getType())
						->first();
			} catch (\Exception $e) {
				throw new \Exception('Receiver not recognized');
			}
		}

		$private_key = $this->keyStore->getPrivateKey();

		$decrypted_payload = $envelope->getHeader()->decodePayload($raw_payload, $private_key);
		$sbd = $serializer->deserialize($decrypted_payload,'OCA\PeppolNext\PonderSource\SBD\StandardBusinessDocument::class', 'xml');
		$invoice = $sbd->getInvoice();

		$sender_public_key = KeyStore::publicKeyFromString($sender_identity['as4direct_public_key']);
		$verifyResult = $envelope->getHeader()->getSecurity()->getSignature()->verify($envelope, $decrypted_payload, $sender_public_key);

		if (!$verifyResult) {
			throw new \Exception('Signature verification failed.');
		}

		if (isset($receiver_identity)) {
			// TODO resign and send
		}
		else {
			$letsPeppolService = new LetsPeppolService();

			if (!$letsPeppolService->sendMessage($sender_identity['id'], $decrypted_payload)) {
				throw new \Exception('Failed to send the message in the peppol network');
			}
		}

		// Return success response
		// FIXME: are there really supposed to be two message ID's? One for the request and one for the response?
		$theirMsgId = $envelope->getHeader()->getMessaging()->getUserMessage()->getMessageInfo()->getMessageId();
		$ourMsgId = uniqid('letspeppol-msg-');
		$ourBodyId = uniqid('id-');
			
		$nonRepudiationInformation = [];
			
		foreach ($envelope->getHeader()->getSecurity()->getSignature()->getSignedInfo()->getReferences() as $reference) {
				$nonRepudiationInformation[] = (new MessagePartNRInformation())->addReference($reference);
		}

		$cert = $this->keyStore->getCertificate();
		
		return $this->generateResponse($theirMsgId, $ourMsgId, $ourBodyId, $nonRepudiationInformation, $private_key, $cert);
    }

	private function generateResponse($theirMsgId, $ourMsgId, $ourBodyId, $nonRepudiationInformation, $private_key, $cert)
	{
		$response = new Envelope(
			new Header(
				new Security(

				),
				new Messaging(null, new SignalMessage(
					new MessageInfo(
						new \DateTime(),
						uniqid().'@letspeppol',
						$theirMsgId),
					new Receipt($nonRepudiationInformation),
					null
				), $ourMsgId)
			),
			new Body($ourBodyId)
		);

		$sha256 = new SHA256();
		$c14ne = new Transform("http://www.w3.org/2001/10/xml-exc-c14n#");  //C14NExcTransform();

		$serializer = SerializerBuilder::create()->build();
		$serializedMessaging = $serializer->serialize($response->getHeader()->getMessaging(), 'xml');
		$serializedMessaging = str_replace("  ", '', str_replace("\n", '', $serializedMessaging));
		$serializedBody = $serializer->serialize($response->getBody(), 'xml');
		$serializedBody = str_replace("  ", '', str_replace("\n", '', $serializedBody));

		$references = [
			new DSigReference("#$ourMsgId", $serializedMessaging, [$c14ne], $sha256),
			new DSigReference("#$ourBodyId", $serializedBody, [$c14ne], $sha256)
		];

		$response->getHeader()->getSecurity()->generateSignature($private_key, $cert, $references, new C14NExclusive(), new RsaSha256(), $response);

		$serializedCanonicalizedResponse = $c14ne->transform($serializer->serialize($response, 'xml'));
		$serializedCanonicalizedResponse = str_replace("\n", '', $serializedCanonicalizedResponse);
		$serializedCanonicalizedResponse = str_replace("  ", '', $serializedCanonicalizedResponse);

		return response($serializedCanonicalizedResponse, 200)
				->withHeaders([
					'Referrer-Policy' => 'strict-origin-when-cross-origin',
					'X-Frame-Options' => 'SAMEORIGIN',
					'X-Content-Type-Options' => 'nosniff',
					'X-XSS-Protection' => '1; mode=block',
					'Strict-Transport-Security' => 'max-age=3600;includeSubDomains',
					'Cache-Control' => 'no-cache, no-store, must-revalidate, proxy-revalidate',
					'Content-Type' => 'application/soap+xml;charset=utf-8',
					'Content-Disposition' => null
				]);
	}

    private function getEnvelopeAndPayload(Request $request)
    {
		$contentType = $request->header('Content-Type');
		$boundryStart = strpos($contentType, 'boundary="');
		$boundryEnd = strpos($contentType, '"', $boundryStart + 10);
		$boundry = substr($contentType, $boundryStart + 10, $boundryEnd - $boundryStart - 10);
		$boundryLength = strlen($boundry);
		$body = $request->getContent();
		$pointer = strpos($body, $boundry);
		$pointer = strpos($body, "\r\n\r\n", $pointer);
		$envelopeStart = $pointer + 4;
		$pointer = strpos($body, $boundry, $envelopeStart);
		$envelopeEnd = $pointer - 4;
		$envelope = substr($body, $envelopeStart, $envelopeEnd - $envelopeStart);
		$pointer = strpos($body, "\r\n\r\n", $pointer);
		$payloadStart = $pointer + 4;
		$pointer = strpos($body, $boundry, $payloadStart);
		$payloadEnd = $pointer - 4;
		$payload = substr($body, $payloadStart, $payloadEnd - $payloadStart);
		return [ $envelope, $payload ];
	}
}