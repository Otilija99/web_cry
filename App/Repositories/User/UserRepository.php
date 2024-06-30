<?php declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use App\Services\SqliteService;

class UserRepository
{
    private SqliteService $database;

    public function __construct(SqliteService $database)
    {
        $this->database = $database;
    }

    public function add(User $user): void
    {
        $this->database->create(
            "users",
            [
                "username" => $user->getUsername(),
                "password" => $user->getPassword(),
                "balance" => $user->getBalance()
            ]
        );
    }

    public function updateBalance(User $user, float $newBalance): void
    {
        $this->database->update(
            "users",
            ["balance" => $newBalance],
            ["id" => $user->getId()]
        );
        $user->setBalance($newBalance);
    }

    public function findByUsername(string $username): ?User
    {
        $result = $this->database->findBy("users", "username", $username);

        if (count($result) === 1) {
            $userdata = reset($result);
            return new User(
                $userdata['username'],
                $userdata['password'],
                (float)$userdata['balance'],
                (int)$userdata['id']
            );
        }
        return null;
    }

    public function findById(int $id): ?User
    {
        $result = $this->database->findBy(
            "users",
            "id",
            (string)$id
        );

        if (count($result) === 1) {
            $userdata = reset($result);
            return new User(
                $userdata['username'],
                $userdata['password'],
                (float)$userdata['balance'],
                (int)$userdata['id']
            );
        }
        return null;
    }
}
