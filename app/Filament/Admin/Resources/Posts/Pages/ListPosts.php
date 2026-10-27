<?php

namespace App\Filament\Admin\Resources\Posts\Pages;

use App\Filament\Admin\Resources\Posts\PostResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
              ->schema([
        Select::make('product_id')
            ->required()
            ->searchable()
            ->options(Product::query()->pluck('name', 'id'))
            ->label('Select Produt'),
       
    ]),
        ];
    }
}
