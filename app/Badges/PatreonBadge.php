<?php

namespace App\Badges;

class PatreonBadge extends AbstractBadge
{
    public function url(): string
    {
        return 'https://www.patreon.com/flagrow';
    }

    public function label(): string
    {
        return 'Donate';
    }

    public function image(): string
    {
        return 'https://img.shields.io/badge/patreon-support-yellow.svg';
    }
}
