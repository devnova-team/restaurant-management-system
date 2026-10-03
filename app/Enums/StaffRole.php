<?php

namespace App\Enums;

enum StaffRole : string
{
    case Owner = 'owner';
    case Cashier = 'cashier';
    case Kitchen = 'kitchen';
}