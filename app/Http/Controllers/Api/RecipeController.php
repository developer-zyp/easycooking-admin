<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Http\Response\ApiResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipes = Recipe::with('category')
            ->with(['images', 'reviews'])
            ->limit(20)
            ->orderBy('created_at', 'desc')
            ->get();

        $response = new ApiResponse(true, 'recipes', $recipes);
        return response()->json($response);
    }

    public function getRecipeByCategoryId($category_id)
    {
        $recipes = Recipe::with('category')
            ->select('id', 'slug', 'name', 'image', 'view_count', 'fav_count', 'category_id', 'post_id')
            ->where('category_id', $category_id)
            ->where('inactive', 0)
            ->get();

        // if ($recipes->isEmpty()) {
        //     return response()->json(['message' => 'No posts found for the specified category ID'], 404);
        // }

        $response = new ApiResponse(true, 'recipes by category', $recipes);
        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:recipes',
            'description' => 'nullable|string',
            'instructions' => 'required|string',
            'prep_time' => 'nullable|integer',
            'cook_time' => 'nullable|integer',
            'category_id' => 'required|exists:categories,id',
        ]);

        $recipe = Recipe::create($validated);

        $response = new ApiResponse(true, 'recipe added successfully', $recipe);
        return response()->json($response);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $recipe = Recipe::with(['category', 'images', 'reviews'])->find($id);

        if (!$recipe) {
            $response = new ApiResponse(false, 'Item not found', $recipe);
            return response()->json($response);
        }

        $recipe->increment('view_count');

        $response = new ApiResponse(true, 'recipe details', $recipe);
        return response()->json($response);
    }

    public function showBySlug($slug)
    {
        $recipe = Recipe::with(['category', 'images', 'reviews'])
            ->where('slug', $slug)
            ->first();

        if (!$recipe) {
            return response()->json(new ApiResponse(false, 'Recipe not found by slug'));
        }

        return response()->json(new ApiResponse(true, 'Recipe details by slug', $recipe));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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

    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $recipes = Recipe::with('category')
            ->select('id', 'slug', 'name', 'image', 'view_count', 'fav_count', 'category_id', 'post_id')
            ->where('inactive', 0)
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $response = [
            'success' => true,
            'message' => 'Search results',
            'query' => $query,
            'page' => $recipes->currentPage(),
            'total_pages' => $recipes->lastPage(),
            'total_results' => $recipes->total(),
            'results' => $recipes->items(),
        ];

        return response()->json($response);
    }

    public function newRecipe(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $recipes = Recipe::with('category')
            ->select('id', 'slug', 'name', 'image', 'view_count', 'fav_count', 'category_id', 'post_id')
            ->where('inactive', 0)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        // ->take(20)
        // ->get();

        $response = [
            'success' => true,
            'message' => 'New recipes',
            'page' => $recipes->currentPage(),
            'total_pages' => $recipes->lastPage(),
            'total_results' => $recipes->total(),
            'results' => $recipes->items(),
        ];

        return response()->json($response);
    }

    public function popular(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $recipes = Recipe::with('category')
            ->select('id', 'slug', 'name', 'image', 'view_count', 'fav_count', 'category_id', 'post_id')
            ->where('inactive', 0)
            ->orderBy('view_count', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        // ->take(20)
        // ->get();

        $response = [
            'success' => true,
            'message' => 'Popular recipes',
            'page' => $recipes->currentPage(),
            'total_pages' => $recipes->lastPage(),
            'total_results' => $recipes->total(),
            'results' => $recipes->items(),
        ];

        return response()->json($response);
    }

    public function cookingKnowledge(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $recipes = Recipe::with('category')
            ->select('id', 'slug', 'name', 'image', 'view_count', 'fav_count', 'category_id', 'post_id')
            ->where('inactive', 0)
            ->whereHas('category', function ($query) {
                $query->where('type', 2);
            })
            // ->where('category_id', '=', 1000)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        // ->take(10)
        // ->get();

        $response = [
            'success' => true,
            'message' => 'Cooking knowledge',
            'page' => $recipes->currentPage(),
            'total_pages' => $recipes->lastPage(),
            'total_results' => $recipes->total(),
            'results' => $recipes->items(),
        ];

        return response()->json($response);
    }

}
