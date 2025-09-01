<?php

namespace App\Core;

class DataState
{
    public $data;
    public $error;
    public int $code;

    public bool $isSuccess;

    public static function success($data, $code = 200): self
    {
        $instance = new self();
        $instance->isSuccess = true;
        $instance->data = $data;
        $instance->code = $code;
        return $instance;
    }

    public static function error($error, $code = 400): self
    {
        $instance = new self();
        $instance->isSuccess = false;
        $instance->error = $error;
        $instance->code = $code;
        return $instance;
    }

    public function fold($onSuccess, $onError)
    {
        if ($this->isSuccess) {
            return $onSuccess($this->data, $this->code);
        } else {
            return $onError($this->error, $this->code);
        }
    }
}
