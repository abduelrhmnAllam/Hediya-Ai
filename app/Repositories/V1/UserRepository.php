<?php

namespace App\Repositories\V1;

use App\Models\User;
use App\Utilities\ResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
    protected string $logChannel;

    public function __construct(Request $request, User $users)
    {
        parent::__construct($users);
        $this->logChannel = 'user_logs';
    }

public function userListing($request)
{
    try {
        // ✅ الحصول على المستخدم الحالي من الـ access token
        $user = auth()->user();

        if (!$user) {
            return ResponseHandler::error('Unauthorized user.', 401);
        }

        // ✅ تحميل الأشخاص المرتبطين فقط بهذا المستخدم
        $query = $user->persons()->with(['relative', 'interests', 'occasions.occasionName']);

        // ✅ الفلاتر (بحث بالاسم - النوع - المدينة إلخ)
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
        return ResponseHandler::success([
            'allPersons' => $persons
        ], __('common.success'));

    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 24);
    }
}


  public function createUser(array $validatedRequest)
{
    try {
        $user = $this->model::create([
            'name' => trim(($validatedRequest['first_name'] ?? '') . ' ' . ($validatedRequest['last_name'] ?? '')),
            'email' => $validatedRequest['email'],
            'password' => $validatedRequest['password'],
            'mobile' => $validatedRequest['mobile'] ?? null,
            'address' => $validatedRequest['address'] ?? null,
            'is_verified' => $validatedRequest['is_verified'] ?? false,
            // 🆕 الخانات الجديدة
            'gift_budgets' => $validatedRequest['gift_budgets'] ?? null,
            'often_buy' => $validatedRequest['often_buy'] ?? null,
            'is_completed' => $validatedRequest['is_completed'] ?? false,
        ]);

        return ResponseHandler::success($user, __('common.success'));
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
    }
}

/** 🧩 Update User */
public function updateUser(array $validatedRequest)
{
    try {
        $user = $this->model->find($validatedRequest['id']);
        if (!$user) {
            return ResponseHandler::error(__('common.not_found'), 404, 3005);
        }

        // 🧩 البيانات القابلة للتحديث
        $updateData = [
            'mobile' => $validatedRequest['mobile'] ?? $user->mobile,
            'address' => $validatedRequest['address'] ?? $user->address,
            'is_verified' => $validatedRequest['is_verified'] ?? $user->is_verified,
            'gift_budgets' => $validatedRequest['gift_budgets'] ?? $user->gift_budgets,
            'often_buy' => $validatedRequest['often_buy'] ?? $user->often_buy,
            'is_completed' => $validatedRequest['is_completed'] ?? $user->is_completed,
        ];

         // ✅ تحديث البريد لو تم إرساله
        if (isset($validatedRequest['name'])) {
            $updateData['name'] = $validatedRequest['name'];
        }

        // ✅ تحديث البريد لو تم إرساله
        if (isset($validatedRequest['email'])) {
            $updateData['email'] = $validatedRequest['email'];
        }

        // ✅ تحديث الباسورد فقط لو تم إرساله (ما يتغيرش إلا وقت الحاجة)
        if (!empty($validatedRequest['password'])) {
            $updateData['password'] = $validatedRequest['password'];
        }

        // ✅ تنفيذ التحديث
        $user->update($updateData);

        // ✅ لو في اهتمامات جديدة (interests)
        if (!empty($validatedRequest['interests']) && is_array($validatedRequest['interests'])) {
            $user->interests()->sync($validatedRequest['interests']);
        }

        // ✅ تحميل العلاقات عشان يرجعها في الـ response
        $user->load('interests');

     return ResponseHandler::userSuccess($user->load('interests'));



    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
    }
}


/** 🧩 Show User */
public function showUser(array $validatedRequest)
{
    try {
        $user = $this->model::find($validatedRequest['id']);
        if (!$user) {
            return ResponseHandler::error(__('common.not_found'), 404, 3005);
        }

        // 🆕 تحميل العلاقات لو حابب (مثلاً interests)
        $user->load('interests');

        return ResponseHandler::success($user, __('common.success'));
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
    }
}

    /** 🧩 Delete User */
    public function deleteUser(array $validatedRequest)
    {
        try {
            $user = $this->model::find($validatedRequest['id']);
            if (!$user) {
                return ResponseHandler::error(__('common.not_found'), 404, 3015);
            }

            $user->delete();
            return ResponseHandler::success([], __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
        }
    }
}
