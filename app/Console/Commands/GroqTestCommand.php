<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GroqTestCommand extends Command
{
    protected $signature = 'groq:test {--model=openai/gpt-oss-120b : The model to test with}';
    protected $description = 'Test Groq API connection from Laravel AI';

    public function handle(): int
    {
        $apiKey = config('services.groq.api_key') ?? env('GROQ_API_KEY');

        if (!$apiKey) {
            $this->error('GROQ_API_KEY is not set in .env');
            return self::FAILURE;
        }

        $model = $this->option('model');

        $this->info("Testing Groq API connection with model: {$model}");

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'What is Laravel? Give a brief answer in 2-3 sentences.'
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 256,
            ]);

            if ($response->successful()) {
                $this->info('✓ Groq API connection successful!');
                $this->line('Status: ' . $response->status());

                $content = $response->json('choices.0.message.content', 'No response');
                $this->line("\nPrompt: What is Laravel?");
                $this->line("\nResponse:");
                $this->line($content);

                return self::SUCCESS;
            } else {
                $this->error('✗ Groq API connection failed');
                $this->line('Status: ' . $response->status());
                $this->line('Error: ' . $response->body());
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error connecting to Groq API');
            $this->line('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
