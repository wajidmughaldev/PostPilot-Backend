<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowPostController extends Controller
{
    public function __invoke(Request $request, PostService $service, string $context, string $post): JsonResponse
    {
        return $this->successResponse('Post retrieved successfully.', $service->show($request->user(), $context, $post));
    }
}
