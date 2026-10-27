<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                  Section::make()
               ->columnSpanFull()
               ->columns(2)
               ->schema([
                TextEntry::make('name'),
                TextEntry::make('description'),
                TextEntry::make('price'),
                
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                      ])->columnSpan(['lg' => 1]),
                        Section::make()
               ->columnSpanFull()
               ->columns(2)
               ->schema([
                      ImageEntry::make('image')->disk('public') ,
                         ])->columnSpan(['lg' => 1]),
            ])->columns(2);
    }
}
