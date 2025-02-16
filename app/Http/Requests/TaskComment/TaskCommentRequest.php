<?php

namespace App\Http\Requests\TaskComment;

use App\Enums\UserRoleEnum;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class TaskCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get the task based on the ID passed in the URL
        $task = $this->route('task');
        $user = auth()->user();

        if (
            auth()->check() &&
            $user->client_id === $task->client_id &&
            ($user->id === $task->created_by || $user->id === $task->responsible_id)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:255'],
        ];
    }
}
