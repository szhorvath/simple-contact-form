<?php

namespace App\Validation;


class ErrorHandler
{
    protected $errors = [];

    public function addError($error, $key = null)
    {
        if ($key) {
            $this->errors[$key][] = $error;
        } else {
            $this->errors[] = $error;
        }
    }

    public function hasErrors()
    {
        return count($this->all()) ? true : false;
    }

    public function all($key = null)
    {
        return $this->errors[$key] ?? $this->errors;
    }

    public function first($key)
    {
        return $this->all()[$key][0] ?? '';
    }
}
