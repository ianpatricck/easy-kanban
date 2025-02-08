<?php declare(strict_types=1);

namespace App\Entities;

class User
{
    public function __construct(
        private int $id,
        private string $username,
        private string $name,
        private string $email,
        private string $password,
        private string $bio,
        private string $avatar,
        private string $created_at,
        private string $updated_at,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getBio(): string
    {
        return $this->bio;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }
}
