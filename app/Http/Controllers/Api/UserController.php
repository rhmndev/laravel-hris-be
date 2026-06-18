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
     * Upload/update profile photo.
     * Validates image format and size, saves to public storage,
     * and updates the user's profile_photo column.
     */
    public function uploadProfilePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|string',
        ]);

        $base64 = $request->input('photo');

        // Strip data URI prefix if present (e.g., "data:image/jpeg;base64,...")
        if (preg_match('/^data:image\/\w+;base64,/', $base64, $matches)) {
            $base64 = substr($base64, strlen($matches[0]));
        }

        $imageData = base64_decode($base64, true);

        if ($imageData === false) {
            return response()->json([
                'success' => false,
                'message' => 'Format gambar tidak valid.',
            ], 422);
        }

        // Max 5MB decoded size
        if (strlen($imageData) > 5 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran foto maksimal 5MB.',
            ], 422);
        }

        // Validate it's actually an image
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya format JPEG, PNG, dan WebP yang diperbolehkan.',
            ], 422);
        }

        $user = $request->user();

        // Delete old photo if exists
        if ($user->profile_photo && Storage::disk('public')->exists(ltrim($user->profile_photo, '/'))) {
            Storage::disk('public')->delete(ltrim($user->profile_photo, '/'));
        }

        // Save new photo
        $extension = match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $path = 'profile-photos/' . $user->id . '_' . time() . '.' . $extension;
        Storage::disk('public')->put($path, $imageData);

        $user->update(['profile_photo' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'data' => $user->fresh(['branch', 'employeeDesignation.shift', 'employeeDesignation.designation']),
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
