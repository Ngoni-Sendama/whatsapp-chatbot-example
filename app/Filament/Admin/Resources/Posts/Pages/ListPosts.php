<?php

namespace App\Filament\Admin\Resources\Posts\Pages;

use App\Filament\Admin\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\Product;
use App\Services\FacebookPostService;
use App\Services\ProductPostContentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

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

                        $imageUrl = ($data['post_type'] === 'with_image')
                            ? $product->image_url
                            : null;

                        if ($data['post_type'] === 'with_image' && ! $imageUrl) {
                            Notification::make()
                                ->danger()
                                ->title('Image not found')
                                ->body('The product image is missing from the public disk. Re-upload the product image and try again.')
                                ->send();

                            return;
                        }

                        $content = app(ProductPostContentService::class)->generate($product);

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
}
