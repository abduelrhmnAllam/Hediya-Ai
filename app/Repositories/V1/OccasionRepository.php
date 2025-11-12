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



    public function updateOccasionForPerson($person_id, $occasion_id, $data)
{
    try {
        $occasion = $this->model::where('id', $occasion_id)
            ->where('person_id', $person_id)
            ->with('person.relative')
            ->first();

        if (!$occasion) {
            return ResponseHandler::error(__('common.not_found'), 404, 2009);
        }

        // ✅ لو فيه relative_id في البيانات، نحدث الشخص أولاً
        if (isset($data['relative_id'])) {
            $person = $occasion->person;
            if ($person) {
                $person->relative_id = $data['relative_id'];
                $person->save();
            }
            unset($data['relative_id']); // نمنع دخوله لتحديث المناسبة نفسها
        }

        // ✅ نحدّث بيانات المناسبة نفسها
        $occasion->update($data);

        // ✅ نرجّع البيانات بعد التحميل الكامل
       $occasion->load('person.relative');

        $person = $occasion->person;
        return response()->json([
    'status'  => 200,
    'code'    => 8200,
    'message' => __('common.success'),
    'person'   => $person,
            'occasion' => $occasion
]);
    

    } catch (\Exception $e) {
        return ResponseHandler::error($e->getMessage(), 500, 26);
    }
}



public function deleteOccasionForPerson($person_id, $occasion_id)
{
    try {

        $occasion = $this->model::where('id', $occasion_id)
            ->where('person_id', $person_id)
            ->with('person.relative')
            ->first();

        if(!$occasion){
            return ResponseHandler::error(__('common.not_found'),404,2009);
        }

        $person = $occasion->person;
        $person->load('relative:id,title');

        $occasion->delete();

        return ResponseHandler::success([
            'person'   => $person,
            'occasion' => null
        ], __('common.success'));

    } catch (\Exception $e) {
        return ResponseHandler::error($e->getMessage(),500,26);
    }
}


public function searchByDateRange($userId,$request)
{
    $people = People::where('user_id',$userId)->pluck('id');

    $query = $this->model::with(['person.relative','occasionName'])
        ->whereIn('person_id',$people);

    if($request->from_date){
        $query->whereDate('date','>=',$request->from_date);
    }

    if($request->to_date){
        $query->whereDate('date','<=',$request->to_date);
    }

   if($request->filled('title')){
        $query->where('title','LIKE','%'.$request->title.'%');
    }

    return ResponseHandler::success(['userOccasions'=>$query->get()]);
}

   public function searchSmart($userId, $type, $request)
{
    $peopleIDs = People::where('user_id', $userId)->pluck('id');

    $query = $this->model::with(['person.relative', 'occasionName'])
        ->whereIn('person_id', $peopleIDs);

    // --- (1) نوع المناسبات ---
    switch (strtolower(trim($type))) {
        case 'upcoming':
            $query->whereDate('date', '>=', today());
            break;

        case 'past':
            $query->whereDate('date', '<', today());
            break;

        case 'all':
            // no filter
            break;

        default:
            return ResponseHandler::error('Invalid type', 422, 2008);
    }

    // --- (2) فلترة التاريخ لو موجود ---
    if ($request->filled('from_date') && !empty($request->from_date)) {
        $query->whereDate('date', '>=', $request->from_date);
    }

    if ($request->filled('to_date') && !empty($request->to_date)) {
        $query->whereDate('date', '<=', $request->to_date);
    }

    // --- (3) فلترة العنوان الذكي ---
    if ($request->filled('title') && !empty(trim($request->title))) {
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', $request->title);
        $clean = trim(mb_strtolower($clean));
        $words = explode(' ', $clean);

        $query->where(function($q) use ($words) {
            foreach ($words as $w) {
                if ($w != '') {
                    $q->orWhereRaw("LOWER(title) LIKE ?", ['%'.$w.'%']);
                }
            }
        });
    }

    $occasions = $query->orderBy('date', 'asc')->get();

    return response()->json([
        'status' => 200,
        'code'   => 8200,
        'message'=> __('common.success'),
        'userOccasions' => $occasions
    ]);
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


    public function getUpcoming($userId)
    {
         try{
             $people = People::where('user_id',$userId)->pluck('id');

             $start = now()->format('Y-m-d');
             $end   = now()->addDays(10)->format('Y-m-d');

             $occasions = $this->model::with(['person.relative','occasionName'])
                 ->whereIn('person_id',$people)
                 ->whereBetween('date',[$start,$end])
                 ->orderBy('date','asc')
                 ->get();

             return ResponseHandler::success([
                 'userOccasions'=>$occasions
             ], __('common.success'));

         }catch(\Exception $e){
             return ResponseHandler::error($e->getMessage(),500,26);
         }
    }


    public function getPast($userId)
    {
         try{
             $people = People::where('user_id',$userId)->pluck('id');

             $start = now()->subDays(10)->format('Y-m-d');
             $end   = now()->format('Y-m-d');

             $occasions = $this->model::with(['person.relative','occasionName'])
                 ->whereIn('person_id',$people)
                 ->whereBetween('date',[$start,$end])
                 ->orderBy('date','asc')
                 ->get();

             return ResponseHandler::success([
                 'userOccasions'=>$occasions
             ], __('common.success'));

         }catch(\Exception $e){
             return ResponseHandler::error($e->getMessage(),500,26);
         }
    }


}
