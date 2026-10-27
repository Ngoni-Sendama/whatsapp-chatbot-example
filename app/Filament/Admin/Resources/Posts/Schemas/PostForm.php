<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Textarea::make('content')
                    ->columnSpanFull(),
                TextInput::make('facebook_url')
                    ->url(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                Textarea::make('error_message')
                    ->columnSpanFull(),
            ]);
    }
}
