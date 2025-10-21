<?php

namespace App\Observers;

use App\Models\Transaction\Project;

class ProjectSafeObserver
{
    public function created(Project $project)
    {
        $project->activities()->create([
            'user_id' => auth()->user()->id,
            'activity' => 'generate',
        ]);
    }
}
