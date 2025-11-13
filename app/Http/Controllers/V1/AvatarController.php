<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    // ✅ جلب كل الصور (مع فلترة حسب الجنس لو تم إرسالها)
    public function index(Request $request)
    {
        $query = Avatar::query();

        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        $avatars = $query->get();

        return response()->json([
            'status'  => 200,
            'code'    => 8200,
            'message' => __('common.success'),
            'avatars'    => $avatars,
        ]);
    }
}
