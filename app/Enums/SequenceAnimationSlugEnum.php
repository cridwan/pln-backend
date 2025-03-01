<?php

namespace App\Enums;

enum SequenceAnimationSlugEnum: string
{
    case EXHAUSE_SECTION = 'exhaust-section';
    case TURBIN_SECTION = 'turbine-section';
    case COMBUSTION_SECTION = 'combustion-section';
    case COMPESSOR_SECTION = 'compressor-section';
    case INLET_SECTION = 'inlet-section';
    case GENERATOR_SECTION = 'generator-section';
}
