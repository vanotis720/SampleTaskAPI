<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Task Management
 *
 * APIs for managing tasks
 */
class TaskController extends Controller
{
    use ApiResponser;

    /**
     * List all tasks
     *
     * Returns a paginated list of the authenticated user's tasks.
     * Results can be filtered by status, priority, category, and title search.
     * 
     * @authenticated
     * 
     * @queryParam status string Filter tasks by status (pending, in_progress, completed). Example: pending
     * @queryParam priority string Filter tasks by priority (low, medium, high). Example: high
     * @queryParam category_id integer Filter tasks by category ID. Example: 1
     * @queryParam search string Search tasks by title. Example: Project
     * @queryParam page integer Page number for pagination. Example: 1
     * 
     * @response {
     *   "status": "Success",
     *   "message": null,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "user_id": 1,
     *         "category_id": 1,
     *         "title": "Complete project",
     *         "description": "Finish the API implementation",
     *         "due_date": "2023-12-31",
     *         "status": "pending",
     *         "priority": "high",
     *         "image_path": null,
     *         "created_at": "2023-01-01T00:00:00.000000Z",
     *         "updated_at": "2023-01-01T00:00:00.000000Z",
     *         "category": {
     *           "id": 1,
     *           "user_id": 1,
     *           "name": "Work",
     *           "color": "#ff0000",
     *           "created_at": "2023-01-01T00:00:00.000000Z",
     *           "updated_at": "2023-01-01T00:00:00.000000Z"
     *         }
     *       }
     *     ],
     *     "first_page_url": "http://localhost/api/tasks?page=1",
     *     "from": 1,
     *     "last_page": 1,
     *     "last_page_url": "http://localhost/api/tasks?page=1",
     *     "links": [],
     *     "next_page_url": null,
     *     "path": "http://localhost/api/tasks",
     *     "per_page": 10,
     *     "prev_page_url": null,
     *     "to": 1,
     *     "total": 1
     *   }
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
        $query = $request->user()->tasks();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $tasks = $query->with('category')->paginate(10);

        return $this->successResponse($tasks);
    }

    /**
     * Create a new task
     *
     * Creates a new task for the authenticated user.
     * 
     * @authenticated
     * 
     * @bodyParam title string required The title of the task. Example: Complete project
     * @bodyParam description string The description of the task. Example: Finish the API implementation
     * @bodyParam due_date date The due date for the task. Example: 2023-12-31
     * @bodyParam status string The status of the task (pending, in_progress, completed). Example: pending
     * @bodyParam priority string The priority of the task (low, medium, high). Example: high
     * @bodyParam category_id integer The ID of the category for this task. Example: 1
     * 
     * @response 201 {
     *   "status": "Success",
     *   "message": "Task created successfully",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "category_id": 1,
     *     "title": "Complete project",
     *     "description": "Finish the API implementation",
     *     "due_date": "2023-12-31",
     *     "status": "pending",
     *     "priority": "high",
     *     "image_path": null,
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     * 
     * @response 422 {
     *   "status": "Error",
     *   "message": "The title field is required.",
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
            'priority' => 'nullable|string|in:low,medium,high',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $task = $request->user()->tasks()->create($validated);

        return $this->successResponse($task, 'Task created successfully', 201);
    }

    /**
     * Get a specific task
     *
     * Returns details for a specific task if it belongs to the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam task required The ID of the task. Example: 1
     * 
     * @response {
     *   "status": "Success",
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "category_id": 1,
     *     "title": "Complete project",
     *     "description": "Finish the API implementation",
     *     "due_date": "2023-12-31",
     *     "status": "pending",
     *     "priority": "high",
     *     "image_path": null,
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z",
     *     "category": {
     *       "id": 1,
     *       "user_id": 1,
     *       "name": "Work",
     *       "color": "#ff0000",
     *       "created_at": "2023-01-01T00:00:00.000000Z",
     *       "updated_at": "2023-01-01T00:00:00.000000Z"
     *     }
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
     *   "message": "No query results for model [App\\Models\\Task] 1",
     *   "data": null
     * }
     */
    public function show(Request $request, Task $task)
    {
        // Check if the authenticated user owns the task
        if ($request->user()->id !== $task->user_id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($task->load('category'));
    }

    /**
     * Update a task
     *
     * Updates the specified task if it belongs to the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam task required The ID of the task. Example: 1
     * @bodyParam title string The title of the task. Example: Updated project title
     * @bodyParam description string The description of the task. Example: Updated description
     * @bodyParam due_date date The due date for the task. Example: 2024-01-15
     * @bodyParam status string The status of the task (pending, in_progress, completed). Example: in_progress
     * @bodyParam priority string The priority of the task (low, medium, high). Example: medium
     * @bodyParam category_id integer The ID of the category for this task. Example: 2
     * 
     * @response {
     *   "status": "Success",
     *   "message": "Task updated successfully",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "category_id": 2,
     *     "title": "Updated project title",
     *     "description": "Updated description",
     *     "due_date": "2024-01-15",
     *     "status": "in_progress",
     *     "priority": "medium",
     *     "image_path": null,
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
     *   "message": "No query results for model [App\\Models\\Task] 1",
     *   "data": null
     * }
     */
    public function update(Request $request, Task $task)
    {
        // Check if the authenticated user owns the task
        if ($request->user()->id !== $task->user_id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
            'priority' => 'nullable|string|in:low,medium,high',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $task->update($validated);

        return $this->successResponse($task, 'Task updated successfully');
    }

    /**
     * Delete a task
     *
     * Deletes the specified task if it belongs to the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam task required The ID of the task. Example: 1
     * 
     * @response 200 {
     *   "status": "Success",
     *   "message": "Task deleted successfully",
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
     *   "message": "No query results for model [App\\Models\\Task] 1",
     *   "data": null
     * }
     */
    public function destroy(Request $request, Task $task)
    {
        // Check if the authenticated user owns the task
        if ($request->user()->id !== $task->user_id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $task->delete();

        return $this->successResponse(null, 'Task deleted successfully');
    }

    /**
     * Upload task image
     *
     * Uploads an image for the specified task if it belongs to the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam task required The ID of the task. Example: 1
     * @bodyParam image file required The image file to upload (max 2MB, image file). No-example
     * 
     * @response {
     *   "status": "Success",
     *   "message": "Image uploaded successfully",
     *   "data": {
     *     "image_path": "task-images/abc123.jpg"
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
     *   "message": "No query results for model [App\\Models\\Task] 1",
     *   "data": null
     * }
     * 
     * @response 422 {
     *   "status": "Error",
     *   "message": "The image field is required.",
     *   "data": null
     * }
     */
    public function uploadImage(Request $request, Task $task)
    {
        // Check if the authenticated user owns the task
        if ($request->user()->id !== $task->user_id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        try {
            $request->validate([
                'image' => 'required|image|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->validator->errors()->first(), 422);
        }

        // Delete old image if exists
        if ($task->image_path && Storage::exists($task->image_path)) {
            Storage::delete($task->image_path);
        }

        // Store the new image
        $path = $request->file('image')->store('task-images', 'public');
        $task->update(['image_path' => $path]);

        return $this->successResponse(['image_path' => $path], 'Image uploaded successfully');
    }
}
