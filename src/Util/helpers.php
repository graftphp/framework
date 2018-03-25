<?php

function csrf_field()
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
}

function csrf_token()
{
    if (!isset($_SESSION['tokens'])) {
        $_SESSION['tokens'] = [];
    }
    $new_token = bin2hex(random_bytes(32));
    $_SESSION['tokens'][] = $new_token;
    if (count($_SESSION['tokens']) > 10) {
        while (count($_SESSION['tokens']) > 10) {
            array_shift($_SESSION['tokens']);
        }
    }

    return $new_token;
}

function csrf_verify()
{
    if (isset($_POST['_token'])) {
        foreach ($_SESSION['tokens'] as $_token) {
            if (hash_equals($_token, $_POST['_token'])) {
                return true;
            }
        }
    }
    return false;
}

function d($var, $die = false)
{
    ob_start();
    var_dump($var);
    $r = '<pre>' . ob_get_clean() . '</pre>';
    echo $r;
    if ($die) {
        die();
    }
}

function dd($var)
{
    d($var, true);
}
