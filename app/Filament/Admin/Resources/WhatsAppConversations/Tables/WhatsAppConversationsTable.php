<?php

namespace App\Filament\Admin\Resources\WhatsAppConversations\Tables;

use App\Models\WhatsAppConversation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WhatsAppConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('profile_name')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('quantity')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('location')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => WhatsAppConversation::statusLabel($state))
                    ->color(fn (?string $state): string => WhatsAppConversation::statusColor($state))
                    ->sortable(),
                TextColumn::make('last_message_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
