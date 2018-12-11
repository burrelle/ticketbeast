<?php

use Faker\Generator as Faker;

$factory->define(App\Order::class, function (Faker $faker) {
    return [
        'amount' => 5250,
        'email' => 'example@example.com',
        'confirmation_number' => 'ORDERCONFIRMATION1234',
        'card_last_four' => '1234'
    ];
});
