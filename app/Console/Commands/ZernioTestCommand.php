<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ZernioTestCommand extends Command
{
    protected $signature = 'zernio:test';
    protected $description = 'Test Zernio API credentials';

    public function handle(): int
    {
        $apiKey = config('services.zernio.api_key');

        if (!$apiKey) {
            $this->error('ZERNIO_API_KEY is not set in .env');
            return self::FAILURE;
        }

        $this->info('Testing Zernio API connection...');

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->get('https://zernio.com/api/v1/profiles');

            if ($response->successful()) {
                $this->info('✓ Zernio API connection successful!');
                $this->line('Status: ' . $response->status());
                $this->line('Response: ' . $response->body());
                return self::SUCCESS;
            } else {
                $this->error('✗ Zernio API connection failed');
                $this->line('Status: ' . $response->status());
                $this->line('Response: ' . $response->body());
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error connecting to Zernio API');
            $this->line('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
