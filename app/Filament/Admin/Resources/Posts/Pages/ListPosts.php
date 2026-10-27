<?php

namespace App\Filament\Admin\Resources\Posts\Pages;

use App\Filament\Admin\Resources\Posts\PostResource;
use App\Models\Product;
use App\Models\Post;
use App\Services\FacebookPostService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
              Action::make('create-post')
              ->label('Generate Post')
              ->modal()
              ->color('success')
              ->schema([
        Select::make('product_id')
            ->required()
            ->searchable()
            ->options(Product::query()->pluck('name', 'id'))
            ->label('Select Produt'),
            Radio::make('post_type')
            ->inlineLabel()
            ->inline()
            ->options([
                'with_image'=>'With Image',
                'without_image'=>'Without Image',
            ])->default('without_image'),

    ])
        ->action(function (array $data) {
            try {
                $product = Product::find($data['product_id']);

                if (!$product) {
                    Notification::make()
                        ->danger()
                        ->title('Error')
                        ->body('Product not found')
                        ->send();
                    return;
                }

                $facebookService = new FacebookPostService();

                $imageUrl = ($data['post_type'] === 'with_image') ? $product->image : null;

                $response = $facebookService->postToFacebook(
                    content: $this->generatePostContent($product),
                    imageUrl: $imageUrl
                );

                Post::create([
                    'product_id' => $product->id,
                    'content' => $this->generatePostContent($product),
                    'facebook_url' => $response['url'] ?? null,
                    'status' => 'posted',
                ]);

                Notification::make()
                    ->success()
                    ->title('Success')
                    ->body('Post created successfully!')
                    ->send();
            } catch (\Exception $e) {
                Notification::make()
                    ->danger()
                    ->title('Error')
                    ->body($e->getMessage())
                    ->send();
            }
        }),
        ];
    }

    private function generatePostContent(Product $product): string
    {
        return <<<EOT
🎉 Check out this amazing product! 🎉

📦 {$product->name}
💰 Price: {$product->price}

{$product->description}

Shop now and enjoy great deals!
EOT;
    }
}
