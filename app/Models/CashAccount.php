<?php

namespace App\Models;

use App\Enums\AccountStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property double $balance
 * @property string $currency
 * @property string $status
 * @property double $transactionTotalSum
 * @property double $total_balance
 */
class CashAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'total_balance' => 'double',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
