<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Models\Office;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends ReasonRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'role' => ['required', Rule::enum(Role::class)],
            'office_id' => ['nullable', 'integer', Rule::exists(Office::class, 'id')->where('is_active', true)],
        ];
    }

    public function role(): Role
    {
        return Role::from((string) $this->validated('role'));
    }

    public function office(): ?Office
    {
        $officeId = $this->validated('office_id');

        return $officeId === null ? null : Office::query()->findOrFail((int) $officeId);
    }
}
