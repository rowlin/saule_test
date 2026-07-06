<?php

namespace Exceptions;

use Core\Render;
use Exception;
use Throwable;

abstract class ViewException extends Exception {

    public function __construct($message, $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    abstract public function getLayout(): string;

    public function render() {
        return (new Render)->view($this->getLayout(), ['message' => $this->getMessage(), 'code' => $this->getCode()]);
    }
}
