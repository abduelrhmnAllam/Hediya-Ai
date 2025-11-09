<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Occasion;
use App\Models\OccasionName;
use App\Models\People;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OccasionNameController extends Controller
{

    
    public function index()
    {
        $data = OccasionName::all();

        return response()->json([
            'status' => true,
            'message' => 'Occasion names retrieved successfully.',
            'data' => $data,
        ]);
    }

    /**
     * 🟣 إنشاء مناسبة جديدة لشخص (Occasion)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'person_id' => 'required|exists:people,id',
            'occasion_name_id' => 'required|exists:occasion_names,id',
            'title' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $person = People::find($request->person_id);

        if (!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Person not found.',
            ], 404);
        }

        $occasion = $person->occasions()->create([
            'occasion_name_id' => $request->occasion_name_id,
            'title' => $request->title ?? OccasionName::find($request->occasion_name_id)->name,
            'date' => $request->date,
            'type' => $request->type ?? OccasionName::find($request->occasion_name_id)->type,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Occasion created successfully.',
            'data' => $occasion->load('occasionName', 'person'),
        ]);
    }

    /**
     * 🟡 عرض مناسبة معينة لشخص
     */
    public function show($id)
    {
        $occasion = Occasion::with(['person', 'occasionName'])->find($id);

        if (!$occasion) {
            return response()->json([
                'status' => false,
                'message' => 'Occasion not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Occasion retrieved successfully.',
            'data' => $occasion,
        ]);
    }

    /**
     * 🔴 حذف مناسبة معينة
     */
    public function destroy($id)
    {
        $occasion = Occasion::find($id);

        if (!$occasion) {
            return response()->json([
                'status' => false,
                'message' => 'Occasion not found.',
            ], 404);
        }

        $occasion->delete();

        return response()->json([
            'status' => true,
            'message' => 'Occasion deleted successfully.',
        ]);
    }
}
