<?php

namespace App\Models;

class User
{
    private string $username;
    private string $password;
    private float $balance;
    private ?int $id;

    public function __construct(
        string $username,
        string $password,
        float  $balance = 1000.0,
        ?int   $id = null
    )
    {
        $this->username = $username;
        $this->password = $password;
        $this->balance = $balance;
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function login(string $password): bool
    {
        return md5($password) === $this->password;
    }
}