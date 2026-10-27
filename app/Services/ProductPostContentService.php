<?php

namespace App\Services;

use App\Ai\Agents\FacebookPostAgent;
use App\Models\Product;

class ProductPostContentService
{
    public function __construct(private readonly FacebookPostAgent $agent) {}

    public function generate(Product $product): string
    {
        $response = $this->agent->prompt($this->buildPrompt($product));

        $content = trim($response->text);

        if ($content === '') {
            throw new \RuntimeException('Groq returned an empty post. Please try again.');
        }

        if ($whatsappUrl = $this->whatsappUrl($product)) {
            $content .= "\n\n📲 Order on WhatsApp: {$whatsappUrl}";
        }

        return $content;
    }

    private function buildPrompt(Product $product): string
    {
        return <<<PROMPT
Write a Facebook post for the product below.

Product: {$product->name}
Price: {$product->price}
Description: {$product->description}

Keep the post under 600 characters. Do not include a URL or an "Order on WhatsApp" line — the application adds that.
PROMPT;
    }

    private function whatsappUrl(Product $product): ?string
    {
        $number = preg_replace('/\D+/', '', (string) config('services.whatsapp.number'));

        if (! $number) {
            return null;
        }

        $message = "Hi, I'm interested in {$product->name} ({$product->price}).";

        return "https://wa.me/{$number}?text=".rawurlencode($message);
    }
}
