<?php

namespace Controllers;

use Core\Controller;

class MainController extends Controller
{
    public function index(): void
    {
        if ($this->auth->isLogged()) {
            $this->render->view('client');
        } else {
            $this->render->view('login');
        }
    }
}
