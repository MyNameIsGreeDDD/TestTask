<?php

function generateSlag()
{
    return [
        'event_id' => mt_rand(0, 1000000),
        'event_date' => generateDate(),
        'ticket_adult_price' => mt_rand(0, 10000000),
        'ticket_adult_quantity' => mt_rand(0, 10000000),
        'ticket_kid_price' => mt_rand(0, 10000000),
        'ticket_kid_quantity' => mt_rand(0, 10000000),
        'barcode' => randomString()
    ];
}

function generateDate(): DateTime
{
    $range = rand(964623927, 1627311927);

    return $date = (new DateTime())->setTimestamp($range);
}

function randomString(): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomstring = '';

    for ($i = 0; $i < 10; $i++) {
        $randomstring = $characters[rand(0, strlen($characters))];
    }

    return $randomstring;
}