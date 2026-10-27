<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WhatsAppConversation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Twilio\Security\RequestValidator;
use Twilio\TwiML\MessagingResponse;

class WhatsAppBotController extends Controller
{
    public function handle(Request $request): Response
    {
        $this->validateTwilioSignature($request);

        $from = trim((string) $request->input('From'));
        $body = trim((string) $request->input('Body'));
        $phone = trim((string) preg_replace('/^whatsapp:/i', '', $from));

        if ($phone === '') {
            return response('Missing sender.', 400);
        }

        $conversation = WhatsAppConversation::firstOrNew(['phone' => $phone]);

        if (! $conversation->exists) {
            $conversation->status = WhatsAppConversation::STATUS_AWAITING_QUANTITY;
        }

        if ($request->filled('ProfileName')) {
            $conversation->profile_name = (string) $request->input('ProfileName');
        }

        $reply = $this->replyFor($conversation, $body);
        $conversation->last_message_at = now();
        $conversation->save();

        Log::info('WhatsApp bot message handled.', [
            'phone' => $phone,
            'status' => $conversation->status,
            'product' => $conversation->product_name,
            'quantity' => $conversation->quantity,
            'location' => $conversation->location,
        ]);

        return $this->twiml($reply);
    }

    private function replyFor(WhatsAppConversation $conversation, string $body): string
    {
        $inquiry = $this->parseInquiry($body);

        if ($inquiry !== null) {
            $this->startInquiry($conversation, $inquiry['name'], $inquiry['price']);

            return "Great choice! How many units of {$conversation->product_name} would you like?";
        }

        if ($conversation->status === WhatsAppConversation::STATUS_AWAITING_QUANTITY) {
            if (! $conversation->product_name) {
                $product = $this->findProduct($body);

                if ($product !== null) {
                    $this->startInquiry($conversation, $product->name, (string) $product->price);

                    return "Great choice! How many units of {$conversation->product_name} would you like?";
                }

                return 'Hi! Which product are you interested in?';
            }

            if ($body === '') {
                return 'Please reply with the quantity you would like.';
            }

            $conversation->quantity = $body;
            $conversation->status = WhatsAppConversation::STATUS_AWAITING_LOCATION;

            return 'Thanks! Where are you located?';
        }

        if ($conversation->status === WhatsAppConversation::STATUS_AWAITING_LOCATION) {
            if ($body === '') {
                return 'Please reply with your location.';
            }

            $conversation->location = $body;
            $conversation->status = WhatsAppConversation::STATUS_COMPLETED;

            Log::info('WhatsApp bot order completed.', [
                'phone' => $conversation->phone,
                'product' => $conversation->product_name,
                'price' => $conversation->price,
                'quantity' => $conversation->quantity,
                'location' => $conversation->location,
            ]);

            return "Thank you! We have your order for {$conversation->quantity} x {$conversation->product_name} in {$conversation->location}. We will get back to you soon.";
        }

        if ($conversation->status === WhatsAppConversation::STATUS_COMPLETED) {
            return 'Thank you! We have received your request and will get back to you soon. Chimwata A Investments - 0773355211';
        }

        return 'Hi! Which product are you interested in?';
    }

    /**
     * @return array{name: string, price: string}|null
     */
    private function parseInquiry(string $body): ?array
    {
        if (preg_match('/interested in\s+(.+?)\s*\(([^)]*)\)/i', $body, $matches)) {
            $name = trim($matches[1]);

            if ($name !== '') {
                return ['name' => $name, 'price' => trim($matches[2])];
            }
        }

        if (preg_match('/interested in\s+(.+)/i', $body, $matches)) {
            $name = trim($matches[1]);

            if ($name !== '') {
                return ['name' => $name, 'price' => ''];
            }
        }

        return null;
    }

    private function startInquiry(WhatsAppConversation $conversation, string $name, string $price): void
    {
        $product = $this->findProduct($name);

        $conversation->fill([
            'product_id' => $product?->id,
            'product_name' => $product?->name ?? $name,
            'price' => $product?->price ?? $price,
            'quantity' => null,
            'location' => null,
            'status' => WhatsAppConversation::STATUS_AWAITING_QUANTITY,
        ]);
    }

    private function findProduct(string $name): ?Product
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        return Product::query()
            ->where('name', $name)
            ->orWhere('name', 'like', "%{$name}%")
            ->first();
    }

    private function validateTwilioSignature(Request $request): void
    {
        if (! config('services.twilio.validate_signature')) {
            return;
        }

        $authToken = (string) config('services.twilio.auth_token');

        if ($authToken === '') {
            Log::warning('Twilio signature validation is enabled but TWILIO_AUTH_TOKEN is empty.');

            return;
        }

        if (! class_exists(RequestValidator::class)) {
            Log::error('Twilio signature validation is enabled but the Twilio SDK is not installed. Run composer install.');

            return;
        }

        $signature = (string) $request->header('X-Twilio-Signature', '');
        $validator = new RequestValidator($authToken);

        if (! $validator->validate($signature, $request->fullUrl(), $request->post())) {
            Log::warning('Invalid Twilio signature.', ['url' => $request->fullUrl()]);

            abort(403, 'Invalid Twilio signature.');
        }
    }

    private function twiml(string $message): Response
    {
        if (class_exists(MessagingResponse::class)) {
            $response = new MessagingResponse;
            $response->message($message);
            $xml = (string) $response;
        } else {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Response><Message>'
                .htmlspecialchars($message, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                .'</Message></Response>';
        }

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
