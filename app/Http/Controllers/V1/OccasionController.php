<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Repositories\V1\OccasionRepository;
use App\Utilities\ResponseHandler;
use Illuminate\Http\Request;

class OccasionController extends Controller
{
    protected $occasionRepository;

    public function __construct(OccasionRepository $occasionRepository)
    {
        $this->occasionRepository = $occasionRepository;
    }

    public function store(Request $request)
    {
        $rules = [
            'person_id' => 'required|exists:people,id',
            'title'     => 'required|string',
            'date'      => 'required|date',
            'type'      => 'sometimes|string',
        ];

        $validated = $this->validated($rules, $request->all());
        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
        }

        return $this->occasionRepository->createOccasion($validated->validated());
    }

    public function show($personId)
    {
        return $this->occasionRepository->getPersonOccasions($personId);
    }
}
