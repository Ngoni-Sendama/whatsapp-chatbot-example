<?php

namespace App\Filament\Admin\Resources\WhatsAppConversations\Pages;

use App\Filament\Admin\Resources\WhatsAppConversations\WhatsAppConversationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWhatsAppConversation extends ViewRecord
{
    protected static string $resource = WhatsAppConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
