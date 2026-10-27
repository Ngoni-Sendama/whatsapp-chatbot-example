<?php

namespace App\Services;

use ZernioDev\Zernio\Zernio;

class FacebookPostService
{
    private $zernio;
    private $accountId;

    public function __construct()
    {
        $this->zernio = new Zernio(config('services.zernio.api_key'));
        $this->accountId = config('services.zernio.account_id');
    }

    public function postToFacebook(string $content, ?string $imageUrl = null): array
    {
        $payload = [
            'accountId' => $this->accountId,
            'platform' => 'facebook',
            'text' => $content,
        ];

        if ($imageUrl) {
            $payload['media'] = [
                ['type' => 'image', 'url' => $imageUrl]
            ];
        }

        $response = $this->zernio->posts()->create($payload);

        return [
            'success' => true,
            'url' => $response['url'] ?? null,
            'response' => $response,
        ];
    }
}
