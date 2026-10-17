<?php

namespace App\Http\Requests\Registrar;

use App\Enums\ClientCategory;
use App\Http\Requests\Registrar\Concerns\ValidatesClientFields;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateClientProfileRequest extends ClientChangeRequest
{
    use ValidatesClientFields;

    protected function prepareForValidation(): void
    {
        $this->prepareClientNameForValidation();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'display_name' => $this->displayNameRules(),
            'category' => $this->categoryRules(),
        ];
    }

    public function displayName(): string
    {
        return (string) $this->validated('display_name');
    }

    public function category(): ClientCategory
    {
        return ClientCategory::from((string) $this->validated('category'));
    }
}
