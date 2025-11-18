<?php

namespace App\Enums;

enum PermissionType: string
{
    case READ = 'read';
    case WRITE = 'write';
    case DELETE = 'delete';
    case MANAGE = 'manage';
}
