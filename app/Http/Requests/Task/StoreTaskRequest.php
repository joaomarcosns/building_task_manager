<?php

namespace App\Http\Requests\Task;

use App\Enums\BuildingStatusEnum;
use App\Enums\TaskPriorityEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', Rule::in(array_column(TaskPriorityEnum::cases(), 'value'))],
            'building_id' => [
                'required',
                'integer',
                'max:255',
                Rule::exists('buildings', 'id')->where(function ($query) {
                    $query->where('client_id', auth()->user()->client_id)->where('status', BuildingStatusEnum::ACTIVE);
                }),
            ],
            'team_id' => [
                'required',
                'integer',
                Rule::exists('teams', 'id')->where(function ($query) {
                    $query->where('client_id', auth()->user()->client_id);
                }),
            ],
        ];
    }
}
