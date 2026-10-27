<?php

namespace App\Filament\Admin\Resources\WhatsAppConversations;

use App\Filament\Admin\Resources\WhatsAppConversations\Pages\CreateWhatsAppConversation;
use App\Filament\Admin\Resources\WhatsAppConversations\Pages\EditWhatsAppConversation;
use App\Filament\Admin\Resources\WhatsAppConversations\Pages\ListWhatsAppConversations;
use App\Filament\Admin\Resources\WhatsAppConversations\Pages\ViewWhatsAppConversation;
use App\Filament\Admin\Resources\WhatsAppConversations\Schemas\WhatsAppConversationForm;
use App\Filament\Admin\Resources\WhatsAppConversations\Schemas\WhatsAppConversationInfolist;
use App\Filament\Admin\Resources\WhatsAppConversations\Tables\WhatsAppConversationsTable;
use App\Models\WhatsAppConversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsAppConversationResource extends Resource
{
    protected static ?string $model = WhatsAppConversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'phone';

    public static function form(Schema $schema): Schema
    {
        return WhatsAppConversationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WhatsAppConversationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppConversationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsAppConversations::route('/'),
            'create' => CreateWhatsAppConversation::route('/create'),
            'view' => ViewWhatsAppConversation::route('/{record}'),
            'edit' => EditWhatsAppConversation::route('/{record}/edit'),
        ];
    }
}
