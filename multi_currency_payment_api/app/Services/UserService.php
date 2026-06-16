<?php

namespace App\Services;

use App\Models\User;
use Override;

class UserService extends BaseSimpleCRUDService
{
    public function __construct(User $model)
    {
        
        parent::__construct($model);
    }
    
    public function listEmployees(?array $filters = null)
    {
        
    }
}