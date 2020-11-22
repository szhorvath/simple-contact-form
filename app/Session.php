<?php

namespace App;


class Session
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function get($key)
    {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = null;
        }

        return $_SESSION[$key];
    }

    public function unset($key)
    {
        unset($_SESSION[$key]);
    }

    public function token()
    {
        return $_SESSION["csrf_token"] = base64_encode(random_bytes(64));
    }

    public function check($token)
    {
        if ($this->get('csrf_token') !== $token) {
            return false;
        }

        $this->unset('csrf_token');
        return true;
    }
}
