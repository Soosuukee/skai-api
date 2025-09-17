<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum RequestStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Acceptée',
            self::DECLINED => 'Refusée',
            self::COMPLETED => 'Terminée',
        };
    }

    public static function fromString(string $status): self
    {
        return match ($status) {
            'pending' => self::PENDING,
            'accepted' => self::ACCEPTED,
            'declined' => self::DECLINED,
            'completed' => self::COMPLETED,
            default => throw new \InvalidArgumentException("Invalid request status: $status"),
        };
    }
}
