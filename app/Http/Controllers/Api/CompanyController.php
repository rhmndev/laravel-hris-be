<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $company = Company::getCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company information not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $company,
            'message' => 'Company information retrieved successfully',
        ]);
    }
}
