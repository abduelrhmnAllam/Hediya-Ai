<?php

namespace App\Repositories\V1;

use App\Models\People;
use App\Utilities\ResponseHandler;
use App\Utilities\FilterHelper;
use Illuminate\Http\Request;
 use Illuminate\Support\Facades\DB;

class PeopleRepository extends BaseRepository
{
    protected string $logChannel;

    public function __construct(Request $request, People $person)
    {
        parent::__construct($person);
        $this->logChannel = 'persons_logs';
    }

   public function personListing($request)
 {
    try {

      $user = auth('api')->user();


        if (!$user) {
            return ResponseHandler::error('Unauthorized user.', 401);
        }

        $query = $user->persons()->with(['relative', 'interests', 'occasions.occasionName']);

        if ($filters = $request->input('filters')) {
            foreach ($filters as $field => $value) {
                if (in_array($field, ['name', 'gender', 'city'])) {
                    $query->where($field, 'LIKE', "%{$value}%");
                }
            }
        }

        // ✅ الترتيب
        $orderBy = $request->input('order_by', 'id');
        $order = $request->input('order', 'desc');
        $query->orderBy($orderBy, $order);

        // ✅ إلغاء الـ paginate — هيرجع أول 5 فقط
        $persons = $query->limit(5)->get();

       // ✅ الرد النهائي
        return response()->json([
            'status' => 200,
            'code' => 8200,
            'message' => __('common.success'),
            'allPersons' => $persons
        ], 200);


    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 24);
    }
}



public function createPerson(array $validatedRequest)
{
    try {
        // ✅ إنشاء الشخص
        $person = $this->model::create([
            'name'          => $validatedRequest['name'],
            'relative_id'   => $validatedRequest['relative_id'] ?? null,
            'user_id'       => auth('api')->id(),
        ]);

        // ✅ إنشاء المناسبات المتعددة
        if (!empty($validatedRequest['occasions']) && is_array($validatedRequest['occasions'])) {
            foreach ($validatedRequest['occasions'] as $occ) {
                $person->occasions()->create([
                    'occasion_name_id' => $occ['occasion_name_id'],
                    'title' => $occ['title'] ?? 'Occasion for ' . $person->name,
                    'date'  => $occ['date'] ?? null,
                    'type'  => optional(\App\Models\OccasionName::find($occ['occasion_name_id']))->type,
                ]);
            }
        }

        // ✅ ربط الاهتمامات
        if (!empty($validatedRequest['interests']) && is_array($validatedRequest['interests'])) {
            $person->interests()->sync($validatedRequest['interests']);
        }

        // ✅ إعادة الرد JSON
    return response()->json([
    'status' => 200,
    'code' => 8200,
    'message' => __('common.success'),
    'addPerson' => $person->load(['relative', 'interests', 'occasions.occasionName']),
]);


    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error(
            $this->prepareExceptionLog($e),
            500,
            26
        );
    }
}



    public function showPerson(array $validatedRequest)
    {
        try {
            $person = $this->model::with(['relative','interests'])->find($validatedRequest['id']);
            if (!$person) {
                return ResponseHandler::error(__('common.not_found'), 404, 2005);
            }
            return ResponseHandler::success($person, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
        }
    }


    public function updatePerson(array $validatedRequest)
{
    try {
        // ✅ البحث عن الشخص
        $person = $this->model::find($validatedRequest['id']);
        if (!$person) {
            return ResponseHandler::error(__('common.not_found'), 404, 2009);
        }

        // ✅ تحديث بيانات الشخص الأساسية
        $person->update([
            'name'          => $validatedRequest['name'] ?? $person->name,
            'birthday_date' => $validatedRequest['birthday_date'] ?? $person->birthday_date,
            'gender'        => $validatedRequest['gender'] ?? $person->gender,
            'region'        => $validatedRequest['region'] ?? $person->region,
            'city'          => $validatedRequest['city'] ?? $person->city,
            'address'       => $validatedRequest['address'] ?? $person->address,
            'relative_id'   => $validatedRequest['relative_id'] ?? $person->relative_id,
        ]);

        // ✅ تحديث الاهتمامات
        if (isset($validatedRequest['interests'])) {
            $person->interests()->sync($validatedRequest['interests']);
        }

        // ✅ تحديث المناسبات (occasions)
        if (isset($validatedRequest['occasions']) && is_array($validatedRequest['occasions'])) {
            foreach ($validatedRequest['occasions'] as $occasionData) {
                $person->occasions()->updateOrCreate(
                    [

                        'occasion_name_id' => $occasionData['occasion_name_id'] ?? null,
                    ],
                    [
                        'title'            => $occasionData['title'] ?? null,
                        'date'             => $occasionData['date'] ?? null,
                        'type'             => $occasionData['type'] ?? null,
                    ]
                );
            }
        }

        // ✅ تحميل العلاقات المطلوبة
        $person->load(['relative', 'interests', 'occasions.occasionName']);

        // ✅ الرد النهائي بنفس تنسيق النظام عندك
        return response()->json([
            'status' => 200,
            'code' => 8200,
            'message' => __('common.success'),
            'updatePerson' => $person,
        ], 200);

    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
    }
 }


public function deletePerson(array $validatedRequest)
{
    DB::beginTransaction();

    try {
        $person = $this->model::find($validatedRequest['id']);

        if (!$person) {
            return ResponseHandler::error(__('common.errors.not_found'), 404, 2004);
        }

        $person->occasions()->delete();
        $person->interests()->detach();
        $person->delete();

        DB::commit();

        return ResponseHandler::success([], __('common.success'));
    } catch (\Exception $e) {
        DB::rollBack();
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
    }
}

public function personListingWithRelativeOnly()
{
      $user = auth('api')->user();

    $persons = $user->persons()
        ->select('id', 'name', 'relative_id')
        ->with('relative:id,title')
        ->orderBy('id','desc')
        ->limit(50)
        ->get();

    return response()->json([
        'status'=>200,
        'code'=>8200,
        'message'=>'success',
        'reletives'=>$persons
    ]);
}




}
