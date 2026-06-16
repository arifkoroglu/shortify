<?php

namespace App\Entities;

use DateTime;

class Url
{
    public function __construct(
        private ?int $id,
        private string $originalUrl,
        private string $shortCode,
        private ?int $userId = null,
        private bool $isActive = true,
        private ?DateTime $expiresAt = null,
        private ?DateTime $createdAt = null
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getOriginalUrl(): string { return $this->originalUrl; }
    public function getShortCode(): string { return $this->shortCode; }
    public function getUserId(): ?int { return $this->userId; }
    public function isActive(): bool { return $this->isActive; }
    public function getExpiresAt(): ?DateTime { return $this->expiresAt; }
    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
}