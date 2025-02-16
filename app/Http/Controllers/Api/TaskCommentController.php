<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskComment\TaskCommentRequest;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {

    }

    /** Store a newly created resource in storage. */
    public function store(TaskCommentRequest $request, Task $task)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['task_id'] = $task->id;

        $taskComment = $task->comments()->create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Comment created successfully',
            'data' => $taskComment,
        ], 201);
    }

    /** Display the specified resource. */
    public function show(TaskComment $taskComment)
    {

    }

    /** Update the specified resource in storage. */
    public function update(Request $request, TaskComment $taskComment)
    {

    }

    /** Remove the specified resource from storage. */
    public function destroy(TaskComment $taskComment)
    {

    }
}
