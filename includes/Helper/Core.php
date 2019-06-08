<?php

namespace Helper;

class Core
{
    /**
     * @return mixed
     */
    public static function getRandomEmoji()
    {
        $items = Array(':)', ':(', ':|', ':D');

        return $items[array_rand($items)];
    }
}