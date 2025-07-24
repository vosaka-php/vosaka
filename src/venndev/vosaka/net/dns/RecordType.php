<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

/**
 * DNS Record Types
 *
 * Link: https://datatracker.ietf.org/doc/html/rfc1035#section-3.2.2
 */
enum RecordType: int
{
    case A = 1;      // IPv4 address
    case NS = 2;     // Name server
    case CNAME = 5;  // Canonical name
    case SOA = 6;    // Start of authority
    case PTR = 12;   // Pointer
    case MX = 15;    // Mail exchange
    case TXT = 16;   // Text
    case AAAA = 28;  // IPv6 address
    case SRV = 33;   // Service
    case ANY = 255;  // Any record
}
