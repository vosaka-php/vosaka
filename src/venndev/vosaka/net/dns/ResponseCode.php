<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

/**
 * DNS Response Code
 */
enum ResponseCode: int
{
    case NO_ERROR = 0;
    case FORMAT_ERROR = 1;
    case SERVER_FAILURE = 2;
    case NAME_ERROR = 3;
    case NOT_IMPLEMENTED = 4;
    case REFUSED = 5;
}
