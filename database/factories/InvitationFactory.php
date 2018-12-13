<?php

use Faker\Generator as Faker;

$factory->define(App\Invitation::class, function (Faker $faker) {
    return [
        'email' => 'somebody@example.com',
        'code' => 'TESTCODE1234',
    ];
});
