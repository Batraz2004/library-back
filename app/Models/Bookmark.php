<?php

namespace App\Models;

use App\BaseQueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $book_id
 * @property int $user_id
 * @property int $quantity
 * @property int $sort_index
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Bookmark extends Model
{
    use SoftDeletes;
    use BaseQueryTrait;

    protected $guarded = [];
    protected $hidden = ['sort_index', 'is_active'];

    protected $casts = [
        'book_id' => 'integer',
        'user_id' => 'integer',
    ];
}
