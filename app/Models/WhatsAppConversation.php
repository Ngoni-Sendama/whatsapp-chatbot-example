<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppConversation extends Model
{
    public const STATUS_AWAITING_QUANTITY = 'awaiting_quantity';

    public const STATUS_AWAITING_LOCATION = 'awaiting_location';

    public const STATUS_COMPLETED = 'completed';

    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'phone',
        'profile_name',
        'product_id',
        'product_name',
        'price',
        'quantity',
        'location',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            self::STATUS_AWAITING_QUANTITY => 'Awaiting quantity',
            self::STATUS_AWAITING_LOCATION => 'Awaiting location',
            self::STATUS_COMPLETED => 'Completed',
            default => ucfirst((string) $status),
        };
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            self::STATUS_AWAITING_QUANTITY => 'warning',
            self::STATUS_AWAITING_LOCATION => 'info',
            self::STATUS_COMPLETED => 'success',
            default => 'gray',
        };
    }
}
