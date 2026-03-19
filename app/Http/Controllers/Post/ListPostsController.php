<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListPostsController extends Controller
{
    public function __invoke(Request $request, PostService $service, string $context): JsonResponse
    {
        return $this->successResponse('Posts retrieved successfully.', $service->list($request->user(), $context));
    }
}
