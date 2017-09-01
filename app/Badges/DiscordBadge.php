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
        return 'https://img.shields.io/badge/discord-chat-blue.svg';
    }
}
