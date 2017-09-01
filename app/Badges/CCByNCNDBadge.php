<?php

namespace App\Badges;

class CCByNCNDBadge extends AbstractProjectBadge
{
    public function url(): string
    {
        return 'https://github.com/' . $this->project->repo . '/blob/master/' . $this->project->licenseFile;
    }

    public function label(): string
    {
        return 'CC-BY-NC-ND 4.0 license';
    }

    public function image(): string
    {
        return 'https://licensebuttons.net/l/by-nc-nd/4.0/88x31.png';
    }
}
