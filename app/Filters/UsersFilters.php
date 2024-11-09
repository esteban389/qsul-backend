<?php

namespace App\Filters;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class UsersFilters
{
    private  static array $allowedFilters = ['name', 'email'];

    private readonly Builder $query;

    public static function getFilters($request)
    {
        return $request->only(self::$allowedFilters);
    }

    public function apply(Builder $query, array $filters): Builder
    {
        if (get_class($query->getModel()) !== User::class){
            throw new InvalidArgumentException('The query must be an instance of ' . User::class);
        }

        if(empty($filters)) {
            return $query;
        }

        $this->query = $query;

        foreach ($filters as $filter => $value) {
            if (in_array($filter, self::$allowedFilters)) {
                $this->$filter($value);
            }
        }

        return $this->query;
    }

    public function name($value): void
    {
        $this->query->where('name', 'like', "%$value%");
    }

    public function email($value): void
    {
        $this->query->where('email', 'like', "%$value%");
    }
}
