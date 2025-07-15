<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    public function getLikes(Request $request, $postId)
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('paginate', 10);

        $likes = Like::with('user')
            ->where('post_id', $postId)
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'status' => 200,
            'message' => 'Likes fetched successfully.',
            'paginate' => [
                'is_next' => $likes->hasMorePages(),
                'is_prev' => $likes->currentPage() > 1,
                'page'    => $likes->currentPage(),
                'limit'   => $likes->perPage(),
                'total'   => $likes->total()
            ],
            'data' => $likes->items()
        ]);
    }

    public function like($postId)
    {
        $like = Like::firstOrCreate([
            'user_id' => auth()->id(),
            'post_id' => $postId
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Post liked successfully.',
            'data' => $like
        ]);
    }

    public function unlike($postId)
    {
        $like = Like::where('user_id', auth()->id())
            ->where('post_id', $postId)
            ->first();

        if (!$like) {
            return response()->json([
                'status' => 403,
                'message' => 'You can only unlike your own like.'
            ], 403);
        }

        $like->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Post unliked successfully.'
        ]);
    }

    public function getComments(Request $request, $postId)
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('paginate', 10);

        $comments = Comment::with('user')
            ->where('post_id', $postId)
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'status' => 200,
            'message' => 'Comments fetched successfully.',
            'paginate' => [
                'is_next' => $comments->hasMorePages(),
                'is_prev' => $comments->currentPage() > 1,
                'page'    => $comments->currentPage(),
                'limit'   => $comments->perPage(),
                'total'   => $comments->total()
            ],
            'data' => $comments->items()
        ]);
    }

    public function comment(Request $request, $postId)
    {
        $this->validate($request, [
            'content' => 'required|string'
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $postId,
            'content' => $request->input('content')
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Comment added successfully.',
            'data' => $comment
        ]);
    }

    public function updateComment(Request $request, $postId, $id)
    {
        $this->validate($request, [
            'content' => 'required|string'
        ]);

        $comment = Comment::where('post_id', $postId)
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$comment) {
            return response()->json([
                'status' => 403,
                'message' => 'You can only update your own comment.'
            ], 403);
        }

        $comment->content = $request->input('content');
        $comment->save();

        return response()->json([
            'status' => 200,
            'message' => 'Comment updated successfully.',
            'data' => $comment
        ]);
    }

    public function deleteComment($postId, $id)
    {
        $comment = Comment::where('post_id', $postId)
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$comment) {
            return response()->json([
                'status' => 403,
                'message' => 'You can only delete your own comment.'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Comment deleted successfully.'
        ]);
    }
}