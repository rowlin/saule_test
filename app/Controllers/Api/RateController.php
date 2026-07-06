<?php

namespace Controllers\Api;

use Core\Controller;
use Services\RateService;

class RateController extends Controller
{
    public function getAll(): void
    {
        $service = $this->container->get(RateService::class);
        $this->jsonResponse($service->getAll());
    }
}
