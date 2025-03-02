<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = Role::class;
        $this->resourceClass = RoleResource::class;
        $this->collectionClass = RoleCollection::class;
        
        $this->storeRules = [
            'name' => 'required|string|max:255|unique:roles',
        ];
        
        $this->updateRules = [
            'name' => 'sometimes|required|string|max:255|unique:roles,name,{id}',
        ];
    }
} 