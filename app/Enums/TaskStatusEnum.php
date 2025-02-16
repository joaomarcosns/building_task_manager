<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatusEnum: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Aberta',
            self::IN_PROGRESS => 'Em andamento',
            self::COMPLETED => 'Concluída',
            self::REJECTED => 'Rejeitada',
        };
    }
}
