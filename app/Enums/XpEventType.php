<?php

declare(strict_types=1);

namespace App\Enums;

enum XpEventType: string
{
    case QUESTION_CREATED = 'question_created';
    case ANSWER_CREATED = 'answer_created';
    case ANSWER_VERIFIED = 'answer_verified';
    case DAILY_TASK_COMPLETED = 'daily_task_completed';
    case WEEKLY_TASK_COMPLETED = 'weekly_task_completed';
    case PROFILE_COMPLETED = 'profile_completed';
    case STREAK_MILESTONE = 'streak_milestone';

    /**
     * Get all enum values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum cases as array.
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

