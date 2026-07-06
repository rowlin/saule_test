<?php

namespace Controllers;

use Core\Controller;

class AdminController extends Controller
{
    public function index(): void
    {
        if ($this->auth->isAdmin()) {
            $this->render->view('admin');
        } else {
            $this->render->view('login');
        }
    }
}
