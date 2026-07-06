<?php

declare(strict_types=1);

namespace Core;

use Core\Interfaces\RenderInterface;
use Dto\Dto;
use Services\Auth;
use Validation\Validator;

class Controller
{
    protected RenderInterface $render;
    protected Auth $auth;
    protected ?Container $container = null;

    public function __construct(?RenderInterface $render = null, ?Auth $auth = null)
    {
        $this->render = $render ?? new Render();
        $this->auth = $auth ?? new Auth(new SessionManager());
    }

    public function setContainer(?Container $container): void
    {
        $this->container = $container;
    }

    public function validate(Dto $dto): array|true
    {
        $validator = new Validator();
        $validator->validate($dto);
        $errors = $validator->getErrors();
        if (!empty($errors)) {
            return $errors;
        }
        return true;
    }

    protected function requireAuth(): int
    {
        $userId = $this->auth->getUserId();
        if (!$userId) {
            $this->jsonError('Unauthorized', 401);
            exit;
        }
        return $userId;
    }

    protected function validateOrError(Dto $dto): void
    {
        $validation = $this->validate($dto);
        if ($validation !== true) {
            $this->jsonError('Validation failed', 422);
            exit;
        }
    }

    protected function requireAdmin(): void
    {
        if (!$this->auth->isAdmin()) {
            $this->jsonError('Forbidden', 403);
            exit;
        }
    }

    protected function getRequestBody(): array
    {
        $data = $_POST;
        if (empty($data)) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $data;
    }

    protected function jsonResult(array $result, int $successCode = 200): void
    {
        if ($result['success']) {
            $this->jsonResponse($result, $successCode);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }

    protected function jsonResponse(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
}
