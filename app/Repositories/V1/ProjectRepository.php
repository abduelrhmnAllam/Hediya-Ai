<?php

namespace App\Repositories\V1;

use App\Utilities\ResponseHandler;
use Illuminate\Http\Request;
use App\Models\Projects;
use App\Models\Attributes;
use App\Models\AttributeValues;
use App\Utilities\FilterHelper;

class ProjectRepository extends BaseRepository
{
    protected string $logChannel;

    public function __construct(Request $request, Projects $projects)
    {
        parent::__construct($projects);
        $this->logChannel = 'projects_logs';
    }

    public function projectListing($request)
    {
        try {
            $query = $this->model::query();

            // Allowed regular columns on the projects table.
            $allowedColumns = ['name', 'status'];

            // Get filters from the request (if provided).
            $filters = $request->input('filters', []);

            // Apply filters if any.
            if (!empty($filters)) {
                $query = FilterHelper::applyFilters($query, $filters, $allowedColumns);
            }

            // Optional Ordering:
            // Accept "order_by" and "order" query parameters.
            $orderBy = $request->input('order_by', null);
            $order = $request->input('order', 'asc'); // default to ascending
            if ($orderBy && in_array($orderBy, $allowedColumns)) {
                $query->orderBy($orderBy, $order);
            }


            $rpp = $request->input('rpp', 10);
            $pageNo = $request->input('page', 1);


            // Get paginated results and eager load EAV attributes.
            $projects = $query->with('attributeValues.attribute')->paginate($rpp);

            // Transform EAV data into a key-value array for each project.
            $projects->getCollection()->transform(function ($project) {
                $dynamicAttributes = [];
                foreach ($project->attributeValues as $attrValue) {
                    $dynamicAttributes[$attrValue->attribute->name] = $attrValue->value;
                }
                $project->dynamic_attributes = $dynamicAttributes;
                unset($project->attributeValues);
                return $project;
            });

            return ResponseHandler::success($projects, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 24);
        }
    }

    public function createProject(array $validatedRequest)
    {
        try {
            // Create the project using the validated request data.
            $project = $this->model::create([
                'name'   => $validatedRequest['name'],
                'status' => $validatedRequest['status'] ?? null, // Use $validatedRequest, not $data.
            ]);

            // Handle dynamic attributes if provided.
            if (isset($validatedRequest['attributes']) && is_array($validatedRequest['attributes'])) {
                foreach ($validatedRequest['attributes'] as $attrName => $attrValue) {
                    // Find or create the attribute (default type 'text'; adjust as needed)
                    $attribute = \App\Models\Attributes::firstOrCreate(
                        ['name' => $attrName],
                        ['type' => 'text']
                    );

                    // Create an attribute value for the project.
                    \App\Models\AttributeValues::create([
                        'attribute_id' => $attribute->id,
                        'entity_id'    => $project->id,
                        'value'        => $attrValue,
                    ]);
                }
            }

            // Reload the project with dynamic attributes.
            $project->load('attributeValues.attribute');
            $dynamicAttributes = [];
            foreach ($project->attributeValues as $attrValue) {
                $dynamicAttributes[$attrValue->attribute->name] = $attrValue->value;
            }
            $project->dynamic_attributes = $dynamicAttributes;
            unset($project->attributeValues);

            return \App\Utilities\ResponseHandler::success($project, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return \App\Utilities\ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
        }
    }

    public function showProject(array $validatedRequest)
    {
        try {

            $project = $this->model::with('attributeValues.attribute')->find($validatedRequest['id']);
            if (!$project) {
                return ResponseHandler::error(__('common.not_found'), 404, 2005);
            }

            $dynamicAttributes = [];
            foreach ($project->attributeValues as $attrValue) {
                $dynamicAttributes[$attrValue->attribute->name] = $attrValue->value;
            }
            $project->dynamic_attributes = $dynamicAttributes;
            unset($project->attributeValues);
            return ResponseHandler::success($project, __('common.success'));

        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
        }
    }

    public function updateProject(array $validatedRequest)
    {
        try {
            $project = $this->model::find($validatedRequest['id']);
            if (!$project) {
                return ResponseHandler::error(__('common.not_found'), 404, 2009);
            }

            // Update project fields using validatedRequest
            $project->update([
                'name'   => $validatedRequest['name'] ?? $project->name,
                'status' => $validatedRequest['status'] ?? $project->status,
            ]);

            // Update or create dynamic attributes if provided
            if (isset($validatedRequest['attributes']) && is_array($validatedRequest['attributes'])) {
                foreach ($validatedRequest['attributes'] as $attrName => $attrValue) {
                    // Find or create the attribute (assuming model Attributes exists)
                    $attribute = \App\Models\Attributes::firstOrCreate(
                        ['name' => $attrName],
                        ['type' => 'text']
                    );

                    $attributeValue = \App\Models\AttributeValues::where('attribute_id', $attribute->id)
                        ->where('entity_id', $project->id)
                        ->first();

                    if ($attributeValue) {
                        $attributeValue->update(['value' => $attrValue]);
                    } else {
                        \App\Models\AttributeValues::create([
                            'attribute_id' => $attribute->id,
                            'entity_id'    => $project->id,
                            'value'        => $attrValue,
                        ]);
                    }
                }
            }

            // Reload project with dynamic attributes
            $project->load('attributeValues.attribute');
            $dynamicAttributes = [];
            foreach ($project->attributeValues as $attrValue) {
                $dynamicAttributes[$attrValue->attribute->name] = $attrValue->value;
            }
            $project->dynamic_attributes = $dynamicAttributes;
            unset($project->attributeValues);

            return ResponseHandler::success($project, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
        }
    }

    public function deleteProject(array $validatedRequest)
    {
        try {
            $project = $this->model::find($validatedRequest['id']);
            $project->delete();
            return ResponseHandler::success([], __('common.success'));

        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 26);
        }
    }
}
