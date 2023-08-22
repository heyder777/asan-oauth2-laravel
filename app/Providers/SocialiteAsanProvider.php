<?php

namespace App\Providers;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\ProviderInterface;

class SocialiteAsanProvider extends AbstractProvider implements ProviderInterface
{

    //data provided by the scope
    protected $scopes = [
        'openid',
        'certificate',
    ];

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getAsanUrl() . '/grant-permission', $state);
    }

    protected function getTokenUrl(): string
    {
        return env('ASAN_TOKEN_URL');
    }

    protected function getCredentialsUrl(): string
    {
        return env('ASAN_CREDENTIALS_URL');
    }

    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => $this->getTokenHeaders($code),
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
            'verify' => false,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getCredentialsUrl(), [
            'headers' => [
                'cache-control' => 'no-cache',
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'verify' => false,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function mapUserToObject(array $user): \Laravel\Socialite\Two\User
    {
        return (new \Laravel\Socialite\Two\User())->setRaw($user)->map([]);
    }

    public function getAsanUrl(): string
    {
        return env('ASAN_LOGIN_URL');
    }

    protected function getTokenHeaders($code): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => "Basic " . base64_encode(env('ASAN_CLIENT') . ':' . env('ASAN_CLIENT_SECRET'))
        ];
    }

    protected function getTokenFields($code): array
    {
        $fields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];

        if ($this->usesPKCE()) {
            $fields['code_verifier'] = $this->request->session()->pull('code_verifier');
        }

        return $fields;
    }

    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());
        $this->user = $this->mapUserToObject($this->getUserByToken(Arr::get($response, 'access_token')));

        return $this->user->setToken(Arr::get($response, 'access_token'))
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'))
            ->setApprovedScopes($this->scopes);

    }
}

