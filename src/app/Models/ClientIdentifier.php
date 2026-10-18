<?php

namespace App\Models;

use App\Enums\IdentifierType;
use Database\Factories\ClientIdentifierFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A Student or Alumni ID on a profile. Identifiers are retired, never deleted or
 * reassigned, so an ID cannot later belong to a different person.
 *
 * @property int $id
 * @property int $client_profile_id
 * @property IdentifierType $type
 * @property string $display_value
 * @property string $canonical_value
 * @property int $normalization_version
 * @property int $added_by_id
 * @property Carbon $added_at
 * @property Carbon|null $retired_at
 * @property int|null $retired_by_id
 * @property string|null $retire_reason
 */
#[Table(name: 'client_identifiers', timestamps: false)]
class ClientIdentifier extends Model
{
    /** @use HasFactory<ClientIdentifierFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ClientProfile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class, 'client_profile_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function retiredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'retired_by_id');
    }

    public function isCurrent(): bool
    {
        return $this->retired_at === null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => IdentifierType::class,
            'normalization_version' => 'integer',
            'added_at' => 'datetime',
            'retired_at' => 'datetime',
        ];
    }
}
