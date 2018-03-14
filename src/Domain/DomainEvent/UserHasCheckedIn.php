<?php

namespace Building\Domain\DomainEvent;

use Prooph\EventSourcing\AggregateChanged;
use Rhumsaa\Uuid\Uuid;

final class UserHasCheckedIn extends AggregateChanged
{
    public static function fromBuildingIdAndUsername(Uuid $buildingId, string $username): self
    {
        return self::occur((string) $buildingId, ['username' => $username]);
    }

    public function username(): string
    {
        return $this->payload['username'];
    }
}
