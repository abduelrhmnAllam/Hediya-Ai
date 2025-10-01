<?php

namespace App\Repositories\V1;

use App\Models\Occasion;
use App\Utilities\ResponseHandler;

class OccasionRepository
{
    protected $model;

    public function __construct(Occasion $model)
    {
        $this->model = $model;
    }

    public function createOccasion(array $data)
    {
        try {
            $occasion = $this->model::create($data);

            return ResponseHandler::success($occasion, __('common.success'));
        } catch (\Exception $e) {
            return ResponseHandler::error($e->getMessage(), 500, 26);
        }
    }

    public function getPersonOccasions($personId)
    {
        $occasions = $this->model::where('person_id', $personId)->get();
        return ResponseHandler::success($occasions, __('common.success'));
    }
}
