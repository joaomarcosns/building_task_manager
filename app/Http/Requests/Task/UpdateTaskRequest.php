<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Enums\BuildingStatusEnum;
use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /** Determine if the user is authorized to make this request. */
    public function authorize(): bool
    {
        $task = $this->route('task');

        return
            auth()->check() &&
            auth()->user()->role === UserRoleEnum::OWNER &&
            $task &&
            $task->client_id === auth()->user()->client_id &&
            $task->created_by === auth()->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $task = $this->route('task');

        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', Rule::in(array_column(TaskPriorityEnum::cases(), 'value'))],
            'status' => ['nullable', 'string', Rule::in(array_column(TaskStatusEnum::cases(), 'value'))],
            'building_id' => [
                'nullable',
                'integer',
                Rule::exists('buildings', 'id')->where(function ($query) {
                    $query->where('client_id', auth()->user()->client_id)->where('status', BuildingStatusEnum::ACTIVE);
                }),
            ],
            'team_id' => [
                'nullable',
                'integer',
                Rule::exists('teams', 'id')->where(
                    fn ($query) => $query->where('client_id', auth()->user()->client_id)
                ),
            ],
            'responsible_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('client_id', auth()->user()->client_id)
                        ->where('team_id', $this->team_id);
                }),
                function ($attribute, $value, $fail) {
                    if ($value === auth()->user()->id) {
                        $fail('The responsible person cannot be the same as the user.');
                    }
                },
            ],
        ];

        // Custom validation to check if the task status is OPEN before allowing update
        if ($task && $task->status !== TaskStatusEnum::OPEN->value) {
            $rules['status'][] = function ($attribute, $value, $fail) {
                $fail('The task status can only be changed when the status is OPEN.');
            };
        }

        return $rules;
    }
}
