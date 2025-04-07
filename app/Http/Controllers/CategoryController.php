<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

/**
 * @group Category Management
 *
 * APIs for managing task categories
 */
class CategoryController extends Controller
{
    use ApiResponser;

    /**
     * List all categories
     *
     * Returns all categories belonging to the authenticated user.
     * 
     * @authenticated
     * 
     * @response {
     *   "status": "Success",
     *   "message": null,
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "name": "Work",
     *       "color": "#ff0000",
     *       "created_at": "2023-01-01T00:00:00.000000Z",
     *       "updated_at": "2023-01-01T00:00:00.000000Z"
     *     },
     *     {
     *       "id": 2,
     *       "user_id": 1,
     *       "name": "Personal",
     *       "color": "#00ff00",
     *       "created_at": "2023-01-01T00:00:00.000000Z",
     *       "updated_at": "2023-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     * 
     * @response 401 {
     *   "status": "Error",
     *   "message": "Unauthenticated.",
     *   "data": null
     * }
     */
    public function index(Request $request)
    {
        $categories = $request->user()->categories;
        return $this->successResponse($categories);
    }

    /**
     * Create a new category
     *
     * Creates a new category for the authenticated user.
     * 
     * @authenticated
     * 
     * @bodyParam name string required The name of the category. Example: Work
     * @bodyParam color string The color code for the category (hexadecimal). Example: #ff0000
     * 
     * @response 201 {
     *   "status": "Success",
     *   "message": "Category created successfully",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "name": "Work",
     *     "color": "#ff0000",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     * 
     * @response 422 {
     *   "status": "Error",
     *   "message": "The name field is required.",
     *   "data": null
     * }
     * 
     * @response 401 {
     *   "status": "Error",
     *   "message": "Unauthenticated.",
     *   "data": null
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $category = $request->user()->categories()->create($validated);

        return $this->successResponse($category, 'Category created successfully', 201);
    }

    /**
     * Get a specific category
     *
     * Returns details for a specific category if it belongs to the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam category required The ID of the category. Example: 1
     * 
     * @response {
     *   "status": "Success",
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "name": "Work",
     *     "color": "#ff0000",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     * 
     * @response 403 {
     *   "status": "Error",
     *   "message": "Unauthorized",
     *   "data": null
     * }
     * 
     * @response 404 {
     *   "status": "Error",
     *   "message": "No query results for model [App\\Models\\Category] 1",
     *   "data": null
     * }
     */
    public function show(Request $request, Category $category)
    {
        // Check if the authenticated user owns the category
        if ($request->user()->id !== $category->user_id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($category);
    }

    /**
     * Update a category
     *
     * Updates the specified category if it belongs to the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam category required The ID of the category. Example: 1
     * @bodyParam name string The name of the category. Example: Work Tasks
     * @bodyParam color string The color code for the category (hexadecimal). Example: #0000ff
     * 
     * @response {
     *   "status": "Success",
     *   "message": "Category updated successfully", 
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "name": "Work Tasks",
     *     "color": "#0000ff",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     * 
     * @response 403 {
     *   "status": "Error",
     *   "message": "Unauthorized",
     *   "data": null
     * }
     * 
     * @response 404 {
     *   "status": "Error",
     *   "message": "No query results for model [App\\Models\\Category] 1",
     *   "data": null
     * }
     */
    public function update(Request $request, Category $category)
    {
        // Check if the authenticated user owns the category
        if ($request->user()->id !== $category->user_id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $category->update($validated);

        return $this->successResponse($category, 'Category updated successfully');
    }

    /**
     * Delete a category
     *
     * Deletes the specified category if it belongs to the authenticated user.
     * Associated tasks will have their category_id set to null.
     * 
     * @authenticated
     * 
     * @urlParam category required The ID of the category. Example: 1
     * 
     * @response 200 {
     *   "status": "Success",
     *   "message": "Category deleted successfully",
     *   "data": null
     * }
     * 
     * @response 403 {
     *   "status": "Error",
     *   "message": "Unauthorized",
     *   "data": null
     * }
     * 
     * @response 404 {
     *   "status": "Error",
     *   "message": "No query results for model [App\\Models\\Category] 1",
     *   "data": null
     * }
     */
    public function destroy(Request $request, Category $category)
    {
        // Check if the authenticated user owns the category
        if ($request->user()->id !== $category->user_id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Optional: Set category_id to null for associated tasks
        $category->tasks()->update(['category_id' => null]);

        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
