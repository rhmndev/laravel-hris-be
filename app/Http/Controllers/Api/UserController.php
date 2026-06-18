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

    /**
     * Update employee's own profile data (ESS - Employee Self Service)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone_personal' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'identity_number' => 'nullable|string|max:20|unique:users,identity_number,' . $user->id,
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data' => $user->fresh(['branch', 'employeeDesignation.shift', 'employeeDesignation.designation']),
            'message' => 'Profil berhasil diperbarui.',
        ]);
    }


}
