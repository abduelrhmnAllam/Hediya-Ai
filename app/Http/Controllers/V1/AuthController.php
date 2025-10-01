<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller as Controller;
use App\Repositories\V1\AuthRepository;
use App\Utilities\ResponseHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
}
