<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {

        $user = auth()->user();

        $query = Task::with([
            'createdBy:id,name,email',
            'team:id,name',
            'building',
        ])->where('client_id', $user->client_id);

        if ($user->role !== UserRoleEnum::OWNER) {
            $query->where('team_id', $user->team_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Filter by building
        if ($request->has('building_id')) {
            $query->where('building_id', $request->input('building_id'));
        }

        // Filter by range of created_at
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $query->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate]);

        // has pagination
        $data = $request->boolean('paginate', false) ? $query->paginate() : $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTaskRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        // Validate the request data
        $data = $request->validated();

        // Add additional data to the task
        $data['client_id'] = auth()->user()->client_id;
        $data['created_by'] = auth()->id();
        $data['due_date'] = now()->addDays(3)->toDateString();

        // Create the task
        $task = Task::create($data);

        // Return a JSON response with the created task
        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully',
            'data' => $task
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        if (!$task->client_id === auth()->user()->client_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This action is unauthorized.'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Task found successfully',
            'data' => $task
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        // Validate the request data
        $data = $request->validated();

        // Update the task
        $task->update($data);

        // Return a JSON response with the updated task
        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully',
            'data' => $task
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if (!$task->client_id === auth()->user()->client_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This action is unauthorized.'
            ], 403);
        }

        // Realiza o Soft Delete
        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully',
        ]);
    }
}
