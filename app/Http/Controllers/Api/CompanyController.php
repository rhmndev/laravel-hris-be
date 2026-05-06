<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->user()?->branch_id;

        $company = Company::query()->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company information not found',
            ], 404);
        }

        $branch = $company->branches()->when($branchId, fn ($query) => $query->where('id',$branchId))->first();

        $company->setRelation('branch', $branch);
        $company->unsetRelation('branches');


        return response()->json([
            'success' => true,
            'data' => $company,
            'message' => 'Company information retrieved successfully',
        ]);
    }
}
