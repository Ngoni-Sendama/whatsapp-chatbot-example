<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product_id')
                    ->numeric(),
                TextEntry::make('content')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('facebook_url')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => Post::statusColor($state)),
                TextEntry::make('error_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
