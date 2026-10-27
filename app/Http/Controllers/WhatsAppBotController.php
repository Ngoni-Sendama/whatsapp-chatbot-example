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

            return $this->quantityPrompt($conversation);
        }

        if ($conversation->status === WhatsAppConversation::STATUS_AWAITING_QUANTITY) {
            if (! $conversation->product_name) {
                $product = $this->findProduct($body);

                if ($product !== null) {
                    $this->startInquiry($conversation, $product->name, (string) $product->price);

                    return $this->quantityPrompt($conversation);
                }

                return 'Hi! Which product are you interested in?';
            }

            $quantity = $this->parseQuantity($body);

            if ($quantity === null) {
                return $this->quantityPrompt($conversation);
            }

            $conversation->quantity = $quantity;
            $conversation->status = WhatsAppConversation::STATUS_AWAITING_LOCATION;

            return 'Thanks! Where are you located?';
        }

        if ($conversation->status === WhatsAppConversation::STATUS_AWAITING_LOCATION) {
            $location = $this->parseLocation($body);

            if ($location === null) {
                return 'Please send your location (city or suburb).';
            }

            $conversation->location = $location;
            $conversation->status = WhatsAppConversation::STATUS_COMPLETED;

            Log::info('WhatsApp bot order completed.', [
                'phone' => $conversation->phone,
                'product' => $conversation->product_name,
                'price' => $conversation->price,
                'quantity' => $conversation->quantity,
                'location' => $conversation->location,
            ]);

            return "Thank you! We have your order for {$conversation->quantity} x {$conversation->product_name} in {$conversation->location}. We will get back to you soon. Chimwata A Investments - 0773355211";
        }

        if ($conversation->status === WhatsAppConversation::STATUS_COMPLETED) {
            return 'Thank you! We have received your request and will get back to you soon. Chimwata A Investments - 0773355211';
        }

        return 'Hi! Which product are you interested in?';
    }

    private function quantityPrompt(WhatsAppConversation $conversation): string
    {
        return "How many units of {$conversation->product_name} would you like?\n\n"
            ."1. 1 unit\n"
            ."2. 2 units\n"
            ."3. 3 units\n"
            ."4. 4 units\n\n"
            .'Reply with 1, 2, 3, or 4.';
    }

    private function parseQuantity(string $body): ?string
    {
        if (preg_match('/^([1-4])(?:\s*(?:unit|units|piece|pieces|pc|pcs))?[.!]?$/i', trim($body), $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function parseLocation(string $body): ?string
    {
        $location = trim($body);

        if (mb_strlen($location) < 3 || ! preg_match('/\p{L}/u', $location)) {
            return null;
        }

        if (in_array(mb_strtolower($location), [
            'hi',
            'hie',
            'hey',
            'hello',
            'helo',
            'yo',
            'test',
            'testing',
            'ok',
            'okay',
            'thanks',
            'thank you',
        ], true)) {
            return null;
        }

        return $location;
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
