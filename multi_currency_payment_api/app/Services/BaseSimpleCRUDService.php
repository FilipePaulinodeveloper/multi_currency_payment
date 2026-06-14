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
        $this->cacheKey = class_basename($model) . '_index_cache';
    }

    public function index()
    {
        
        return Cache::rememberForever($this->cacheKey, function () {
            return $this->model->paginate(15);
        });
       
    }

    public function store(array $data)
    { 

        $data = $this->model->create($data);            
        Cache::forget($this->cacheKey);
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
        Cache::forget($this->cacheKey);
        return $record;
         
    }

    public function destroy(string $id)
    {       
        $record = $this->model->findOrFail($id);          
        $record->delete();            
        Cache::forget($this->cacheKey);     
    }
}