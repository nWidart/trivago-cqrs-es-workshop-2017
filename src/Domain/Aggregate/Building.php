<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserHasCheckedIn;
use Building\Domain\DomainEvent\UserHasCheckedOut;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array<null, string>
     */
    private $checkedInUsers;

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        if (array_key_exists($username, $this->checkedInUsers)) {
            throw new \DomainException(sprintf(
                'User %s is already checked in',
                $username
            ));
        }
        $this->recordThat(UserHasCheckedIn::fromBuildingIdAndUsername($this->uuid, $username));
    }

    public function checkOutUser(string $username)
    {
        if (! array_key_exists($username, $this->checkedInUsers)) {
            throw new \DomainException(sprintf(
                'User %s is not yet checked in',
                $username
            ));
        }
        $this->recordThat(UserHasCheckedOut::fromBuildingIdAndUsername($this->uuid, $username));
    }

    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = $event->uuid();
        $this->name = $event->name();
    }

    public function whenUserHasCheckedIn(UserHasCheckedIn $event): void
    {
        $this->checkedInUsers[$event->username()] = null;
    }

    public function whenUserHasCheckedOut(UserHasCheckedOut $event): void
    {
        unset($this->checkedInUsers[$event->username()]);
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
