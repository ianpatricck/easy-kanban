<?php declare(strict_types=1);

// |=======================================================|
// | Repositório dos usuários para persistência de dados   |
// |=======================================================|

namespace App\Data\Repositories;

use App\Data\DAO;
use App\DTO\CreateUserDTO;
use App\Entities\User;

class UserRepository
{
    public function __construct(
        private readonly DAO $dao
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

        $this->dao->execute($query, get_object_vars($dto));
    }

    public function findOneByUsername(string $username): User|null
    {
        $query = 'SELECT * FROM users WHERE username = ?';
        $user = $this->dao->fetchOne($query, [$username]);

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
        $user = $this->dao->fetchOne($query, [$email]);

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
        $user = $this->dao->fetchOne($query, [$id]);

        if ($user) {
            $userArray = get_object_vars($user);
            $userEntity = new User(...array_values($userArray));
            return $userEntity;
        }

        return null;
    }

    public function updateUsername(int $id, string $newUsername): void
    {
        $query = 'UPDATE users SET username = ? WHERE id = ?';
        $this->dao->execute($query, [$newUsername, $id]);
    }

    public function updateName(int $id, string $newName): void
    {
        $query = 'UPDATE users SET name = ? WHERE id = ?';
        $this->dao->execute($query, [$newName, $id]);
    }

    public function updateEmail(int $id, string $updatedEmail): void
    {
        $query = 'UPDATE users SET email = ? WHERE id = ?';
        $this->dao->execute($query, [$updatedEmail, $id]);
    }

    public function updatePassword(int $id, string $newPassword): void
    {
        $query = 'UPDATE users SET password = ? WHERE id = ?';
        $this->dao->execute($query, [$newPassword, $id]);
    }

    public function updateDescription(string $username, string $bio): void
    {
        $query = 'UPDATE users SET bio = ? WHERE username = ?';
        $this->dao->execute($query, [$bio, $username]);
    }

    public function delete(int $id): void
    {
        $query = 'DELETE FROM users WHERE id = ?';
        $this->dao->execute($query, [$id]);
    }
}
