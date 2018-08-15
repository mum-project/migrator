<?php

namespace App\Migrators;

interface IMigrator
{
    /**
     * Migrate the old data from it's database into MUM's database.
     *
     * @return bool
     */
    public function migrate(): bool;
}