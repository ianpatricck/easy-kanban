<?php declare(strict_types=1);

namespace App\Data\Repositories;

use App\Data\DAO\UserDAO;
use App\DTO\CreateUserDTO;
use App\Entities\User;

class UserRepository
{
    public function __construct(
        private readonly UserDAO $userDAO
    ) {}

    public function create(CreateUserDTO $dto): void
    {
        $query = 'INSERT INTO users (
              username, 
              name, 
              email, 
              password, 
              bio, 
              avatar
            ) VALUES (?, ?, ?, ?, ?, ?)';

        $this->userDAO->execute($query, get_object_vars($dto));
    }

    public function findOneByUsername(string $username): User|null
    {
        $query = 'SELECT * FROM users WHERE username = ?';
        $user = $this->userDAO->fetchOne($query, [$username]);

        if ($user) {
            $userArray = get_object_vars($user);
            $userEntity = new User(...array_values($userArray));
            return $userEntity;
        }

        return null;
    }

    public function findOneByEmail(string $email): User|null
    {
        $query = 'SELECT * FROM users WHERE email = ?';
        $user = $this->userDAO->fetchOne($query, [$email]);

        if ($user) {
            $userArray = get_object_vars($user);
            $userEntity = new User(...array_values($userArray));
            return $userEntity;
        }

        return null;
    }

    public function findOneById(int $id): User|null
    {
        $query = 'SELECT * FROM users WHERE id = ?';
        $user = $this->userDAO->fetchOne($query, [$id]);

        if ($user) {
            $userArray = get_object_vars($user);
            $userEntity = new User(...array_values($userArray));
            return $userEntity;
        }

        return null;
    }

    public function updateUsername(string $currentUsername, string $newUsername): void
    {
        $query = 'UPDATE users SET username = ? WHERE username = ?';
        $this->userDAO->execute($query, [$newUsername, $currentUsername]);
    }

    public function updateName(string $username, string $newName): void
    {
        $query = 'UPDATE users SET name = ? WHERE username = ?';
        $this->userDAO->execute($query, [$newName, $username]);
    }

    public function updateEmail(int $id, string $updatedEmail): void
    {
        $query = 'UPDATE users SET email = ? WHERE id = ?';
        $this->userDAO->execute($query, [$updatedEmail, $id]);
    }

    public function updatePassword(int $id, string $newPassword): void
    {
        $query = 'UPDATE users SET password = ? WHERE id = ?';
        $this->userDAO->execute($query, [$newPassword, $id]);
    }

    public function updateDescription(string $username, string $bio): void
    {
        $query = 'UPDATE users SET bio = ? WHERE username = ?';
        $this->userDAO->execute($query, [$bio, $username]);
    }

    public function delete(int $id): void
    {
        $query = 'DELETE FROM users WHERE id = ?';
        $this->userDAO->execute($query, [$id]);
    }
}
