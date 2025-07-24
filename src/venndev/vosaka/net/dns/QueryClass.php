<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

/**
 * DNS Query Class
 *
 * Link: https://datatracker.ietf.org/doc/html/rfc1035#section-3.2.4
 */
enum QueryClass: int
{
    case IN = 1;     // Internet
    case CS = 2;     // CSNET
    case CH = 3;     // CHAOS
    case HS = 4;     // Hesiod
    case ANY = 255;  // Any class
}
