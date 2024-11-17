<?php

namespace App\Http\Services;

use App\Models\Process;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

class ProcessService
{


    public function getProcesses(): Collection
    {
        return QueryBuilder::for(Process::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['name'])
            ->get();
    }
}
