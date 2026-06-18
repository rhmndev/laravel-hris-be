<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Earning;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;

class EarningController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['auth:sanctum'];
    }

    public function index(Request $request)
    {
        $query = Earning::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $earnings = $query->with('createdBy')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'message' => 'Daftar komponen penghasilan',
            'data' => $earnings
        ]);
    }

    public function show(Earning $earning)
    {
        return response()->json([
            'message' => 'Detail penghasilan',
            'data' => $earning->load('createdBy')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|unique:earnings',
            'type' => 'required|in:basic,allowance,bonus,incentive,overtime',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;

        $earning = Earning::create($validated);

        return response()->json([
            'message' => 'Komponen penghasilan berhasil dibuat',
            'data' => $earning
        ], 201);
    }

    public function update(Request $request, Earning $earning)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|unique:earnings,name,' . $earning->id,
            'type' => 'nullable|in:basic,allowance,bonus,incentive,overtime',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);

        $earning->update($validated);

        return response()->json([
            'message' => 'Penghasilan berhasil diupdate',
            'data' => $earning
        ]);
    }

    public function destroy(Earning $earning)
    {
        $earning->delete();

        return response()->json([
            'message' => 'Penghasilan berhasil dihapus'
        ]);
    }
}
