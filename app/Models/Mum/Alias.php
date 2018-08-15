<?php

namespace App\Models\Mum;

class Alias extends BaseModel
{
    /**
     * Gets the domain that this alias belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Gets the complete email address built from the local_part and the domain of this alias.
     *
     * @return string
     */
    public function address()
    {
        return $this->local_part . '@' . $this->domain->domain;
    }
}