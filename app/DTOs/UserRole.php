<?php

namespace App\DTOs;

enum UserRole: string
{
    case NationalCoordinator = 'national_coordinator';
    case CampusCoordinator = 'campus_coordinator';
    case ProcessLeader = 'process_leader';
}
