<?php

namespace App\Models\Mum;

use function getDomainOfEmailAddress;
use function getLocalPartOfEmailAddress;
use Illuminate\Database\Eloquent\Builder;

class Mailbox extends BaseModel
{
    /**
     * Gets the domain that this mailbox belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Scope a query to include mailboxes matching the supplied email address.
     *
     * @param Builder $query
     * @param string  $address
     * @return Builder
     */
    public function scopeWhereAddress(Builder $query, string $address)
    {
        return $query->where('local_part', getLocalPartOfEmailAddress($address))
            ->whereHas('domain', function (Builder $query) use ($address) {
                $query->where('domain', getDomainOfEmailAddress($address));
            });
    }

    /**
     * Gets the complete email address built from the local_part and the domain of this mailbox.
     *
     * @return string
     */
    public function address()
    {
        return $this->local_part . '@' . $this->domain->domain;
    }
}