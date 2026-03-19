<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeletePostController extends Controller
{
    public function __invoke(Request $request, PostService $service, string $context, string $post): JsonResponse
    {
        $service->delete($request->user(), $context, $post);

        return $this->successResponse('Post deleted successfully.', null);
    }
}
