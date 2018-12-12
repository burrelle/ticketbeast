<?php

namespace App;

use App\Concert;

class CustomConcertFactory
{
    public static function createPublished($overrides)
    {
        $concert = factory(Concert::class)->create($overrides);
        $concert->publish();
        return $concert;
    }
}
