<?php

namespace App\Http\Requests\Registrar;

use App\Http\Requests\Admin\ReasonRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Every change to an existing profile carries the version the staff member was
 * looking at, so a stale screen cannot overwrite a newer edit, and a reason.
 */
class ClientChangeRequest extends ReasonRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'version' => ['required', 'integer', 'min:1'],
        ];
    }

    public function version(): int
    {
        return (int) $this->validated('version');
    }
}
