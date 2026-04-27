<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function profilePhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        $photoUrl = null;
        $photobase64 = null;

        if ($user && $user->profile_photo) {
            $photoPath = ltrim($user->profile_photo, '/');
            $photoUrl = 'storage/' . $photoPath;

            if (Storage::disk('public')->exists($photoPath)) {
                $contents = Storage::disk('public')->get($photoPath);
                $mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $contents) ?: 'application/octet-stream';
                // $mimeType = Storage::disk('public')->mimeType($photoPath) ?? 'image/jpeg';
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
