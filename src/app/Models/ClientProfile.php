<?php

namespace App\Models;

use App\Enums\ClientCategory;
use App\Enums\VerificationState;
use Carbon\CarbonInterface;
use Database\Factories\ClientProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Identifies a requester. Not an academic record and never a login account.
 * Change it only through the actions in App\Actions\Clients, which keep the
 * version, revision history and audit trail in step.
 *
 * @property int $id
 * @property int $office_id
 * @property string $display_name
 * @property ClientCategory $category
 * @property VerificationState $verification_state
 * @property string $source_reference
 * @property int|null $verified_by_id
 * @property CarbonInterface|null $verified_at
 * @property int $created_by_id
 * @property int $version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ClientIdentifier> $identifiers
 * @property-read Collection<int, ClientIdentifier> $currentIdentifiers
 * @property-read Collection<int, ClientProfileRevision> $revisions
 */
#[Fillable(['office_id', 'display_name', 'category', 'source_reference'])]
class ClientProfile extends Model
{
    /** @use HasFactory<ClientProfileFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Office, $this>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Every identifier, including retired ones (history).
     *
     * @return HasMany<ClientIdentifier, $this>
     */
    public function identifiers(): HasMany
    {
        return $this->hasMany(ClientIdentifier::class)->orderBy('added_at')->orderBy('id');
    }

    /**
     * @return HasMany<ClientIdentifier, $this>
     */
    public function currentIdentifiers(): HasMany
    {
        return $this->hasMany(ClientIdentifier::class)->whereNull('retired_at')->orderBy('added_at')->orderBy('id');
    }

    /**
     * @return HasMany<ClientProfileRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(ClientProfileRevision::class)->orderByDesc('revision_number');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ClientCategory::class,
            'verification_state' => VerificationState::class,
            'verified_at' => 'datetime',
            'version' => 'integer',
        ];
    }
}
