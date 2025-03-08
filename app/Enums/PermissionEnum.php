<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case LOCATION = 'location';
    case UNIT = 'unit';
    case MACHINE = 'machine';
    case INSPECTION_TYPE = 'inspection-type';
    case GLOBAL_UNIT = 'global-unit';
    case MANPOWER = 'manpower';
    case CONSMAT = 'consmat';
    case SCOPE_STANDART = 'scope-standart';
    case INSTRUKSI_KERJA = 'instruksi-kerja';
    case PART = 'part';
    case TOOLS = 'tools';
    case ADDITIONAL_SCOPE = 'additional-scope';
    case USER = 'user';
    case ROLE = 'role';
}
