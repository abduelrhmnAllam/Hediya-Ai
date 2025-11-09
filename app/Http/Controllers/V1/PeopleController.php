<?php

namespace App\Http\Controllers\V1;

use App\Repositories\V1\PeopleRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use App\Utilities\ResponseHandler;
use Illuminate\Validation\Rule;


class PeopleController extends Controller
{
    protected PeopleRepository $personRepository;

    public function __construct(PeopleRepository $personRepository, Request $request)
    {
        parent::__construct($request);
        $this->personRepository = $personRepository;
    }

    /**
     * GET /api/persons
     */
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

        return $this->personRepository->personListing($request);
    }

    /**
     * POST /api/persons
     */
    public function store(Request $request)
    {
     $rules = [
    'name'          => 'required|string',
    'relative_id'   => 'sometimes|integer|exists:relatives,id',
    'interests'     => 'sometimes|array',
    'occasions'     => 'sometimes|array',
    'occasions.*.occasion_name_id' => 'required|integer|exists:occasion_names,id',
    'occasions.*.title' => 'nullable|string',
    'occasions.*.date' => 'nullable|date',
];


        $validated = $this->validated($rules, $request->all());
        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
        }

        return $this->personRepository->createPerson($validated->validated());
    }

    /**
     * GET /api/persons/{id}
     */
    public function show($id, Request $request)
    {
        $request->merge(['id' => $id]);
        $rules = [
            'id' => 'required|integer|exists:people,id',
        ];

        $validated = $this->validated($rules, $request->all());
        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
        }

        return $this->personRepository->showPerson($validated->validated());
    }

    /**
     * PUT /api/persons/{id}
     */
  public function update($id, Request $request)
{
    $request->merge(['id' => $id]);

    $rules = [
        'id'            => 'required|integer|exists:people,id',
        'name'          => 'sometimes|string|max:255',
        'birthday_date' => 'sometimes|date',
        'gender'        => 'sometimes|string|in:male,female,other',
        'region'        => 'sometimes|string|max:255',
        'city'          => 'sometimes|string|max:255',
        'address'       => 'sometimes|string|max:500',
        'relative_id'   => 'sometimes|integer|exists:relatives,id',

        // ✅ الاهتمامات (IDs فقط)
        'interests'         => 'sometimes|array',
        'interests.*'       => 'integer|exists:interests,id',

        // ✅ المناسبات (تحديث ذكي)
        'occasions'                         => 'sometimes|array',
        'occasions.*.occasion_name_id'      => 'required_with:occasions|integer|exists:occasion_names,id',
        'occasions.*.title'                 => 'sometimes|string|max:255',
        'occasions.*.date'                  => 'sometimes|date',
        'occasions.*.type'                  => 'sometimes|string|max:100',
    ];

    $validated = $this->validated($rules, $request->all());

    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 2007, $validated->errors());
    }

    return $this->personRepository->updatePerson($validated->validated());
}

    /**
     * DELETE /api/persons/{id}
     */
    public function destroy($id, Request $request)
    {
        $request->merge(['id' => $id]);
        $rules = [
         'id' => [
                'required',
                'integer',
                Rule::exists('people', 'id'),
            ],
        ];

        $validated = $this->validated($rules, $request->all());
        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 2009, $validated->errors());
        }

        return $this->personRepository->deletePerson($validated->validated());


    }


      public function indexWithRelativeOnly(Request $request)
{
    // لازم دا اول سطر
    $request->request->remove('id');
    $request->query->remove('id');

    $validated = $this->validated([
        'filters' => 'sometimes|array',
        'filters.name' => 'sometimes|string',
        'filters.gender' => 'sometimes|string',
        'filters.city' => 'sometimes|string',
        'order_by' => 'sometimes|in:id,name,created_at',
        'order' => 'sometimes|in:asc,desc'
    ], $request->all());

    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
    }

    return $this->personRepository->personListingWithRelativeOnly($request);
}



}
