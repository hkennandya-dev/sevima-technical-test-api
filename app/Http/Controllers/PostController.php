<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('paginate', 10);
        $userId = auth()->id();

        $posts = Post::with('user')
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as is_liked' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = collect($posts->items())->map(function ($post) {
            $post->image_path = $post->image_path ? url('storage/' . $post->image_path) : null;
            return $post;
        });

        return response()->json([
            'status' => 200,
            'message' => 'Posts fetched successfully.',
            'paginate' => [
                'is_next' => $posts->hasMorePages(),
                'is_prev' => $posts->currentPage() > 1,
                'page' => $posts->currentPage(),
                'limit' => $posts->perPage(),
                'total' => $posts->total()
            ],
            'data' => $data
        ]);
    }

    public function me(Request $request)
    {
        $userId = auth()->id();

        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('paginate', 10);

        $posts = Post::with('user')
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as is_liked' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $posts->getCollection()->transform(function ($post) {
            $post->image_path = $post->image_path ? url('storage/' . $post->image_path) : null;
            return $post;
        });

        return response()->json([
            'status' => 200,
            'message' => 'My posts fetched successfully.',
            'paginate' => [
                'is_next' => $posts->hasMorePages(),
                'is_prev' => $posts->currentPage() > 1,
                'page' => $posts->currentPage(),
                'limit' => $posts->perPage(),
                'total' => $posts->total()
            ],
            'data' => $posts->items()
        ]);
    }


    public function show($id)
    {
        $userId = auth()->id();

        $post = Post::with('user')
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as is_liked' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->findOrFail($id);

        $post->image_path = $post->image_path ? url('storage/' . $post->image_path) : null;

        return response()->json([
            'status' => 200,
            'message' => 'Post fetched successfully.',
            'data' => $post
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Some fields are invalid.',
                'error' => $validator->errors()
            ], 400);
        }

        if (!$request->hasFile('image') && !$request->filled('caption')) {
            return response()->json([
                'status' => 400,
                'message' => 'Some fields are invalid.',
                'error' => [
                    'caption' => ['The caption field is required.']
                ]
            ], 400);
        }

        $path = null;
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $imageUrl = url('storage/' . $path);
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'caption' => $request->input('caption'),
            'image_path' => $path
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Post created successfully.',
            'data' => [
                'id' => $post->id,
                'user_id' => $post->user_id,
                'caption' => $post->caption,
                'image_path' => $imageUrl,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden action.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Some fields are invalid.',
                'error' => $validator->errors()
            ], 400);
        }

        $post->caption = $request->input('caption');
        $post->save();

        $post->image_path = $post->image_path ? url('storage/' . $post->image_path) : null;

        return response()->json([
            'status' => 200,
            'message' => 'Post updated successfully.',
            'data' => $post
        ]);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden action.',
            ], 403);
        }

        $post->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Post deleted successfully.',
        ]);
    }
}
