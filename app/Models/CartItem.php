<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $quantity
 * @property int $book_id
 * @property int $cart_id
 * @property CartItem $cart
 * @property Book $book
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CartItem extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['is_active', 'sort_index'];

    protected $casts = [
        'user_id' => 'integer',
        'book_id' => 'integer',
        'cart_id' => 'integer',
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
