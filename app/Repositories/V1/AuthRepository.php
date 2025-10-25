<?php

namespace App\Repositories\V1;

use App\Models\User;
use App\Utilities\ResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerificationCodeMail;

class AuthRepository extends BaseRepository
{
    protected string $logChannel;

    public function __construct(Request $request, User $user)
    {
        parent::__construct($user);
        $this->logChannel = 'auth_logs';
    }


public function checkEmailExists(array $data)
{
    try {
        $email = $data['email'];

        // هل المستخدم موجود ومفعل؟
        $user = $this->model::where('email', $email)->first();

        if ($user && $user->is_verified) {
            // مستخدم موجود ومفعل → يدخل على login flow
            return ResponseHandler::success([
                'exists' => true,
                'email' => $email,
                'message' => 'Email exists, please submit password.'
            ]);
        }

        // لو المستخدم موجود بس مش متفعل → حدّث كود التفعيل بدل ما تعمل insert جديد
        $code = rand(100000, 999999);
        if ($user && !$user->is_verified) {
            $user->update(['verification_code' => $code]);
        } else {
            // مستخدم جديد تمامًا → أضفه لأول مرة
            $this->model::create([
                'name' => 'TempUser_' . Str::random(5),
                'email' => $email,
                'verification_code' => $code,
                'is_verified' => false,
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        // إرسال الكود بالبريد
        Mail::to($email)->send(new VerificationCodeMail($code));

        return ResponseHandler::success([
            'exists' => false,
            'email' => $email,
            'message' => 'Verification code sent successfully.'
        ]);
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 14);
    }
}



public function verifyPin(array $data)
{
    try {
        $user = $this->model::where('email', $data['email'])
            ->where('verification_code', $data['code'])
            ->first();

        if (!$user) {
            return ResponseHandler::error('Invalid or expired code', 400);
        }

        $user->update([
            'is_verified' => true,
            'verification_code' => null,
        ]);

        return ResponseHandler::success(['verified' => true, 'message' => 'Email verified successfully.']);
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 14);
    }
}

public function resendVerificationCode(array $data)
{
    try {
        $email = $data['email'];
        $user = $this->model::where('email', $email)->first();

        if (!$user) {
            return ResponseHandler::error('Email not found. Please register first.', 404);
        }

        if ($user->is_verified) {
            return ResponseHandler::error('Email already verified.', 400);
        }

        // Generate new code
        $code = rand(100000, 999999);

        // Update user with new verification code
        $user->update([
            'verification_code' => $code,
        ]);

        // Send email (reusing the same Mailable)
        \Mail::to($email)->send(new \App\Mail\VerificationCodeMail($code));

        return ResponseHandler::success([
            'email' => $email,
            'message' => 'New verification code sent successfully.',
        ]);
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 14);
    }
}


   public function completeRegistration(array $data)
{
    try {
        // البحث عن المستخدم
        $user = $this->model::where('email', $data['email'])->first();

        if (!$user) {
            return ResponseHandler::error('Email not found. Please check.', 404);
        }

        if (!$user->is_verified) {
            return ResponseHandler::error('Email not verified. Please verify first.', 403);
        }

        // تحديث بيانات المستخدم
        $user->update([
            'name' => $data['name']  ?? null,
            'password' => Hash::make($data['password']),
            'mobile' => $data['mobile'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        // إنشاء توكن الدخول
        $token = $user->createToken('authToken')->accessToken;

        $dataToReturn = [
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'mobile', 'address', 'is_verified', 'created_at']),
        ];

        return ResponseHandler::success($dataToReturn, 'Registration completed successfully.');
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 14);
    }
}



public function submitPasswordLogin(array $data)
{
    try {
        $user = $this->model::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return ResponseHandler::error(__('common.errors.invalidCreds'), 401);
        }

        // successful login — return token
        $token = $user->createToken('authToken')->accessToken;

        return ResponseHandler::success([
            'token' => $token,
            'user' => $user
        ], __('common.success'));
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 14);
    }
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
public function requestPasswordReset(array $data)
{
    try {
        $email = $data['email'];
        $user = $this->model::where('email', $email)->first();

        if (!$user) {
            return ResponseHandler::error('Email not found.', 404);
        }

        $code = rand(100000, 999999);
        $user->update(['verification_code' => $code]);

        \Mail::to($email)->send(new \App\Mail\VerificationCodeMail($code));

        return ResponseHandler::success([
            'email' => $email,
            'message' => 'Reset code sent successfully.'
        ]);
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 14);
    }
}

public function verifyResetCode(array $data)
{
    try {
        $user = $this->model::where('email', $data['email'])
            ->where('verification_code', $data['code'])
            ->first();

        if (!$user) {
            return ResponseHandler::error('Invalid or expired code.', 400);
        }

        // وسم المستخدم بأنه جاهز لإعادة التعيين
        $user->update(['is_verified' => true, 'verification_code' => null]);

        return ResponseHandler::success([
            'email' => $user->email,
            'message' => 'Verification successful. You can now reset your password.'
        ]);
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 14);
    }
}

public function resetPassword(array $data)
{
    try {
        $user = $this->model::where('email', $data['email'])->first();

        if (!$user) {
            return ResponseHandler::error('User not found.', 404);
        }

        if (!$user->is_verified) {
            return ResponseHandler::error('Verification required before reset.', 403);
        }

        $user->update([
            'password' => \Hash::make($data['password']),
        
        ]);

        return ResponseHandler::success([
            'message' => 'Password has been reset successfully.'
        ]);
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 14);
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
