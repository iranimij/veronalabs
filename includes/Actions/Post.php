<?php

namespace Actions;

use Helper\Core;

class Post
{
    /**
     * Add Emoji To Contents
     */
    public static function addEmojiToContents()
    {
        add_filter('the_content', function ($title) {
            return $title . PHP_EOL . Core::getRandomEmoji();
        }, 10, 2);
    }
}