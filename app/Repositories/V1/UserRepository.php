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

    /** 🧩 List Users */
    public function userListing($request)
    {
        try {
            $query = $this->model::query();

            if ($filters = $request->input('filters')) {
                foreach ($filters as $field => $value) {
                    if (in_array($field, ['name', 'email', 'mobile'])) {
                        $query->where($field, 'LIKE', "%{$value}%");
                    }
                }
            }

            $orderBy = $request->input('order_by', 'id');
            $order = $request->input('order', 'desc');
            $query->orderBy($orderBy, $order);

            $rpp = $request->input('rpp', 10);
            $users = $query->paginate($rpp);

            return ResponseHandler::success($users, __('common.success'));
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

            $updateData = [
                'mobile' => $validatedRequest['mobile'] ?? $user->mobile,
                'address' => $validatedRequest['address'] ?? $user->address,
                'is_verified' => $validatedRequest['is_verified'] ?? $user->is_verified,
            ];

            if (isset($validatedRequest['first_name']) || isset($validatedRequest['last_name'])) {
                $firstName = $validatedRequest['first_name'] ?? explode(' ', $user->name)[0] ?? '';
                $lastName = $validatedRequest['last_name'] ?? explode(' ', $user->name)[1] ?? '';
                $updateData['name'] = trim($firstName . ' ' . $lastName);
            }

            if (isset($validatedRequest['email'])) {
                $updateData['email'] = $validatedRequest['email'];
            }

            if (isset($validatedRequest['password'])) {
                $updateData['password'] = $validatedRequest['password'];
            }

            $user->update($updateData);

            return ResponseHandler::success($user, __('common.success'));
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
