<?php

require '../vendor/autoload.php';

use venndev\vosaka\net\tcp\TCPReadHalf;
use venndev\vosaka\net\tcp\TCPWriteHalf;
use venndev\vosaka\net\tcp\TCPListener;
use venndev\vosaka\VOsaka;

function main(): Generator
{
    $listener = TCPListener::bind("127.0.0.1:8080");
}