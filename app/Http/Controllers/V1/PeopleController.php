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
    $rules = [
        'filters' => 'sometimes|array',
        'filters.name' => 'nullable|string|max:255',
        'filters.city' => 'nullable|string|max:255',
        'filters.gender' => 'nullable|string|in:male,female,other',
        'filters.relative_id' => 'nullable|integer|exists:relatives,id',
        'filters.interest_id' => 'nullable|integer|exists:interests,id',
        'filters.occasion_name_id' => 'nullable|integer|exists:occasion_names,id',

        'order_by' => 'nullable|string|in:id,name,city,gender,created_at',
        'order' => 'nullable|in:asc,desc',
        'rpp' => 'nullable|integer|min:1',
        'paginate' => 'nullable|boolean'
    ];

    $validated = $this->validated($rules, $request->all());

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
    // ✅ تحويل attachments من object إلى array لو المستخدم بعت عنصر واحد فقط
    if ($request->has('attachments') && !is_array($request->input('attachments'))) {
        $attachments = $request->input('attachments');
        if (is_array($attachments) && array_key_exists('file', $attachments)) {
            $request->merge(['attachments' => [$attachments]]);
        }
    }

    // ✅ تحويل occasions من object إلى array لو المستخدم بعت مناسبة واحدة فقط
    if ($request->has('occasions') && !is_array($request->input('occasions'))) {
        $occasions = $request->input('occasions');
        if (is_array($occasions) && array_key_exists('occasion_name_id', $occasions)) {
            $request->merge(['occasions' => [$occasions]]);
        }
    }

    // ✅ نفس الفكرة ممكن نعملها لو الاهتمامات interest واحدة فقط
    if ($request->has('interests') && !is_array($request->input('interests'))) {
        $interests = $request->input('interests');
        if (is_numeric($interests)) {
            $request->merge(['interests' => [$interests]]);
        }
    }

    // ✅ قواعد الفاليديشن
    $rules = [
        'name'          => 'required|string|max:255',
        'relative_id'   => 'sometimes|integer|exists:relatives,id',
        'avatar_id'     => 'sometimes|integer|exists:avatars,id',
        'birthday_date' => 'nullable|date',
        'gender'        => 'nullable|string|in:male,female,other',
        'region'        => 'nullable|string|max:255',
        'city'          => 'nullable|string|max:255',
        'address'       => 'nullable|string|max:255',

        // ✅ الاهتمامات
        'interests'     => 'sometimes|array',
        'interests.*'   => 'integer|exists:interests,id',

        // ✅ المناسبات
        'occasions'                         => 'sometimes|array',
        'occasions.*.occasion_name_id'      => 'required_with:occasions|integer|exists:occasion_names,id',
        'occasions.*.title'                 => 'nullable|string|max:255',
        'occasions.*.date'                  => 'nullable|date',

        // ✅ المرفقات (الهدايا)
        'attachments'                    => 'sometimes|array',
        'attachments.*.file'             => 'nullable',
        'attachments.*.product_name'     => 'nullable|string|max:255',
        'attachments.*.product_brand'    => 'nullable|string|max:255',
        'attachments.*.price'            => 'nullable|numeric|min:0',
        'attachments.*.store_name'       => 'nullable|string|max:255',
        'attachments.*.note'             => 'nullable|string|max:1000',
    ];

    // ✅ تنفيذ الفاليديشن
    $validated = $this->validated($rules, $request->all());

    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
    }

    // ✅ تمرير البيانات النظيفة إلى الريبوستري
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

    // ✅ نمرر البيانات بعد التحقق
    return $this->personRepository->showPerson($validated->validated());
}


    /**
     * PUT /api/persons/{id}
     */
     public function update($id, Request $request)
{
    // ✅ لو attachments مش array (يعني object واحد فقط) نحولها لمصفوفة
    if ($request->has('attachments') && !is_array($request->input('attachments'))) {
        $attachments = $request->input('attachments');
        // لو هي object واحد فقط (فيها keys زي file و product_name)
        if (is_array($attachments) && array_key_exists('file', $attachments)) {
            $request->merge(['attachments' => [$attachments]]);
        }
    }

    // ✅ نفس الفكرة ممكن لمناسبات (اختياري)
    if ($request->has('occasions') && !is_array($request->input('occasions'))) {
        $occasions = $request->input('occasions');
        if (is_array($occasions) && array_key_exists('occasion_name_id', $occasions)) {
            $request->merge(['occasions' => [$occasions]]);
        }
    }

    // ✅ قواعد الفاليديشن
    $rules = [
        'name'          => 'sometimes|string|max:255',
        'birthday_date' => 'sometimes|date',
        'gender'        => 'sometimes|string|in:male,female,other',
        'region'        => 'sometimes|string|max:255',
        'city'          => 'sometimes|string|max:255',
        'address'       => 'sometimes|string|max:500',
        'relative_id'   => 'sometimes|integer|exists:relatives,id',
        'avatar_id'     => 'sometimes|integer|exists:avatars,id',

        'interests'     => 'sometimes|array',
        'interests.*'   => 'integer|exists:interests,id',

        'occasions'                         => 'sometimes|array',
        'occasions.*.occasion_name_id'      => 'required_with:occasions|integer|exists:occasion_names,id',
        'occasions.*.title'                 => 'sometimes|string|max:255',
        'occasions.*.date'                  => 'sometimes|date',
        'occasions.*.type'                  => 'sometimes|string|max:100',

        // ✅ المرفقات (الهدايا)
        'attachments'                    => 'sometimes|array',
        'attachments.*.id'               => 'sometimes|integer|exists:attachments,id',
        'attachments.*.file'             => 'nullable',
        'attachments.*.product_name'     => 'nullable|string|max:255',
        'attachments.*.product_brand'    => 'nullable|string|max:255',
        'attachments.*.price'            => 'nullable|numeric|min:0',
        'attachments.*.store_name'       => 'nullable|string|max:255',
        'attachments.*.note'             => 'nullable|string|max:1000',
    ];

    // ✅ تنفيذ الفاليديشن
    $validated = $this->validated($rules, $request->all());

    if ($validated->fails()) {
        return ResponseHandler::error(__('common.errors.validation'), 422, 2007, $validated->errors());
    }

    // ✅ نضيف الـ ID يدويًا
    $validatedData = $validated->validated();
    $validatedData['id'] = $id;

    // ✅ تمرير البيانات للـ Repository
    return $this->personRepository->updatePerson($validatedData);
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


        public function indexWithRelativeOnly()
        {
            return $this->personRepository->personListingWithRelativeOnly();
        }



}
