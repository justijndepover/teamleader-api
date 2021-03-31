<?php

namespace Justijndepover\Teamleader;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Message;
use Justijndepover\Teamleader\Exceptions\CouldNotAquireAccessToken;

class Teamleader
{
    /**
     * @var string
     */
    private $baseUrl = 'https://app.teamleader.eu';

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $authorizationCode;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var int
     */
    private $tokenExpiresAt;

    public function __construct(string $clientId, string $clientSecret, string $redirectUri, string $state)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->state = $state;
        $this->client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'verify' => true,
        ]);
    }

    public function redirectForAuthorizationUrl(): string
    {
        return $this->baseUrl . '/oauth2/authorize'
            . '?client_id=' . $this->clientId
            . '&response_type=code'
            . '&state=' . $this->state
            . '&redirect_uri=' . $this->redirectUri;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId($clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret($clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri($redirectUri): void
    {
        $this->redirectUri = $redirectUri;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState($state): void
    {
        $this->state = $state;
    }

    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    public function setAuthorizationCode($authorizationCode): void
    {
        $this->authorizationCode = $authorizationCode;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken($refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getTokenExpiresAt(): int
    {
        return $this->tokenExpiresAt;
    }

    public function setTokenExpiresAt($tokenExpiresAt): void
    {
        $this->tokenExpiresAt = $tokenExpiresAt;
    }

    public function shouldAuthorize(): bool
    {
        return empty($this->authorizationCode) && empty($this->refreshToken);
    }

    public function shouldRefreshToken(): bool
    {
        return empty($this->accessToken) || $this->tokenHasExpired();
    }

    public function connect(): void
    {
        if ($this->shouldAuthorize()) {
            header("Location: {$this->redirectForAuthorizationUrl()}");
            exit;
        }

        if ($this->shouldRefreshToken()) {
            $this->acquireAccessToken();
        }
    }

    private function tokenHasExpired(): bool
    {
        if (empty($this->tokenExpiresAt)) {
            return true;
        }

        return ($this->tokenExpiresAt - 60) < time();
    }

    private function acquireAccessToken(): void
    {
        try {
            // If refresh token not yet acquired, do token request
            if (empty($this->refreshToken)) {
                $data = [
                    'form_params' => [
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'code' => $this->authorizationCode,
                        'grant_type' => 'authorization_code',
                        'redirect_uri' => $this->redirectUri,
                    ],
                ];
            } else { // else do refresh token request
                $data = [
                    'form_params' => [
                        'client_id' => $this->exactClientId,
                        'client_secret' => $this->exactClientSecret,
                        'refresh_token' => $this->refreshToken,
                        'grant_type' => 'refresh_token',
                    ],
                ];
            }

            $response = $this->client->post($this->baseUrl . '/oauth2/access_token', $data);

            Message::rewindBody($response);
            $body = json_decode($response->getBody()->getContents(), true);

            $this->accessToken = $body['access_token'];
            $this->refreshToken = $body['refresh_token'];
            $this->tokenExpiresAt = time() + $body['expires_in'];
        } catch (Exception $e) {
            throw CouldNotAquireAccessToken::make($e->getCode(), $e->getMessage());
        }
    }
}
