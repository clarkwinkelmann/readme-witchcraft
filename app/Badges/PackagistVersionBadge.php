<?php

namespace App\Badges;

class PackagistVersionBadge extends AbstractProjectBadge
{
    public function url(): string
    {
        return 'https://packagist.org/packages/' . $this->project->repo;
    }

    public function label(): string
    {
        return 'Latest Stable Version';
    }

    public function image(): string
    {
        return 'https://img.shields.io/packagist/v/' . $this->project->repo . '.svg';
    }
}
