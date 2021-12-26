<?php

namespace core\admin\extension;

use core\base\controller\Singleton;

class UsersExtension
{
    use Singleton;

    protected $a;

    public function extension($args = [])
    {
        $this->a = 13;
        return $this->a;
    }
}