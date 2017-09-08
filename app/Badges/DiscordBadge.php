<?php

namespace App\Badges;

class DiscordBadge extends AbstractBadge
{
    public function url(): string
    {
        return 'https://flagrow.io/join-discord';
    }

    public function label(): string
    {
        return 'Join our Discord server';
    }

    public function image(): string
    {
        return 'https://discordapp.com/api/guilds/240489109041315840/embed.png';
    }
}
