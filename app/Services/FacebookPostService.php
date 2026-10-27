<?php

namespace App\Services;

use Zernio\Api\PostsApi;
use Zernio\Configuration;
use Zernio\Model\CreatePostRequest;
use Zernio\Model\CreatePostRequestMedia;

class FacebookPostService
{
    private $postsApi;
    private $accountId;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration();
        $config->setAccessToken(config('services.zernio.api_key'));

        $this->postsApi = new PostsApi(null, $config);
        $this->accountId = config('services.zernio.account_id');
    }

    public function postToFacebook(string $content, ?string $imageUrl = null): array
    {
        try {
            $request = new CreatePostRequest([
                'account_id' => $this->accountId,
                'platform' => 'facebook',
                'text' => $content,
            ]);

            if ($imageUrl) {
                $media = new CreatePostRequestMedia([
                    'type' => 'image',
                    'url' => $imageUrl,
                ]);
                $request->setMedia([$media]);
            }

            $response = $this->postsApi->createPost($request);

            return [
                'success' => true,
                'url' => $response['url'] ?? null,
                'response' => $response,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
