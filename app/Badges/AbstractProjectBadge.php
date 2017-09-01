<?php

namespace App\Badges;

use App\Project;

abstract class AbstractProjectBadge extends AbstractBadge
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }
}
