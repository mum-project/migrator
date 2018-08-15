<?php

namespace App\Migrators;

use App\Exceptions\DuplicateKeyException;
use function array_key_exists;
use function var_dump;

class IdMatcher
{
    protected $matches = [];

    /**
     * Add a pair of ids to the provided type.
     *
     * @param string $type
     * @param int    $migratingId
     * @param int    $mumId
     * @throws DuplicateKeyException
     */
    public function addPair(string $type, int $migratingId, int $mumId): void
    {
        $this->ensureTypeExists($type);
        if (array_key_exists($migratingId, $this->matches[$type])) {
            throw new DuplicateKeyException('Duplicate old key ' . $migratingId . ' for type ' . $type);
        }
        $this->matches[$type][$migratingId] = $mumId;
    }

    /**
     * Get all pairs of ids of the provided type.
     *
     * @param string $type
     * @return array
     */
    public function getPairs(string $type): array
    {
        if (!array_key_exists($type, $this->matches)) {
            return [];
        }
        return $this->matches[$type];
    }

    /**
     * Get MUM's id for the provided old id of the provided type.
     *
     * @param string $type
     * @param int    $migratingId
     * @return int|null
     */
    public function getMumId(string $type, int $migratingId): ?int
    {
        if (array_key_exists($type, $this->matches) && array_key_exists($migratingId, $this->matches[$type])) {
            return $this->matches[$type][$migratingId];
        }
        return null;
    }

    /**
     * Create an array for the provided type if it does not already exist.
     *
     * @param string $type
     */
    protected function ensureTypeExists(string $type): void
    {
        if (!array_key_exists($type, $this->matches)) {
            $this->matches[$type] = [];
        }
    }
}