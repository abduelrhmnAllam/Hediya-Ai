<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Repositories\V1\UserRepository;
use App\Utilities\ResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository, Request $request)
    {
        parent::__construct($request);
        $this->userRepository = $userRepository;
    }

    /** ==================== GET /api/users ==================== */
    public function index(Request $request)
    {
        $validated = $this->validated([
            'filters' => 'sometimes|array',
            'filters.name' => 'sometimes|string',
            'filters.email' => 'sometimes|string',
            'order_by' => 'sometimes|in:id,name,email,created_at',
            'order' => 'sometimes|in:asc,desc',
            'rpp' => 'sometimes|integer|min:1',
            'page' => 'sometimes|integer|min:1',
        ], $request->all());

        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
        }

        return $this->userRepository->userListing($request);
    }

    /** ==================== POST /api/users ==================== */
    public function store(Request $request)
    {
        $validated = $this->validated([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6',
            'mobile'     => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
        ], $request->all());

        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
        }

        $data = $validated->validated();
        $data['password'] = Hash::make($data['password']);

        return $this->userRepository->createUser($data);
    }

    /** ==================== GET /api/users/{id} ==================== */
    public function show($id, Request $request)
    {
        $validated = $this->validated([
            'id' => 'required|integer|exists:users,id',
        ], ['id' => $id]);

        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 3011, $validated->errors());
        }

        return $this->userRepository->showUser($validated->validated());
    }

    /** ==================== PUT /api/users/{id} ==================== */

    public function update(Request $request, string $id)
{
    $data = $request->merge(['id' => $id])->all();

    $validated = $this->validated([
        'id' => 'required|integer|exists:users,id',
        'name' => 'sometimes|string|max:100',

        'email'      => 'sometimes|email|unique:users,email,' . $id,
        'password'   => 'sometimes|min:6',
        'mobile'     => 'nullable|string|max:20',
        'address'    => 'nullable|string|max:255',
        'is_verified' => 'sometimes|boolean',
        'gift_budgets' => 'sometimes|numeric|min:0',
        'often_buy' => 'sometimes|string|max:50',
        'is_completed' => 'sometimes|boolean',
        'interests' => 'sometimes|array',
        'interests.*' => 'integer|exists:interests,id',
    ], $data);

    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 3001, $validated->errors());
    }

    $validatedData = $validated->validated();

    // ✅ هشفر الباسورد فقط لو فعلاً اتبعت
    if (!empty($validatedData['password'])) {
        $validatedData['password'] = Hash::make($validatedData['password']);
    } else {
        unset($validatedData['password']);
    }

    return $this->userRepository->updateUser($validatedData);
}


    /** ==================== DELETE /api/users/{id} ==================== */
    public function destroy(string $id)
    {
        $validated = $this->validated([
            'id' => 'required|integer|exists:users,id',
        ], ['id' => $id]);

        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 3011, $validated->errors());
        }

        return $this->userRepository->deleteUser($validated->validated());
    }
}
