<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;

class StorePostController extends Controller
{
    public function __invoke(StorePostRequest $request, PostService $service, string $context): JsonResponse
    {
        return $this->successResponse('Post created successfully.', $service->create($request->user(), $context, $request->validated()), 201);
    }
}
