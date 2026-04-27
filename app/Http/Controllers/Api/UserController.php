<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function profilePhoto(Request $request): JsonResponse {

    $user = $request->user();
    $photoUrl = null;
    $photobase64 = null;

    if ($user && $user->profile_photo) {

        $photoPath = ltrim($user->profile_photo, '/');
        $photoUrl = 'storage/' . $photoPath;
        // dd($photoUrl);

        if (Storage::disk('public')->exists($photoPath)) {
            // $mimeType = Storage::disk('public')->mimeType($photoPath) ?? 'image/jpeg';
            $contents = Storage::disk('public')->get($photoPath);
            $mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $contents) ?: 'image/jpeg';
            $photobase64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);

            }
    }

    return response()->json([
        'success' => true,
        'data' => [
            'profile_photo_url' => $photoUrl,
            'profile_photo_base64' => $photobase64,
        ],
        'message' => 'Profile photo retrieved successfully.',
    ]);
}


}
