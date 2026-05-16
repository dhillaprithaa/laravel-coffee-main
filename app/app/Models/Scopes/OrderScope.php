<?php

namespace App\Models\Scopes;

use App\Enums\RoleType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class OrderScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $authenticated = Auth::check();
        if (!$authenticated) return;

        $user = Auth::user();
        if ($user->role !== RoleType::PIMPINAN) return;

        $builder
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);
    }
}
