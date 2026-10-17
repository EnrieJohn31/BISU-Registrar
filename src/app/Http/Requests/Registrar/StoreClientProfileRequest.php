<?php

namespace App\Http\Requests\Registrar;

use App\Enums\ClientCategory;
use App\Enums\IdentifierType;
use App\Http\Requests\Admin\ReasonRequest;
use App\Http\Requests\Registrar\Concerns\ValidatesClientFields;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreClientProfileRequest extends ReasonRequest
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
            // Where the details came from, for example "Registrar counter, walk-in".
            'source_reference' => ['required', 'string', 'min:3', 'max:255'],
            'identifier_type' => $this->identifierTypeRules(),
            'identifier_value' => $this->identifierValueRules(),
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

    public function sourceReference(): string
    {
        return trim((string) $this->validated('source_reference'));
    }

    public function identifierType(): IdentifierType
    {
        return IdentifierType::from((string) $this->validated('identifier_type'));
    }

    public function identifierValue(): string
    {
        return (string) $this->validated('identifier_value');
    }
}
