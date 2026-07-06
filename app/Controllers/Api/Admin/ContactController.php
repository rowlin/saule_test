<?php

namespace Controllers\Api\Admin;

use Core\Controller;
use Models\User;

class ContactController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    public function adminAdd(): void
    {
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
        $userId = (int) ($_GET['user_id'] ?? 0);
        if (!$userId) {
            $this->jsonError('User ID required', 400);
            return;
        }

        $userModel = new User();
        $this->jsonResponse($userModel->getContacts($userId));
    }
}
