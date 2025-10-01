<?php

namespace App\Repositories\V1;

use App\Models\User;
use App\Utilities\ResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthRepository extends BaseRepository
{
    protected string $logChannel;

    public function __construct(Request $request, User $user)
    {
        parent::__construct($user);
        $this->logChannel = 'auth_logs';
    }

    public function registerUser(array $validatedRequest)
    {
        try {
            $user = $this->model::create([
                'name' => $validatedRequest['first_name'].' '.$validatedRequest['last_name'],
                'email'      => $validatedRequest['email'],
                'password'   => Hash::make($validatedRequest['password']),
            ]);

            // Issue access token
            $dataToReturn['token'] = $user->createToken('authToken')->accessToken;
            $dataToReturn['user'] = $user;
            return ResponseHandler::success($dataToReturn, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500,14);
        }
    }

    public function loginUser(array $validatedRequest)
    {
        try {

            $user = $this->model::where('email', $validatedRequest['email'])->first();

            if (!$user || !Hash::check($validatedRequest['password'], $user->password)) {
                return ResponseHandler::error(__('common.errors.invalidCreds'), 401);
            }

            $dataToReturn['token'] = $user->createToken('authToken')->accessToken;;
            $dataToReturn['user'] = $user;
            return ResponseHandler::success($dataToReturn, __('common.success'));

        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500,14);
        }
    }

    public function logoutUser()
    {
        try {

            auth()->guard('api')->user()->token()->revoke();

            return ResponseHandler::success([],__('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500,14);
        }
    }

}
