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

        $filters = $request->input('filters', []);

        $query = $user->persons()->with([
            'relative',
            'interests',
            'avatar',
            // ✅ تحميل المناسبات بشرط ذكي
            'occasions' => function ($q) use ($filters) {
                $q->with('occasionName');

                if (!empty($filters['occasion_title'])) {
                    $q->where('title', 'LIKE', "%{$filters['occasion_title']}%");
                }

                if (!empty($filters['occasion_name_id'])) {
                    $q->where('occasion_name_id', $filters['occasion_name_id']);
                }
            }
        ]);

        // ✅ فلترة الأشخاص
        foreach ($filters as $field => $value) {
            if (empty($value) || trim($value) === '') continue;

            switch ($field) {
                case 'name':
                case 'city':
                    $query->where($field, 'LIKE', "%{$value}%");
                    break;

                case 'gender':
                    $query->where('gender', $value);
                    break;

                case 'relative_id':
                    $query->where('relative_id', $value);
                    break;

                case 'interest_id':
                    $query->whereHas('interests', function ($q) use ($value) {
                        $q->where('interests.id', $value);
                    });
                    break;

                case 'occasion_title':
                    $query->whereHas('occasions', function ($q) use ($value) {
                        $q->where('title', 'LIKE', "%{$value}%");
                    });
                    break;

                case 'occasion_name_id':
                    $query->whereHas('occasions', function ($q) use ($value) {
                        $q->where('occasion_name_id', $value);
                    });
                    break;
            }
        }

        // ✅ ترتيب النتيجة
        $orderBy = $request->input('order_by', 'id');
        $order = $request->input('order', 'desc');
        $query->orderBy($orderBy, $order);

        // ✅ عدد النتائج
        $rpp = $request->input('rpp', 10);
        $paginate = $request->boolean('paginate', false);

        $persons = $paginate
            ? $query->paginate($rpp)
            : $query->limit($rpp)->get();

        return response()->json([
            'status' => 200,
            'code'   => 8200,
            'message' => __('common.success'),
            'allPersons' => $persons,
        ], 200);

    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 24);
    }
}


        public function createPerson(array $validatedRequest)
{
    try {
        // ✅ إنشاء الشخص الجديد
        $person = $this->model::create([
            'name'          => $validatedRequest['name'],
            'relative_id'   => $validatedRequest['relative_id'] ?? null,
            'user_id'       => auth('api')->id(),
            'avatar_id'     => $validatedRequest['avatar_id'] ?? null, // 🔹 دعم الـAvatar
            'pic'           => $validatedRequest['pic'] ?? null,       // 🔹 الصورة المرفوعة أو Base64 أو URL
            'birthday_date' => $validatedRequest['birthday_date'] ?? null,
            'gender'        => $validatedRequest['gender'] ?? null,
            'region'        => $validatedRequest['region'] ?? null,
            'city'          => $validatedRequest['city'] ?? null,
            'address'       => $validatedRequest['address'] ?? null,
        ]);

        // ✅ إنشاء المناسبات لو موجودة
        if (!empty($validatedRequest['occasions']) && is_array($validatedRequest['occasions'])) {
            foreach ($validatedRequest['occasions'] as $occ) {
                $person->occasions()->create([
                    'occasion_name_id' => $occ['occasion_name_id'],
                    'title'            => $occ['title'] ?? 'Occasion for ' . $person->name,
                    'date'             => $occ['date'] ?? null,
                    'type'             => optional(\App\Models\OccasionName::find($occ['occasion_name_id']))->type,
                ]);
            }
        }

        // ✅ ربط الاهتمامات
        if (!empty($validatedRequest['interests']) && is_array($validatedRequest['interests'])) {
            $person->interests()->sync($validatedRequest['interests']);
        }
        // ✅ إضافة المرفقات
        if (!empty($validatedRequest['attachments']) && is_array($validatedRequest['attachments'])) {
              foreach ($validatedRequest['attachments'] as $attach) {
                    $person->attachments()->create([
                         'file'          => $attach['file'] ?? null,
                         'product_name'  => $attach['product_name'] ?? null,
                         'product_brand' => $attach['product_brand'] ?? null,
                         'price'         => $attach['price'] ?? null,
                         'store_name'    => $attach['store_name'] ?? null,
                         'note'          => $attach['note'] ?? null,
                            ]);
                }
        }

        // ✅ تحميل العلاقات المطلوبة للعرض
        $person->load(['avatar', 'attachments','relative', 'interests', 'occasions.occasionName']);

        // ✅ الرد النهائي
        return response()->json([
            'status'     => 200,
            'code'       => 8200,
            'message'    => __('common.success'),
            'addPerson'  => $person,
        ]);

    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
    }
}

