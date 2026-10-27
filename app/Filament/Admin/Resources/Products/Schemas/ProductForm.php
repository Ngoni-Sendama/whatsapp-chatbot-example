<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->required(),
                FileUpload::make('image')
                    ->image()
                    ->required(),
                TextInput::make('description')
                    ->required(),
            ]);
    }
}
