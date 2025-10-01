<?php

namespace App\Repositories\V1;

use App\Models\Timesheets;
use App\Models\User;
use App\Utilities\ResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class TimesheetRepository extends BaseRepository
{
    protected string $logChannel;

    public function __construct(Request $request, Timesheets $timesheets)
    {
        parent::__construct($timesheets);
        $this->logChannel = 'timesheet_logs';
    }

    public function listTimesheets(Request $request)
    {
        try {
            $query = $this->model::query();

            // Allowed columns for filtering.
            $allowedColumns = ['task_name', 'date', 'hours', 'user_id', 'project_id'];
            $allowedOperators = ['=', 'like', 'gt', 'lt'];

            // Get filters from the request (if provided).
            $filters = $request->input('filters', []);
            if (!empty($filters)) {
                foreach ($filters as $field => $criteria) {
                    // Only process filters for allowed columns.
                    if (in_array($field, $allowedColumns)) {
                        if (is_array($criteria)) {
                            // e.g. filters[task_name][like]=%API%
                            foreach ($criteria as $operator => $value) {
                                if (in_array(strtolower($operator), $allowedOperators)) {
                                    $op = (strtolower($operator) === 'like') ? 'LIKE' : $operator;
                                    $query->where($field, $op, $value);
                                }
                            }
                        } else {
                            // Direct equality: filters[field]=value
                            $query->where($field, '=', $criteria);
                        }
                    }
                }
            }

            // Ordering: accept "order_by" and "order" parameters.
            $orderBy = $request->input('order_by');
            $order = $request->input('order', 'asc'); // default ascending
            if ($orderBy && in_array($orderBy, $allowedColumns)) {
                $query->orderBy($orderBy, $order);
            }

            // Pagination: use 'rpp' (records per page, default 10) and 'page_no' (default 1)
            $rpp = $request->input('rpp', 10);

            // Execute the query with pagination.
            $timesheets = $query->paginate($rpp);

            return ResponseHandler::success($timesheets, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500,14);
        }
    }
    public function createTimesheet(array $validatedRequest)
    {
        try {
            $user = $this->model::create($validatedRequest);

            return ResponseHandler::success($user, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500,14);
        }
    }
    public function showTimesheet(array $validatedRequest)
    {
        try {
            $timesheet = $this->model::find($validatedRequest['id']);
            if (!$timesheet) {
                return ResponseHandler::error(__('common.not_found'), 404, 1004);
            }
            return ResponseHandler::success($timesheet, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500,14);
        }
    }

    public function deleteTimesheet(array $validatedRequest)
    {
        try {
            $timesheet = $this->model::find($validatedRequest['id']);
            if (!$timesheet) {
                return ResponseHandler::error(__('common.not_found'), 404, 5015);
            }
            $timesheet->delete();
            return ResponseHandler::success([], __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
        }
    }
}
