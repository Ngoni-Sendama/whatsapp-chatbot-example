<?php

namespace App\Filament\Admin\Resources\WhatsAppConversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('profile_name'),
                Select::make('product_id')
                    ->relationship('product', 'name'),
                TextInput::make('product_name'),
                TextInput::make('price'),
                TextInput::make('quantity'),
                TextInput::make('location'),
                TextInput::make('status')
                    ->required()
                    ->default('awaiting_quantity'),
                DateTimePicker::make('last_message_at'),
                ])
            ]);
    }
}
