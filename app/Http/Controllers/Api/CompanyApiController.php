<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;

class CompanyApiController extends Controller
{
    // GET /api/companies/{company}
    public function show(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer',
        ]);

        $company = Company::find($data['id']);

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        return response()->json(['data' => $company], 200);
    }
}
