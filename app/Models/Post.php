<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['product_id', 'facebook_url', 'content', 'status', 'error_message'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
