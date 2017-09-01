<?php

namespace App\Badges;

class PackagistDownloadsBadge extends AbstractProjectBadge
{
    public function url(): string
    {
        return 'https://packagist.org/packages/' . $this->project->repo;
    }

    public function label(): string
    {
        return 'Total Downloads';
    }

    public function image(): string
    {
        return 'https://img.shields.io/packagist/dt/' . $this->project->repo . '.svg';
    }
}
