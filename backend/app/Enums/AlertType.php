<?php

namespace App\Enums;

enum AlertType: string
{
    case SUSPICIOUS_LOGIN = 'suspicious_login';
    case NEW_LOCATION = 'new_location';
    case MULTIPLE_FAILED_ATTEMPTS = 'multiple_failed_attempts';
    case RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
}
