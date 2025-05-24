<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

final class User
{
    public function __construct(
        public ?int $id,
        public string $username,
        public string $passwordHash,
        public DateTimeImmutable $createdAt,
    ) {
    }

    public function __toString(): string
    {
        return $this->username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }


}
