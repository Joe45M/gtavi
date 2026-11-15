<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PostResource;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class PostsController
{
    public function index(Request $request, PostService $posts): AnonymousResourceCollection
    {
        $paginator = $posts->listPublished([
            'search' => $request->query('search', ''),
            'category' => $request->query('category', ''),
            'tag' => $request->query('tag', ''),
            'verification_badge' => $request->query('verification_badge', ''),
        ], min(25, max(1, (int) $request->query('per_page', 10))));

        return PostResource::collection($paginator);
    }

    public function show(string $slug, PostService $posts): PostResource
    {
        $post = $posts->findPublishedBySlug($slug);
        abort_if($post === null, 404);

        return new PostResource($post);
    }
}
