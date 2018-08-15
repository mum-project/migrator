<?php

namespace App\Models\Mum;

class Domain extends BaseModel
{
    /**
     * Gets all mailboxes that belong to this domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mailboxes()
    {
        return $this->hasMany(Mailbox::class);
    }

    /**
     * Gets all aliases that belong to this domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aliases()
    {
        return $this->hasMany(Alias::class);
    }
}