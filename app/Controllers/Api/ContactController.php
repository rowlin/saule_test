<?php

namespace Controllers\Api;

use Core\Controller;
use Dto\AddContactDto;
use Models\User;

class ContactController extends Controller
{
    public function add(AddContactDto $dto): void
    {
        $userId = $this->requireAuth();
        if (!in_array($dto->type, CONTACT_TYPES)) {
            $this->jsonError('Type must be phone or email', 400);
            return;
        }

        $userModel = new User();
        try {
            $id = $userModel->addContact($userId, $dto->type, $dto->value);
            $this->jsonResponse(['success' => true, 'id' => $id], 201);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), 400);
        }
    }

    public function delete(): void
    {
        $userId = $this->requireAuth();
        $data = $this->getRequestBody();
        $contactId = (int) ($data['id'] ?? 0);

        if (!$contactId) {
            $this->jsonError('Contact ID required', 400);
            return;
        }

        $userModel = new User();
        $userModel->deleteContact($contactId, $userId);
        $this->jsonResponse(['success' => true]);
    }

    public function adminAdd(): void
    {
        $this->requireAdmin();

        $data = $this->getRequestBody();
        $userId = (int) ($data['userId'] ?? 0);
        $type = $data['type'] ?? '';
        $value = $data['value'] ?? '';

        if (!$userId || !$type || !$value) {
            $this->jsonError('userId, type and value required', 400);
            return;
        }

        if (!in_array($type, CONTACT_TYPES)) {
            $this->jsonError('Type must be phone or email', 400);
            return;
        }

        $userModel = new User();
        try {
            $id = $userModel->addContact($userId, $type, $value);
            $this->jsonResponse(['success' => true, 'id' => $id], 201);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), 400);
        }
    }

    public function adminDelete(): void
    {
        $this->requireAdmin();

        $data = $this->getRequestBody();
        $contactId = (int) ($data['id'] ?? 0);
        $userId = (int) ($data['userId'] ?? 0);

        if (!$contactId || !$userId) {
            $this->jsonError('id and userId required', 400);
            return;
        }

        $userModel = new User();
        $userModel->deleteContact($contactId, $userId);
        $this->jsonResponse(['success' => true]);
    }

    public function adminUserContacts(): void
    {
        $this->requireAdmin();

        $userId = (int) ($_GET['user_id'] ?? 0);
        if (!$userId) {
            $this->jsonError('User ID required', 400);
            return;
        }

        $userModel = new User();
        $this->jsonResponse($userModel->getContacts($userId));
    }
}
