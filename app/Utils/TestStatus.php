<?php

namespace App\TestStatus;

enum TestStatus: string
{
    case PENDING   = 'pending';
    case RUNNING   = 'running';
    case PASSED    = 'passed';
    case FAILED    = 'failed';
    case SKIPPED   = 'skipped';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente de ejecución',
            self::RUNNING => 'En progreso',
            self::PASSED  => 'Superado',
            self::FAILED  => 'Fallido',
            self::SKIPPED => 'Omitido',
        };
    }
}
