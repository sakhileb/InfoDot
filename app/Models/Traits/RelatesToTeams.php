<?php

namespace App\Models\Traits;

trait RelatesToTeams
{
    /** @phpstan-require-extends \Illuminate\Database\Eloquent\Model */

    /** @property int $id */
    public function scopeForCurrentTeam($query): void
    {
        $query->where('team_id', auth()->user()->currentTeam->id);
    }
}