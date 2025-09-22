<?php

namespace App\Enums;

enum CaseStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case BACKEND = 'backend';
}
