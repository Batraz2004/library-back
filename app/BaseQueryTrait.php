<?php

namespace App;

use Illuminate\Contracts\Database\Query\Builder;

trait BaseQueryTrait
{
    /**
     * @param $query
     * @return Builder
     */
    public function ScopeIsActive(Builder $query): Builder
    {
        /**@var Bilder $query */
        return $query->where('is_active', 1);
    }
}
