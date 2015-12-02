<?php

namespace GraftPHP\Framework;

class Functions
{

    public static function Redirect($url)
    {
        header("Location: $url");
        exit();
    }

}
