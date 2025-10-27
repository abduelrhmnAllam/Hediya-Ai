<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Relative;
use App\Models\Interest;
use App\Models\OccasionName;
use Illuminate\Http\Request;

class OnBoardingController extends Controller
{
    /**
     * 🟢 البحث + الترتيب الأبجدي في Relatives
     */
    public function relatives(Request $request)
    {
        try {
            $query = trim($request->input('query', ''));

            $relatives = Relative::query()
                ->when($query, function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orderByRaw("CASE WHEN title LIKE '{$query}%' THEN 1 ELSE 2 END");
                })
                ->select('id', 'title', 'image')
                ->orderBy('title', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($relative) {
                    $relative->image_url = $relative->image
    ? asset('storage/' . $relative->image)
    : asset('images/default-relative.png');

                    return $relative;
                });

            return response()->json([
                'status' => true,
                'message' => 'Relatives retrieved successfully.',
                'relatives' => $relatives,
            ], 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 🔵 البحث + الترتيب الأبجدي في Interests
     */
    public function interests(Request $request)
    {
        try {
            $query = trim($request->input('query', ''));

            $interests = Interest::query()
                ->when($query, function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orderByRaw("CASE WHEN title LIKE '{$query}%' THEN 1 ELSE 2 END");
                })
                ->select('id', 'title', 'icon')
                ->orderBy('title', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($interest) {
                    $interest->icon_url = $interest->icon
                        ? asset('storage/' . $interest->icon)
                        : asset('images/default-interest.png');
                    return $interest;
                });

            return response()->json([
                'status' => true,
                'message' => 'Interests retrieved successfully.',
                'interests' => $interests,
            ], 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 🟣 البحث + الترتيب الأبجدي في Occasion Names
     */
    public function occasions(Request $request)
    {
        try {
            $query = trim($request->input('query', ''));

            $occasions = OccasionName::query()
                ->when($query, function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orderByRaw("CASE WHEN name LIKE '{$query}%' THEN 1 ELSE 2 END");
                })
                ->select('id', 'name', 'type')
                ->orderBy('name', 'asc')
                ->limit(5)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Occasion names retrieved successfully.',
                'occasions' => $occasions,
            ], 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 🛑 دالة موحدة لأخطاء السيرفر
     */
    private function errorResponse($e)
    {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
