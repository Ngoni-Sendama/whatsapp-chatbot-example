<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Posts\PostResource;
use App\Filament\Admin\Resources\Products\ProductResource;
use App\Filament\Admin\Resources\WhatsAppConversations\WhatsAppConversationResource;
use App\Models\Post;
use App\Models\Product;
use App\Models\WhatsAppConversation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $products = Product::count();
        $productsWithImages = Product::query()
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->count();

        $posts = Post::count();
        $posted = Post::where('status', 'posted')->count();
        $failed = Post::where('status', 'failed')->count();

        $conversations = WhatsAppConversation::count();
        $completed = WhatsAppConversation::where('status', WhatsAppConversation::STATUS_COMPLETED)->count();
        $inProgress = WhatsAppConversation::whereIn('status', [
            WhatsAppConversation::STATUS_AWAITING_QUANTITY,
            WhatsAppConversation::STATUS_AWAITING_LOCATION,
        ])->count();

        return [
            Stat::make('Products', $products)
                ->description("{$productsWithImages} with images")
                ->descriptionIcon('heroicon-o-photo')
                ->color('primary')
                ->url(ProductResource::getUrl()),
            Stat::make('Facebook Posts', $posts)
                ->description("{$posted} posted · {$failed} failed")
                ->descriptionIcon('heroicon-o-megaphone')
                ->color($failed > 0 ? 'warning' : 'success')
                ->url(PostResource::getUrl()),
            Stat::make('WhatsApp Conversations', $conversations)
                ->description("{$completed} completed · {$inProgress} in progress")
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->url(WhatsAppConversationResource::getUrl()),
            Stat::make('Completed Orders', $completed)
                ->description("{$inProgress} awaiting follow-up")
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success')
                ->url(WhatsAppConversationResource::getUrl()),
        ];
    }
}
