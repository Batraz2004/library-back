<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['id','title','category_id','fields'];

    protected $casts = [
        'fields' => 'array',
    ];
}
