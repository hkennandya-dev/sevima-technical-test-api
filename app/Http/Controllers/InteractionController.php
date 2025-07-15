<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
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
        Like::where('user_id', auth()->id())
            ->where('post_id', $postId)
            ->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Post unliked successfully.'
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
}
