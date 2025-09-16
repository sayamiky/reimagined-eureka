<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = Post::paginate(
            $perPage = $request->perPage
        )->withQueryString();

        return PostResource::collection($posts)->additional(['message' => 'Posts retrieved successfully']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $post = Post::create(array_merge(
            [
                'user_id' => auth()->user()->id
            ],
            $data
        ));
        return (new PostResource($post->loadMissing('user')))->additional([
            'message' => 'Post created successfully',
            'status' => true
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::find($id);
        if (! $post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return (new PostResource($post->loadMissing('user')))->additional([
            'message' => 'Posts retrieved successfully',
            'status' => true
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, $id)
    {
        $data = $request->validated();
        $post = Post::find($id);
        if (! $post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        if ($post->user_id !== auth()->user()->id) {
            return response()->json(['message' => 'You are not authorized to update this post'], 403);
        }

        $post->update($data);
        return (new PostResource($post->loadMissing('user')))->additional([
            'message' => 'Post updated successfully',
            'status' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();
        return response()->json([
            'message' => 'Post deleted successfully',
            'status' => true
        ]);
    }
}
