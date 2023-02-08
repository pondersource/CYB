<?php

namespace App\Connectors\LetsPeppol\ACube;

use Illuminate\Support\Facades\Http;

class Authentication
{

    private const TOKEN_FILE = 'token.db';

    public function getValidToken(): ?string {
        if ($this->hasValidToken()) {
            return $this->getToken();
        }
        else {
            return $this->generateToken();
        }
    }

    public function generateToken(): ?string {
        $response = Http::retry(3, 100, function ($exception, $request) {
            return $exception instanceof ConnectionException;
        }, throw: false)->post(Constants::LOGIN_URL, [
            'email' => Constants::USERNAME,
            'password' => Constants::PASSWORD
        ]);

        if ($response->successful()) {
            $token = $response['token'];
            file_put_contents(__DIR__.'/'.self::TOKEN_FILE, $token);
            return $token;
        }
        else {
            return null;
        }
    }

    public function hasValidToken(): bool {
        $token_info = $this->getTokenInfo();

        if (isset($token_info)) {
            return time() < $token_info['exp'];
        }
        else {
            return false;
        }
    }

    // {"iat":1675081666,"exp":1675168066,"roles":{"it.api.acubeapi.com":["ROLE_WRITER"],"peppol.api.acubeapi.com":["ROLE_WRITER"]},"username":"acube@pondersource.com","uid":334,"fid":null,"pid":null}
    public function getTokenInfo(): ?array {
        $token = $this->getToken();

        if (isset($token)) {
            return json_decode(base64_decode(explode('.', $token)[1]), true);
        }
        else {
            return null;
        }
    }

    public function getToken(): ?string {
        if (file_exists(__DIR__.'/'.self::TOKEN_FILE)) {
            return file_get_contents(__DIR__.'/'.self::TOKEN_FILE);
        }
        else {
            return null;
        }
    }
}