<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deduction;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;

class DeductionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['auth:sanctum'];
    }

    public function index(Request $request)
    {
        $query = Deduction::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $deductions = $query->with('createdBy')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'message' => 'Daftar komponen potongan',
            'data' => $deductions
        ]);
    }

    public function show(Deduction $deduction)
    {
        return response()->json([
            'message' => 'Detail potongan',
            'data' => $deduction->load('createdBy')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|unique:deductions',
            'type' => 'required|in:tax,social_security,loan,advance,other',
            'description' => 'nullable|string',
            'is_fixed' => 'required|boolean',
            'amount' => 'required_if:is_fixed,true|nullable|numeric|min:0',
            'percentage' => 'required_if:is_fixed,false|nullable|numeric|min:0|max:100'
        ]);

        $validated['created_by'] = $request->user()->id;

        $deduction = Deduction::create($validated);

        return response()->json([
            'message' => 'Komponen potongan berhasil dibuat',
            'data' => $deduction
        ], 201);
    }

    public function update(Request $request, Deduction $deduction)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|unique:deductions,name,' . $deduction->id,
            'type' => 'nullable|in:tax,social_security,loan,advance,other',
            'description' => 'nullable|string',
            'is_fixed' => 'nullable|boolean',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean'
        ]);

        $deduction->update($validated);

        return response()->json([
            'message' => 'Potongan berhasil diupdate',
            'data' => $deduction
        ]);
    }

    public function destroy(Deduction $deduction)
    {
        $deduction->delete();

        return response()->json([
            'message' => 'Potongan berhasil dihapus'
        ]);
    }
}
