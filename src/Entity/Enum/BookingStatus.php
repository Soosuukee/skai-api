<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Acceptée',
            self::DECLINED => 'Refusée',
        };
    }

    public static function fromString(string $status): self
    {
        return match ($status) {
            'pending' => self::PENDING,
            'accepted' => self::ACCEPTED,
            'declined' => self::DECLINED,
            default => throw new \InvalidArgumentException("Invalid booking status: $status"),
        };
    }
}
