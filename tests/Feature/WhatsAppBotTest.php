<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppBotTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_collects_quantity_and_location(): void
    {
        $product = Product::create([
            'name' => 'Cudy AC1200 Outdoor Gigabit',
            'slug' => 'cudy-ac1200-outdoor-gigabit',
            'price' => '$150',
            'image' => 'products/test.jpg',
            'description' => 'Outdoor WiFi access point.',
        ]);

        $phone = '+263771000000';

        $this->post('/webhooks/twilio/whatsapp', [
            'From' => "whatsapp:{$phone}",
            'Body' => "Hi, I'm interested in {$product->name} ({$product->price}).",
            'ProfileName' => 'Test User',
        ])
            ->assertOk()
            ->assertHeader('Content-Type', 'text/xml; charset=utf-8')
            ->assertSee('How many units of Cudy AC1200 Outdoor Gigabit would you like?', false);

        $this->assertDatabaseHas('whatsapp_conversations', [
            'phone' => $phone,
            'product_id' => $product->id,
            'status' => 'awaiting_quantity',
        ]);

        $this->post('/webhooks/twilio/whatsapp', [
            'From' => "whatsapp:{$phone}",
            'Body' => '3',
        ])
            ->assertOk()
            ->assertSee('Where are you located?', false);

        $this->assertDatabaseHas('whatsapp_conversations', [
            'phone' => $phone,
            'quantity' => '3',
            'status' => 'awaiting_location',
        ]);

        $this->post('/webhooks/twilio/whatsapp', [
            'From' => "whatsapp:{$phone}",
            'Body' => 'Harare',
        ])
            ->assertOk()
            ->assertSee('We will get back to you soon.', false);

        $this->assertDatabaseHas('whatsapp_conversations', [
            'phone' => $phone,
            'quantity' => '3',
            'location' => 'Harare',
            'status' => 'completed',
        ]);
    }
}
