<?php

declare(strict_types=1);

namespace App\Enums;

enum SkillProficiency: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case EXPERT = 'expert';
}
