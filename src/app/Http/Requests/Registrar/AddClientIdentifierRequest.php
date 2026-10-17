<?php

namespace App\Http\Requests\Registrar;

use App\Enums\IdentifierType;
use App\Http\Requests\Registrar\Concerns\ValidatesClientFields;
use Illuminate\Contracts\Validation\ValidationRule;

class AddClientIdentifierRequest extends ClientChangeRequest
{
    use ValidatesClientFields;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'identifier_type' => $this->identifierTypeRules(),
            'identifier_value' => $this->identifierValueRules(),
        ];
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
