<?php

namespace App\Filament\Admin\Resources\Posts\Pages;

use App\Filament\Admin\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\Product;
use App\Services\FacebookPostService;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create-post')
                ->label('Generate Post')
                ->modal()
                ->color('success')
                ->schema([
                    Select::make('product_id')
                        ->required()
                        ->searchable()
                        ->options(Product::query()->pluck('name', 'id'))
                        ->label('Select Product'),
                    Radio::make('post_type')
                        ->inlineLabel()
                        ->inline()
                        ->options([
                            'with_image' => 'With Image',
                            'without_image' => 'Without Image',
                        ])->default('without_image'),
                ])
                ->action(function (array $data) {
                    try {
                        $product = Product::find($data['product_id']);

                        if (! $product) {
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Product not found')
                                ->send();

                            return;
                        }

                        $content = $this->generatePostContent($product);

                        $imageUrl = ($data['post_type'] === 'with_image')
                            ? $this->publicImageUrl($product->image)
                            : null;

                        $result = app(FacebookPostService::class)->postToFacebook(
                            content: $content,
                            imageUrl: $imageUrl,
                        );

                        Post::create([
                            'product_id' => $product->id,
                            'content' => $content,
                            'facebook_url' => $result['url'] ?? null,
                            'status' => $result['success'] ? 'posted' : 'failed',
                            'error_message' => $result['error'] ?? null,
                        ]);

                        if (! $result['success']) {
                            Notification::make()
                                ->danger()
                                ->title('Facebook publish failed')
                                ->body($result['error'] ?? 'The post was created but Facebook did not return a published URL.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title('Success')
                            ->body('Post published to Facebook!')
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    private function publicImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
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

    private function generatePostContent(Product $product): string
    {
        $content = <<<EOT
🎉 Check out this amazing product! 🎉

📦 {$product->name}
💰 Price: {$product->price}

{$product->description}

Shop now and enjoy great deals!
EOT;

        if ($whatsappUrl = $this->whatsappUrl($product)) {
            $content .= "\n\n📲 Order on WhatsApp: {$whatsappUrl}";
        }

        return $content;
    }
}
