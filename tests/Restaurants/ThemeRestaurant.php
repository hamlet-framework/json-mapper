<?php

namespace Hamlet\JsonMapper\Restaurants;

class ThemeRestaurant extends Restaurant
{
    /** @var string */
    protected $theme;

    public function theme(): string
    {
        return $this->theme;
    }
}
