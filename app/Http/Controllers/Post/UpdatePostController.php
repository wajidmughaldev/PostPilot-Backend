<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;

class UpdatePostController extends Controller
{
    public function __invoke(UpdatePostRequest $request, PostService $service, string $context, string $post): JsonResponse
    {
        return $this->successResponse('Post updated successfully.', $service->update($request->user(), $context, $post, $request->validated()));
    }
}
