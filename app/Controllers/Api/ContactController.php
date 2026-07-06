<?php

namespace Controllers\Api;

use Core\Controller;
use Dto\AddContactDto;
use Models\User;

class ContactController extends Controller
{
    private int $userId;

    public function __construct()
    {
        parent::__construct();
        $this->userId = $this->requireAuth();
    }

    public function add(AddContactDto $dto): void
    {
        if (!in_array($dto->type, CONTACT_TYPES)) {
            $this->jsonError('Type must be phone or email', 400);
            return;
        }

        $userModel = new User();
        try {
            $id = $userModel->addContact($this->userId, $dto->type, $dto->value);
            $this->jsonResponse(['success' => true, 'id' => $id], 201);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), 400);
        }
    }

    public function delete(): void
    {
        $data = $this->getRequestBody();
        $contactId = (int) ($data['id'] ?? 0);

        if (!$contactId) {
            $this->jsonError('Contact ID required', 400);
            return;
        }

        $userModel = new User();
        $userModel->deleteContact($contactId, $this->userId);
        $this->jsonResponse(['success' => true]);
    }
}
