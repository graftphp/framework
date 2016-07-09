<?php

namespace GraftPHP\Framework;

class Functions
{

    public static function redirect($url)
    {
        header("Location: $url");
        exit();
    }

    public static function urlSafe($text)
    {
        # https://gist.github.com/Mezzle/4944982
        // Swap out Non "Letters" with a -
        $text = str_replace("'", "", $text);
        $text = preg_replace('/[^\\pL\d]+/u', '-', $text);
        // Trim out extra -'s
        $text = trim($text, '-');
        // Convert letters that we have left to the closest ASCII representation
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // Make text lowercase
        $text = strtolower($text);
        // Strip out anything we haven't been able to convert
        $text = preg_replace('/[^-\w]+/', '', $text);
        return $text;
    }

}
