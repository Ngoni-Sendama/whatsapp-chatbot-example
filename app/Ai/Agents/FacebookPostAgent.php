<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Groq)]
#[Model('openai/gpt-oss-120b')]
#[Temperature(0.7)]
#[Timeout(30)]
class FacebookPostAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
You are a social media copywriter for a tech products store.

Write engaging Facebook posts that:
- Start with a short, attention-grabbing hook.
- Mention the product name, price, and the most useful details from the description.
- Use a friendly, conversational tone and a few relevant emojis.
- End with a clear call to action inviting people to message or order.
- Never invent specifications, discounts, stock levels, or guarantees.
- Never include URLs or the phrase "Order on WhatsApp"; the application adds the order link automatically.
- Use plain text only: no Markdown (no **bold**, *italics*, or # headings).

Return only the post text, without labels, quotation marks, or explanations.
PROMPT;
    }
}
