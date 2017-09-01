<?php

namespace App\Badges;

class MITLicenseBadge extends AbstractProjectBadge
{
    public function url(): string
    {
        return 'https://github.com/' . $this->project->repo . '/blob/master/' . $this->project->licenseFile;
    }

    public function label(): string
    {
        return 'MIT license';
    }

    public function image(): string
    {
        return 'https://img.shields.io/badge/license-MIT-blue.svg';
    }
}
