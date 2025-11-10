<?php

namespace App\Repositories\V1;
use App\Models\People;

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

    public function addOccasion(array $validated)
    {
    try {
        $person = People::find($validated['id']);
        if (!$person) {
            return ResponseHandler::error(__('common.not_found'),404,2009);
        }

        $occasion = $person->occasions()->create([
            'occasion_name_id' => $validated['occasion_name_id'],
            'title' => $validated['title'] ?? null,
            'date'  => $validated['date'] ?? null,
            'type'  => $validated['type'] ?? null,
        ]);

        // load relation relative فقط
        $person->load('relative:id,title');

        return ResponseHandler::success([
            'person'   => $person,
            'occasion' => $occasion
        ], __('common.success'));

    } catch (\Exception $e) {
        return ResponseHandler::error($e->getMessage(),500,26);
    }
   }

   public function getUserOccasions($userId)
{
    try {
        // نجيب كل الأشخاص اللي تابعين للمستخدم
        $people = People::where('user_id', $userId)->pluck('id');

        // نجيب كل المناسبات اللي تخص الأشخاص دي
        $occasions = $this->model::with(['person.relative'])
            ->whereIn('person_id', $people)
            ->orderBy('date', 'asc')
            ->get();

       return response()->json([
    'status' => 200,
    'code' => 8200,
    'message' => __('common.success'),
    'userOccasions' => $occasions,
]);


    } catch (\Exception $e) {
        return ResponseHandler::error($e->getMessage(), 500, 26);
    }
}

public function searchUserOccasionsByDate($userId, $date)
{
    try {
        // نجيب كل الأشخاص اللي تابعين للمستخدم
        $people = People::where('user_id', $userId)->pluck('id');

        // نجيب المناسبات اللي تخص الأشخاص دي في التاريخ المطلوب
        $occasions = $this->model::with(['person.relative'])
            ->whereIn('person_id', $people)
            ->whereDate('date', $date) // فلترة حسب التاريخ
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'status' => 200,
            'code' => 8200,
            'message' => __('common.success'),
            'userOccasions' => $occasions
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'code' => 26,
            'message' => $e->getMessage(),
        ]);
    }
}


}
