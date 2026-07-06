<?php

declare(strict_types=1);

namespace Exceptions;

use Exception;

class NotFoundViewException extends ViewException
{
  public function getLayout(): string
  {
    return '404';
  }
} 
