<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProfessorDataCollection;
use App\Http\Resources\ProfessorDataResource;
use App\Models\ProfessorData;
use Illuminate\Http\Request;

class ProfessorDataController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = ProfessorData::class;
        $this->resourceClass = ProfessorDataResource::class;
        $this->collectionClass = ProfessorDataCollection::class;
        
        $this->storeRules = [
            'bio' => 'required|string',
            'specialization' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0',
            'user_id' => 'required|exists:users,id|unique:professors_data',
        ];
        
        $this->updateRules = [
            'bio' => 'sometimes|required|string',
            'specialization' => 'sometimes|required|string|max:255',
            'years_of_experience' => 'sometimes|required|integer|min:0',
            'user_id' => 'sometimes|required|exists:users,id|unique:professors_data,user_id,{id}',
        ];
    }
} 