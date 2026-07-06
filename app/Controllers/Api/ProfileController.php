<?php

namespace Controllers\Api;

use Core\Controller;
use Services\UserService;

class ProfileController extends Controller
{
    public function show(): void
    {
        $userId = $this->requireAuth();
        $service = $this->container->get(UserService::class);
        $profile = $service->getProfile($userId);

        if (!$profile) {
            $this->jsonError('User not found', 404);
            return;
        }

        $this->jsonResponse($profile);
    }
}
