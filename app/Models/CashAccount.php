<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property double $balance
 * @property string $currency
 * @property string $status
 */
class CashAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'balance' => 'double',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