public function showPerson(array $validatedRequest)
{
    try {
        // ✅ تحميل كل العلاقات المهمة للشخص
        $person = $this->model::with([
            'relative',
            'interests',
            'avatar',
            'attachments',
            'occasions.occasionName'
        ])->find($validatedRequest['id']);

        // ✅ التحقق من وجود الشخص
        if (!$person) {
            return ResponseHandler::error(__('common.not_found'), 404, 2005);
        }

        // ✅ الرد بنفس شكل النظام الموحد
        return response()->json([
            'status'  => 200,
            'code'    => 8200,
            'message' => __('common.success'),
            'person'  => $person,
        ], 200);

    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
    }
}



   public function updatePerson(array $validatedRequest)
    {
        try {
            $person = $this->model::with(['attachments'])->find($validatedRequest['id']);
            if (!$person) {
                return ResponseHandler::error(__('common.not_found'), 404, 2009);
            }

            // ✅ تحديث بيانات الشخص الأساسية
            $person->update([
                'name'          => $validatedRequest['name']          ?? $person->name,
                'avatar_id'     => $validatedRequest['avatar_id']     ?? $person->avatar_id,
                'pic'           => $validatedRequest['pic']           ?? $person->pic,
                'birthday_date' => $validatedRequest['birthday_date'] ?? $person->birthday_date,
                'gender'        => $validatedRequest['gender']        ?? $person->gender,
                'region'        => $validatedRequest['region']        ?? $person->region,
                'city'          => $validatedRequest['city']          ?? $person->city,
                'address'       => $validatedRequest['address']       ?? $person->address,
                'relative_id'   => $validatedRequest['relative_id']   ?? $person->relative_id,
            ]);

            // ✅ تحديث الاهتمامات
            if (isset($validatedRequest['interests'])) {
                $person->interests()->sync($validatedRequest['interests']);
            }

            // ✅ تحديث المناسبات
            if (isset($validatedRequest['occasions']) && is_array($validatedRequest['occasions'])) {
                foreach ($validatedRequest['occasions'] as $occasionData) {
                    $person->occasions()->updateOrCreate(
                        ['occasion_name_id' => $occasionData['occasion_name_id'] ?? null],
                        [
                            'title' => $occasionData['title'] ?? null,
                            'date'  => $occasionData['date'] ?? null,
                            'type'  => $occasionData['type'] ?? null,
                        ]
                    );
                }
            }

            // ✅ تحديث أو إضافة المرفقات
            if (isset($validatedRequest['attachments']) && is_array($validatedRequest['attachments'])) {
                $sentAttachmentIds = collect($validatedRequest['attachments'])
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                $person->attachments()
                    ->whereNotIn('id', $sentAttachmentIds)
                    ->delete();

                foreach ($validatedRequest['attachments'] as $attachData) {
                    $person->attachments()->updateOrCreate(
                        ['id' => $attachData['id'] ?? null],
                        [
                            'file'          => $attachData['file'] ?? null,
                            'product_name'  => $attachData['product_name'] ?? null,
                            'product_brand' => $attachData['product_brand'] ?? null,
                            'price'         => $attachData['price'] ?? null,
                            'store_name'    => $attachData['store_name'] ?? null,
                            'note'          => $attachData['note'] ?? null,
                        ]
                    );
                }
            }

            // ✅ تحميل العلاقات بعد التحديث
            $person->load(['avatar', 'attachments', 'relative', 'interests', 'occasions.occasionName']);

            return response()->json([
                'status'  => 200,
                'code'    => 8200,
                'message' => __('common.success'),
                'updatedPerson'    => $person,
            ]);
        } catch (Exception $e) {
            return ResponseHandler::error($e->getMessage(), 500, 26);
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
