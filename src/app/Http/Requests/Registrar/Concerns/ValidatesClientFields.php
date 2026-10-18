<?php

namespace App\Http\Requests\Registrar\Concerns;

use App\Enums\ClientCategory;
use App\Enums\IdentifierType;
use App\Support\Clients\IdentifierNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ValidatesClientFields
{
    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function displayNameRules(): array
    {
        // Unicode names, with no control characters. No first/middle/last split is
        // assumed: the name is stored as the Registrar writes it.
        return ['required', 'string', 'min:2', 'max:255', 'regex:/\A[^\p{Cc}]+\z/u'];
    }

    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function categoryRules(): array
    {
        return ['required', Rule::enum(ClientCategory::class)];
    }

    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function identifierTypeRules(): array
    {
        return ['required', Rule::enum(IdentifierType::class)];
    }

    /**
     * @return array<int, Closure|string>
     */
    protected function identifierValueRules(): array
    {
        return [
            'required',
            'string',
            'max:64',
            function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value) || IdentifierNormalizer::display($value) === null) {
                    $fail(IdentifierNormalizer::DESCRIPTION);
                }
            },
        ];
    }

    /**
     * Collapse repeated spaces in the name before validating it.
     */
    protected function prepareClientNameForValidation(): void
    {
        $name = $this->input('display_name');

        if (is_string($name)) {
            $this->merge(['display_name' => trim((string) preg_replace('/\s+/u', ' ', $name))]);
        }
    }
}
