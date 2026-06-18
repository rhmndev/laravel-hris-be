<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;

class DesignationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['auth:sanctum'];
    }

    /**
     * Get all designations
     */
    public function index(Request $request)
    {
        $query = Designation::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $designations = $query->with('createdBy')
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'message' => 'Daftar jabatan',
            'data' => $designations
        ]);
    }

    /**
     * Get specific designation
     */
    public function show(Designation $designation)
    {
        return response()->json([
            'message' => 'Detail jabatan',
            'data' => $designation->load('createdBy', 'employeeDesignations')
        ]);
    }

    /**
     * Create designation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|unique:designations',
            'description' => 'nullable|string',
            'level' => 'nullable|integer|between:1,10'
        ]);

        $validated['created_by'] = $request->user()->id;

        $designation = Designation::create($validated);

        return response()->json([
            'message' => 'Jabatan berhasil dibuat',
            'data' => $designation
        ], 201);
    }

    /**
     * Update designation
     */
    public function update(Request $request, Designation $designation)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|unique:designations,name,' . $designation->id,
            'description' => 'nullable|string',
            'level' => 'nullable|integer|between:1,10'
        ]);

        $designation->update($validated);

        return response()->json([
            'message' => 'Jabatan berhasil diupdate',
            'data' => $designation
        ]);
    }

    /**
     * Delete designation
     */
    public function destroy(Designation $designation)
    {
        // Check if designation is being used
        if ($designation->employeeDesignations()->count() > 0) {
            return response()->json([
                'message' => 'Jabatan tidak dapat dihapus karena sedang digunakan oleh karyawan'
            ], 403);
        }

        $designation->delete();

        return response()->json([
            'message' => 'Jabatan berhasil dihapus'
        ]);
    }
}
