<?php

namespace App\Validation;

use App\Session;
use App\Validation\ErrorHandler;

class Validator
{
    protected $errorHandler;

    protected $session;

    protected $rules = ['csrf', 'required', 'minlength', 'maxlength', 'email', 'phone', 'alpha', 'text'];

    public $messages = [
        'csrf' => 'Token is not valid or missing',
        'required' => 'The :field field is required',
        'minlength' => 'The :field field must be a minimum length of :satisfier character',
        'maxlength' => 'The :field field must be a maximum length of :satisfier character',
        'email' => 'That is not a valid email address',
        'phone' => 'That is not a valid UK phone number',
        'mobile' => 'That is not a valid UK mobile number',
        'alpha' => 'The :field field must contains only letters and numbers',
        'text' => 'The :field can only contains letters, numbers and punctuations',
    ];

    public function __construct(ErrorHandler $errorHandler, Session $session)
    {
        $this->errorHandler = $errorHandler;
        $this->session = $session;
    }

    public function check($items, $rules)
    {
        foreach ($items as $item => $value) {
            if (in_array($item, array_keys($rules))) {
                $this->validate([
                    'field' => $item,
                    'value' => $value,
                    'rules' => $rules[$item],
                ]);
            }
        }

        return $this;
    }

    public function fails()
    {
        return $this->errorHandler->hasErrors();
    }

    protected function validate($item)
    {
        $field = $item['field'];
        foreach ($item['rules'] as $rule => $satisfier) {
            if (in_array($rule, $this->rules)) {
                if (!call_user_func_array([$this, $rule], [$field, $item['value'], $satisfier])) {
                    $this->errorHandler->addError(
                        str_replace([':field', ':satisfier'], [$field, $satisfier], $this->messages[$rule]),
                        $field
                    );
                }
            }
        }
    }

    public function errors()
    {
        return $this->errorHandler;
    }

    protected function csrf($field, $value, $satisfier)
    {
        return $this->session->check($value);
    }

    protected function required($field, $value, $satisfier)
    {
        return !empty(trim($value));
    }

    protected function minlength($field, $value, $satisfier)
    {
        return mb_strlen($value) >= $satisfier;
    }

    protected function maxlength($field, $value, $satisfier)
    {
        return mb_strlen($value) <= $satisfier;
    }

    protected function email($field, $value, $satisfier)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    protected function mobile($field, $value, $satisfier)
    {
        return preg_match('/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/', $value);
    }

    protected function phone($field, $value, $satisfier)
    {
        return preg_match('/^((\(?0\d{4}\)?\s?\d{3}\s?\d{3})|(\(?0\d{3}\)?\s?\d{3}\s?\d{4})|(\(?0\d{2}\)?\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/', $value);
    }

    protected function alpha($field, $value, $satisfier)
    {
        return ctype_alnum($value);
    }

    protected function text($field, $value, $satisfier)
    {
        return preg_match("/^(?=.*[A-Z0-9])[\w.,!\"\'\/$ ]+$/i", $value);
    }
}
