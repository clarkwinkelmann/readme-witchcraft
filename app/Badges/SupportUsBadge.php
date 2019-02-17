<?php

namespace App\Badges;

class SupportUsBadge extends AbstractBadge
{
    public function url(): string
    {
        return 'https://flagrow.io/support-us';
    }

    public function label(): string
    {
        return 'Support Us';
    }

    public function image(): string
    {
        return 'https://img.shields.io/badge/flagrow.io-support%20us-yellow.svg';
    }
}
