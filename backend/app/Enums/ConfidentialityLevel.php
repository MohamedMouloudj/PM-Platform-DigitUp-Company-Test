<?php

namespace App\Enums;

enum ConfidentialityLevel: string
{
    case PUBLIC = 'public';
    case INTERNAL = 'internal';
    case CONFIDENTIAL = 'confidential';
    case TOP_SECRET = 'top_secret';
}
