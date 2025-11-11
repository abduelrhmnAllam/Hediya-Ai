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


    public function GetAllOccassions(Request $request, $type)
{
    $validated = $this->validated([
        'title'     => 'sometimes|string',
        'from_date' => 'sometimes|date',
        'to_date'   => 'sometimes|date',
    ], $request->all());

    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
    }

    if(! in_array($type, ['upcoming','past','all'])){
        return ResponseHandler::error('Invalid type value',422,2001,[
            'type' => ['type must be: upcoming, past, all']
        ]);
    }

    $userId = auth('api')->id();

    return $this->occasionRepository->searchSmart($userId, $type, $request);
}



    public function searchByDate(Request $request)
{
    $request->validate([
        'date' => 'required|date',
    ]);

    $userId = auth('api')->id();

    return $this->occasionRepository->searchUserOccasionsByDate($userId, $request->date);
}


    public function getUpcomingOccasions(Request $request)
{
    $userId = auth('api')->id();

    return $this->occasionRepository->getUpcoming($userId);
}

public function getPastOccasions(Request $request)
{
    $userId = auth('api')->id();

    return $this->occasionRepository->getPast($userId);
}



    public function addNewOccassion($id, Request $request)
        {
            $request->merge(['id'=>$id]);

             $rules = [
                 'id' => 'required|integer|exists:people,id',
                 'occasion_name_id' => 'required|integer|exists:occasion_names,id',
                 'title' => 'sometimes|string|max:255',
                 'date' => 'sometimes|date',
                 'type' => 'sometimes|string|max:100',
             ];

             $validated = $this->validated($rules,$request->all());
             if ($validated->fails()) {
                 return ResponseHandler::error(__('common.errors.validation'),422,2007,$validated->errors());
             }

            return $this->occasionRepository->addOccasion($validated->validated());
        }

    public function updatePersonOccasion($person_id, $occasion_id, Request $request)
{
    $rules = [
        'occasion_name_id' => 'sometimes|integer|exists:occasion_names,id',
        'title' => 'sometimes|string|max:255',
        'date' => 'sometimes|date',
        'type' => 'sometimes|string|max:100',
    ];

    $validated = $this->validated($rules,$request->all());
    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'),422,2007,$validated->errors());
    }

    return $this->occasionRepository->updateOccasionForPerson($person_id, $occasion_id, $validated->validated());
}


public function deletePersonOccasion($person_id, $occasion_id)
{
    return $this->occasionRepository->deleteOccasionForPerson($person_id, $occasion_id);
}


}
