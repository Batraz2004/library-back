<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BaseQueryTrait
{
    /**
     * @param Builder $query
     * @return Builder
     */
    public function ScopeIsActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }
}
