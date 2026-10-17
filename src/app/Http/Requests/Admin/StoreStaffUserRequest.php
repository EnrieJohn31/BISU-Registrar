<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ProfileValidationRules;
use App\Enums\Role;
use App\Models\Office;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStaffUserRequest extends ReasonRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            ...$this->profileRules(),
            'office_id' => ['nullable', 'integer', Rule::exists(Office::class, 'id')->where('is_active', true)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'distinct', Rule::enum(Role::class)],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $office = $this->office();

                foreach ($this->roles() as $role) {
                    if ($role->isCampusWide()) {
                        continue;
                    }

                    if ($office === null) {
                        $validator->errors()->add('office_id', __('Choose an office for :role.', ['role' => $role->label()]));
                    } elseif (! $role->isAllowedIn($office->code)) {
                        $validator->errors()->add('roles', __(':role cannot be assigned in :office.', ['role' => $role->label(), 'office' => $office->name]));
                    }
                }
            },
        ];
    }

    /**
     * @return list<Role>
     */
    public function roles(): array
    {
        /** @var array<int, string> $roles */
        $roles = (array) $this->input('roles', []);

        return array_values(array_map(fn (string $role): Role => Role::from($role), $roles));
    }

    public function office(): ?Office
    {
        $officeId = $this->input('office_id');

        return $officeId === null || $officeId === '' ? null : Office::query()->find((int) $officeId);
    }
}
