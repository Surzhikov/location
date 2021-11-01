<?php

namespace Surzhikov\Location;
use RuntimeException;
 
class LocationQueryBuilderException extends RuntimeException
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}