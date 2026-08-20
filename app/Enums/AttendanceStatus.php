<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case EARLY_LEAVE = 'early_leave';
    case ABSENT = 'absent';
}
