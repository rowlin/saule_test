<?php

namespace Controllers;

use Core\Controller;
use Dto\LoginDto;
use Models\User;

class LoginController extends Controller
{
    public function index(): void
    {
        $this->render->view('login');
    }

    public function login(LoginDto $formData): void
    {
        $this->validateOrError($formData);
        $userModel = new User();
        $user = $userModel->findByLogin($formData->login);

        if (!$user || !password_verify($formData->password, $user['password'])) {
            $this->jsonError('Invalid credentials', 401);
            return;
        }

        if ($user['status'] === 'blocked') {
            $this->jsonError('User is blocked', 403);
            return;
        }

        $this->auth->login($user);
        $this->jsonResponse([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'is_admin' => (bool) $user['is_admin'],
            ],
        ]);
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->jsonResponse(['success' => true]);
    }
}
