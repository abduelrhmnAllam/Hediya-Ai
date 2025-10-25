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
            $query = $this->model::with(['relative', 'interests']);

            $allowedColumns = ['name','gender','city'];

            $filters = $request->input('filters', []);
            if (!empty($filters)) {
                $query = FilterHelper::applyFilters($query, $filters, $allowedColumns);
            }

            $orderBy = $request->input('order_by', null);
            $order   = $request->input('order', 'asc');
            if ($orderBy && in_array($orderBy, $allowedColumns)) {
                $query->orderBy($orderBy, $order);
            }

            $rpp = $request->input('rpp', 10);
            $persons = $query->paginate($rpp);

            return ResponseHandler::success($persons, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 24);
        }
    }

public function createPerson(array $validatedRequest)
{
    try {
        // ✅ إنشاء الشخص
        $person = $this->model::create([
            'name'          => $validatedRequest['name'],
            'birthday_date' => $validatedRequest['birthday_date'] ?? null,
            'gender'        => $validatedRequest['gender'] ?? null,
            'region'        => $validatedRequest['region'] ?? null,
            'city'          => $validatedRequest['city'] ?? null,
            'address'       => $validatedRequest['address'] ?? null,
            'relative_id'   => $validatedRequest['relative_id'] ?? null,
            'user_id'       => auth('api')->id(),
        ]);

        // ✅ إنشاء المناسبات المتعددة
        if (!empty($validatedRequest['occasions']) && is_array($validatedRequest['occasions'])) {
            foreach ($validatedRequest['occasions'] as $occ) {
                $person->occasions()->create([
                    'occasion_name_id' => $occ['occasion_name_id'],
                    'title' => $occ['title'] ?? 'Occasion for ' . $person->name,
                    'date'  => $occ['date'] ?? null,
                    'type'  => optional(\App\Models\OccasionName::find($occ['occasion_name_id']))->type,
                ]);
            }
        }

        // ✅ ربط الاهتمامات
        if (!empty($validatedRequest['interests']) && is_array($validatedRequest['interests'])) {
            $person->interests()->sync($validatedRequest['interests']);
        }

        // ✅ إعادة الرد JSON
        return ResponseHandler::success(
            $person->load(['relative', 'interests', 'occasions.occasionName']),
            __('common.success')
        );

    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error(
            $this->prepareExceptionLog($e),
            500,
            26
        );
    }
}



    public function showPerson(array $validatedRequest)
    {
        try {
            $person = $this->model::with(['relative','interests'])->find($validatedRequest['id']);
            if (!$person) {
                return ResponseHandler::error(__('common.not_found'), 404, 2005);
            }
            return ResponseHandler::success($person, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
        }
    }

    public function updatePerson(array $validatedRequest)
    {
        try {
            $person = $this->model::find($validatedRequest['id']);
            if (!$person) {
                return ResponseHandler::error(__('common.not_found'), 404, 2009);
            }

            $person->update([
                'name'          => $validatedRequest['name'] ?? $person->name,
                'birthday_date' => $validatedRequest['birthday_date'] ?? $person->birthday_date,
                'gender'        => $validatedRequest['gender'] ?? $person->gender,
                'region'        => $validatedRequest['region'] ?? $person->region,
                'city'          => $validatedRequest['city'] ?? $person->city,
                'address'       => $validatedRequest['address'] ?? $person->address,
                'relative_id'   => $validatedRequest['relative_id'] ?? $person->relative_id,
            ]);

            if (isset($validatedRequest['interests'])) {
                $person->interests()->sync($validatedRequest['interests']);
            }

            return ResponseHandler::success(
                $person->load(['relative','interests']),
                __('common.success')
            );
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
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

}
