<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\StudentDataCollection;
use App\Http\Resources\StudentDataResource;
use App\Models\StudentData;
use Illuminate\Http\Request;

class StudentDataController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = StudentData::class;
        $this->resourceClass = StudentDataResource::class;
        $this->collectionClass = StudentDataCollection::class;
        
        $this->storeRules = [
            'education_level' => 'required|string|max:255',
            'interests' => 'nullable|string',
            'user_id' => 'required|exists:users,id|unique:student_data',
        ];
        
        $this->updateRules = [
            'education_level' => 'sometimes|required|string|max:255',
            'interests' => 'nullable|string',
            'user_id' => 'sometimes|required|exists:users,id|unique:student_data,user_id,{id}',
        ];
    }
} 