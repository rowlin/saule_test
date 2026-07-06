<?php

namespace Services;

use Core\SessionManager;

class Auth
{
    public function __construct(private SessionManager $sessionManager)
    {
    }

    public function isLogged(): bool
    {
        return $this->sessionManager->has("user");
    }

    public function isAdmin(): bool
    {
        $user = $this->sessionManager->get("user");
        return $user && ($user['is_admin'] ?? false);
    }

    public function getUser(): ?array
    {
        return $this->sessionManager->get("user");
    }

    public function getUserId(): ?int
    {
        $user = $this->getUser();
        return $user ? (int) $user['id'] : null;
    }

    public function login(array $user): void
    {
        $this->sessionManager->set("user", $user);
    }

    public function logout(): void
    {
        $this->sessionManager->remove("user");
    }
}
