<?php

namespace App\Http\Controllers;

use App\Services\BaseSimpleCRUDService;
use Error;
use Faker\Core\Uuid;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;

use App\Traits\ErrorHandlerTrait;


Abstract class BaseSimpleCRUDController extends Controller
{
    
    use AuthorizesRequests, ValidatesRequests, ErrorHandlerTrait;

    protected BaseSimpleCRUDService $service;

    public function __construct(BaseSimpleCRUDService $service)
    {
        $this->service = $service;
    }

    /**
     * Resolve o FormRequest específico baseado no nome da controller
     * Exemplo: UserController -> UserRequest
     */
    protected function getFormRequest(): ?FormRequest
    {
        $controllerName = class_basename(static::class);
        $requestName = str_replace('Controller', 'Request', $controllerName);
        $requestClass = "App\\Http\\Requests\\{$requestName}";

        $request = class_exists($requestClass) ? $requestClass : null;
        if (!$request) {
            throw new \Exception("FormRequest {$requestClass} não encontrado para " . class_basename(static::class));
        }
        $request = app()->make($requestClass);
        return $request;
    }

    /**
     * Gera uma mensagem de sucesso personalizada baseada no modelo e ação
     */
    protected function getSuccessMessage(string $action = 'created'): string
    {
        $controllerName = str_replace('Controller', '', class_basename(static::class));
        
        $actions = [
            'created' => 'created successfully',
            'updated' => 'updated successfully',
            'deleted' => 'deleted successfully',
            'retrieved' => 'retrieved successfully',
            'listed' => 'listed successfully',
        ];
        
        $actionText = $actions[$action] ?? $action;
        
        return "{$controllerName} {$actionText}";
    }

    public function index()
    {
        $dados = $this->service->index();
        return response()->json([            
            'data' => $dados
        ], 200);
    }

    public function store()
    {
        $request = $this->getFormRequest();
        
        $dados = $this->service->store($request->validated());
        return response()->json([
            'message' => $this->getSuccessMessage('created'),
            'data' => $dados
        ], 201);
    }

    public function show(string $id)
    { 
        $dado = $this->service->show($id);
        return response()->json([            
            'data' => $dado
        ], 200);
    }

    public function update(string $id)
    {      
            $request = $this->getFormRequest();                        
            $dados = $this->service->update($id, $request->validated()); 
            return response()->json([
                'message' => $this->getSuccessMessage('updated'),
                'data' => $dados
            ], 200);
    }

    public function destroy(string $id)
    {
         $this->service->destroy($id);
        return response()->json([
            'message' => $this->getSuccessMessage('deleted'),            
        ], 200);
    }
}
