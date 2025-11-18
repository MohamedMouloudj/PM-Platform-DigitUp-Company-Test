<?php

namespace App\Enums;

enum FileScanStatus: string
{
    case PENDING = 'pending';
    case CLEAN = 'clean';
    case INFECTED = 'infected';
    case SUSPICIOUS = 'suspicious';
}
