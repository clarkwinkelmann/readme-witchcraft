<?php

namespace App\Badges;

abstract class AbstractBadge
{
    abstract public function url(): string;

    abstract public function label(): string;

    abstract public function image(): string;

    public function markdown(): string
    {
        return '[![' . $this->label() . '](' . $this->image() . ')](' . $this->url() . ')';
    }
}
