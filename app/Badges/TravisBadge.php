<?php

namespace App\Badges;

class TravisBadge extends AbstractProjectBadge
{
    public function url(): string
    {
        return 'https://travis-ci.org/' . $this->project->repo;
    }

    public function label(): string
    {
        return 'Build status';
    }

    public function image(): string
    {
        return 'https://travis-ci.org/' . $this->project->repo . '.svg?branch=master';
    }
}
