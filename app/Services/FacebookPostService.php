<?php

namespace App\Services;

use Zernio\Api\PostsApi;
use Zernio\ApiException;
use Zernio\Configuration;
use Zernio\Model\CreatePostRequest;
use Zernio\Model\CreatePostRequestPlatformsInner;
use Zernio\Model\MediaItem;

class FacebookPostService
{
    private PostsApi $postsApi;

    private ?string $accountId;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration();
        $config->setAccessToken(config('services.zernio.api_key'));

        $this->postsApi = new PostsApi(null, $config);
        $this->accountId = config('services.zernio.account_id');
    }

    public function postToFacebook(string $content, ?string $imageUrl = null): array
    {
        if (! $this->accountId) {
            throw new \RuntimeException('ZERNIO_ACCOUNT_ID is not configured.');
        }

        $payload = [
            'content' => $content,
            'platforms' => [
                new CreatePostRequestPlatformsInner([
                    'platform' => 'facebook',
                    'account_id' => $this->accountId,
                ]),
            ],
            'publish_now' => true,
        ];

        if ($imageUrl) {
            $payload['media_items'] = [
                new MediaItem([
                    'type' => MediaItem::TYPE_IMAGE,
                    'url' => $imageUrl,
                ]),
            ];
        }

        try {
            $response = $this->postsApi->createPost(new CreatePostRequest($payload));
        } catch (ApiException $e) {
            throw new \RuntimeException($this->extractErrorMessage($e), $e->getCode(), $e);
        }

        $post = $response->getPost();
        $platform = $post?->getPlatforms()[0] ?? null;

        $url = $platform?->getPlatformPostUrl()
            ?? $platform?->getPlatformPostId();
        $status = $platform?->getStatus();
        $error = $platform?->getErrorMessage()
            ?? (method_exists($response, 'getError') ? $response->getError() : null);

        return [
            'success' => $url !== null && ! in_array($status, ['failed', 'error'], true),
            'url' => $url,
            'status' => $status,
            'error' => $error,
            'post_id' => $post?->getId(),
            'warnings' => $response->getWarnings() ?? [],
            'response' => $response,
        ];
    }

    private function extractErrorMessage(ApiException $e): string
    {
        $body = $e->getResponseBody();

        if (is_string($body)) {
            $body = json_decode($body, true);
        }

        if (is_object($body)) {
            $body = json_decode(json_encode($body), true);
        }

        if (is_array($body)) {
            return $body['error'] ?? $body['message'] ?? json_encode($body);
        }

        return $e->getMessage();
    }
}
