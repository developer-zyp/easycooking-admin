<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        if (auth()->user() == null) {
            $posts = Post::published()->with(['images'])
                ->select('id', 'title', 'slug', 'view_count', 'created_at', 'updated_at')
                ->withCount('reviews', 'likes')
                ->selectRaw('CAST((SELECT AVG(post_reviews.rating) FROM post_reviews WHERE post_reviews.post_id = posts.id) AS DECIMAL(10, 2)) as average_rating')
                // ->selectRaw('(SELECT AVG(post_reviews.rating) FROM post_reviews WHERE post_reviews.post_id = posts.id) as average_rating')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
            // ->simplePaginate(20);
        } else {
            $posts = Post::published()->with(['images'])
                ->select('id', 'title', 'slug', 'view_count', 'created_at', 'updated_at')
                ->withCount('reviews', 'likes')
                ->selectRaw('CAST((SELECT AVG(post_reviews.rating) FROM post_reviews WHERE post_reviews.post_id = posts.id) AS DECIMAL(10, 2)) as average_rating')
                ->with('likes', function ($like) {
                    return $like->where('user_id', auth()->user()->id)
                        ->select('id', 'user_id', 'post_id')->get();
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
            // ->simplePaginate(20);
        }

        $response = [
            'success' => true,
            'message' => 'Posts',
            'page' => $posts->currentPage(),
            'total_pages' => $posts->lastPage(),
            'total_results' => $posts->total(),
            'results' => $posts->items(),
        ];

        return response()->json($response);

        // $response = new ApiResponse(true, 'Posts', $posts);
        // return response()->json($posts);
    }

    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if (auth()->user() == null) {
            $posts = Post::published()->with(['images'])
                ->select('id', 'title', 'slug', 'view_count', 'created_at', 'updated_at')
                ->withCount('reviews', 'likes')
                ->selectRaw('CAST((SELECT AVG(post_reviews.rating) FROM post_reviews WHERE post_reviews.post_id = posts.id) AS DECIMAL(10, 2)) as average_rating')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('content', 'LIKE', "%{$query}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
        } else {
            $posts = Post::published()->with(['images'])
                ->select('id', 'title', 'slug', 'view_count', 'created_at', 'updated_at')
                ->withCount('reviews', 'likes')
                ->selectRaw('CAST((SELECT AVG(post_reviews.rating) FROM post_reviews WHERE post_reviews.post_id = posts.id) AS DECIMAL(10, 2)) as average_rating')
                ->with('likes', function ($like) {
                    return $like->where('user_id', auth()->user()->id)
                        ->select('id', 'user_id', 'post_id');
                })
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('content', 'LIKE', "%{$query}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
        }

        $response = [
            'success' => true,
            'message' => 'Search results',
            'page' => $posts->currentPage(),
            'total_pages' => $posts->lastPage(),
            'total_results' => $posts->total(),
            'results' => $posts->items(),
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::with([
            'images',
            'recipes' => function ($query) {
                $query->select('id', 'slug', 'name', 'image', 'view_count', 'fav_count', 'category_id', 'post_id')
                    ->with('category');
            },
            'reviews' => function ($query) {
                $query->latest()->take(5)->with('user:id,name,image');
            }
        ])
            ->withCount('reviews', 'likes')
            ->selectRaw('CAST((SELECT AVG(post_reviews.rating) FROM post_reviews WHERE post_reviews.post_id = posts.id) AS DECIMAL(10, 2)) as average_rating')
            ->find($id);

        if (!$post) {
            $response = new ApiResponse(false, 'Item not found', $post);
            return response()->json($response);
        }

        $post->increment('view_count');

        $response = new ApiResponse(true, 'post details', $post);
        return response()->json($response);
    }

    public function showBySlug($slug)
    {
        info('showBySlug:' . $slug);
        $post = Post::with([
            'images',
            'recipes' => function ($query) {
                $query->select('id', 'slug', 'name', 'image', 'view_count', 'fav_count', 'category_id', 'post_id')
                    ->with('category');
            },
            'reviews' => function ($query) {
                $query->latest()->take(5)->with('user:id,name,image');
            }
        ])
            ->withCount(['reviews', 'likes'])
            ->selectRaw('CAST((SELECT AVG(post_reviews.rating) FROM post_reviews WHERE post_reviews.post_id = posts.id) AS DECIMAL(10, 2)) as average_rating')
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return response()->json(new ApiResponse(false, 'Post not found by slug', null));
        }

        $post->increment('view_count');

        return response()->json(new ApiResponse(true, 'Post details by slug', $post));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function likeOrUnlike($id)
    {
        $post = Post::find($id);

        if (!$post) {
            $response = new ApiResponse(false, 'Post not found.');
            return response()->json($response);
        }

        $like = $post->likes()->where('user_id', auth()->user()->id)->first();

        if (!$like) {
            PostLike::create([
                'user_id' => auth()->user()->id,
                'post_id' => $id,
            ]);

            $response = new ApiResponse(true, 'Liked:' . $post->id);
            return response()->json($response);
        }

        $like->delete();

        $response = new ApiResponse(true, 'Unliked');
        return response()->json($response);

    }

    public function saveOrUnsave($id)
    {
        $post = Post::find($id);

        if (!$post) {
            $response = new ApiResponse(false, 'Post not found.');
            return response()->json($response);
        }

        $like = $post->likes()
            ->where('user_id', auth()->user()->id)
            ->where('is_save', 1)
            ->first();

        if (!$like) {
            PostLike::create([
                'user_id' => auth()->user()->id,
                'post_id' => $id,
                'is_save' => 1,
            ]);

            $response = new ApiResponse(true, 'Saved:' . $post->id);
            return response()->json($response);
        }

        $like->is_save = 0; // Toggle the is_save attribute
        if ($like instanceof PostLike) {
            $like->save();
        } else {
            $response = new ApiResponse(false, 'Unable to update save status.');
            return response()->json($response);
        }

        $response = new ApiResponse(true, 'UnSaved:' . $post->id);
        return response()->json($response);

    }

}
