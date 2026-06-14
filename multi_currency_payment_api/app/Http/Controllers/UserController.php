<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends BaseSimpleCRUDController
{
    public function __construct(UserService $service)
    {              

        parent::__construct($service);
    }
}
