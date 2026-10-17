<?php

namespace App\Models;

use App\Enums\OfficeCode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property OfficeCode $code
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'is_active'])]
class Office extends Model
{
    public static function forCode(OfficeCode $code): self
    {
        return static::query()->where('code', $code->value)->firstOrFail();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'code' => OfficeCode::class,
            'is_active' => 'boolean',
        ];
    }
}
