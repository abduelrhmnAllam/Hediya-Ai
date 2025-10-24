<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller as Controller;
use App\Repositories\V1\AuthRepository;
use App\Utilities\ResponseHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    protected AuthRepository $authRepository;

    /**
     * AuthController constructor.
     *
     * @param AuthRepository $authRepository
     */
    public function __construct(AuthRepository $authRepository, Request $request)
    {
        parent::__construct($request);
        $this->authRepository = $authRepository;

    }

    /**
     * Handle user registration.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {

        $rules = [
            'first_name' => 'sometimes|required|regex:/^\\S+$/',
            'last_name'  => 'sometimes|required|regex:/^\\S+$/',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6',
        ];

        $validated = $this->validated($rules, $request->all());

        if ($validated->fails()) {
           return ResponseHandler::error(__('common.errors.validation'), 422, 12, $validated->errors());
        }

        return $this->authRepository->registerUser($validated->validated());
    }

    public function login(Request $request): JsonResponse
    {
        $rules = [
            'email'    => 'required|email',
            'password' => 'required'
        ];

        $validated = $this->validated($rules, $request->all());

        if ($validated->fails()) {
            ResponseHandler::error(__('common.errors.validation'), 422, 12, $validated->errors());
        }

        return $this->authRepository->loginUser($validated->validated());
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->authRepository->logoutUser();
    }


    public function checkEmail(Request $request): JsonResponse
{
    $rules = ['email' => 'required|email'];
    $validated = $this->validated($rules, $request->all());
    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 12, $validated->errors());
    }
    return $this->authRepository->checkEmailExists($validated->validated());
}

public function verifyPin(Request $request): JsonResponse
{
    $rules = ['email' => 'required|email', 'code' => 'required|digits:6'];
    $validated = $this->validated($rules, $request->all());
    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 12, $validated->errors());
    }
    return $this->authRepository->verifyPin($validated->validated());
}


public function resendPin(Request $request): JsonResponse
{
    $rules = [
        'email' => 'required|email',
    ];

    $validated = $this->validated($rules, $request->all());

    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 12, $validated->errors());
    }

    return $this->authRepository->resendVerificationCode($validated->validated());
}


public function completeRegister(Request $request): JsonResponse
{
    $rules = [
        'email' => 'required|email',
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'password' => 'required|min:6'
    ];
    $validated = $this->validated($rules, $request->all());
    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 12, $validated->errors());
    }
    return $this->authRepository->completeRegistration($validated->validated());
}

public function submitPassword(Request $request): JsonResponse
{
    $rules = ['email' => 'required|email', 'password' => 'required'];
    $validated = $this->validated($rules, $request->all());
    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 12, $validated->errors());
    }
    return $this->authRepository->submitPasswordLogin($validated->validated());
}

    public function redirectToGoogle()
{
    return Socialite::driver('google')->stateless()->redirect();
}

public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(uniqid()), // random password
                'is_verified' => true,
            ]);
        }

        $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'status' => true,
            'message' => 'Google login successful',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Google login failed: ' . $e->getMessage(),
        ], 500);
    }
}


}
