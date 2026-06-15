<?php

namespace App\Services;

use App\Traits\ErrorHandlerTrait;
use Error;
use Faker\Core\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

abstract class BaseSimpleCRUDService
{
    use ErrorHandlerTrait;

    protected Model $model;
    protected string $cacheKey;

    public function __construct(Model $model)
    {
        $this->model = $model;       
    }

    public function index(?array $filters = null)
    {                           
        return $this->model->paginate(15);              
    }

    public function store(array $data)
    { 

        $data = $this->model->create($data);          
        
        return $data;  

    }

    public function show(string $id)
    {       
        return $this->model->findOrFail($id);
    }

    public function update(string $id, array $data)
    {       
        
        $record = $this->model->findOrFail($id);                  
        $record->update($data);    
            
        return $record;
         
    }

    public function destroy(string $id)
    {       
        $record = $this->model->findOrFail($id);          
        $record->delete();          
         
    }
}