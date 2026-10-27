<?php

namespace App\Filament\Admin\Resources\WhatsAppConversations\Pages;

use App\Filament\Admin\Resources\WhatsAppConversations\WhatsAppConversationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppConversations extends ListRecords
{
    protected static string $resource = WhatsAppConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
