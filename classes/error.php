<?php

function error($code, $message = '') {
    switch ($code) {
        case 310 :  $definition = "Too many Redirects";             break;
        case 400 :  $definition = "Bad request";                    break;
        case 401 :  $definition = "Unauthorized";                   break;
        case 402 :  $definition = "PaymentRequired";                break;
        case 403 :  $definition = "Forbidden";                      break;
        case 404 :  $definition = "Not found";                      break;
        case 410 :  $definition = "Gone";                           break;
        case 500 :  $definition = "Internal Error";                 break;
        case 501 :  $definition = "Not implemented";                break;
        case 502 :  $definition = "Service temporarily overloaded"; break;
        case 507 :  $definition = "Insufficient storage";           break;
        case 508 :  $definition = "Loop detected";                  break;
        default  :
            $code = 520;
            $definition = "Web server is returning an unknown error";
        break;
    }

    if (class_exists('_')) _::log('error ' . $code, $message);

    header("HTTP/1.0 " . $code . " " . $definition);
    die($message);
}

?>