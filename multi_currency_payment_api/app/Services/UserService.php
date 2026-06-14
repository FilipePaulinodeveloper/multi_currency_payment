<?php

namespace App\Services;

use App\Models\User;    

class UserService extends BaseSimpleCRUDService
{
    public function __construct(User $model)
    {
        
        parent::__construct($model);
    }
}