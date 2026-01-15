<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ApiResourceHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResourceHandler;

    /**
     * Get the authenticated user.
     */
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse($request->user());
    }
}
