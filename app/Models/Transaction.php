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
class Transaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'balance' => 'double',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }
}
