<?php

namespace App\Models;

use App\BaseQueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Database\Eloquent\Collection<int, CartItem> $cartItems
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Cart extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function cartItems():HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
