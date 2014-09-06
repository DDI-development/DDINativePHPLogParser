<?php

class Request
{
    public $_post = array();
    public $_get = array();
    public $_files = array();

    public function __construct()
    {
        $this->_get = $this->sanitize($_GET);
        $this->_post = $this->sanitize($_POST);
        $this->_files = $this->sanitize($_FILES);
        $this->type = $_SERVER['REQUEST_METHOD'];
    }

    public function sanitize($arg)
    {
        if (is_array($arg)) {
            foreach ($arg as $key => $value) {
                unset($arg[$key]);
                $arg[$this->sanitize($key)] = $this->sanitize($value);
            }
        } else {
            $arg = htmlspecialchars($arg, ENT_COMPAT, 'UTF-8');
        }
        return $arg;
    }
}
