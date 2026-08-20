<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case OUTLET_ADMIN = 'outlet_admin';
    case STYLIST = 'stylist';
}
