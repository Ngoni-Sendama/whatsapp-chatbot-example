<?php

namespace App\Filament\Admin\Resources\WhatsAppConversations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppConversationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Section::make()
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                TextEntry::make('phone'),
                TextEntry::make('profile_name')
                    ->placeholder('-'),
                TextEntry::make('product.name')
                    ->label('Product')
                    ->placeholder('-'),
                TextEntry::make('product_name')
                    ->placeholder('-'),
                TextEntry::make('price')
                    ->placeholder('-'),
                TextEntry::make('quantity')
                    ->placeholder('-'),
                TextEntry::make('location')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('last_message_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                    ])
            ]);
    }
}
