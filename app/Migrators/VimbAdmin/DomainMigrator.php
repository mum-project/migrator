<?php

namespace App\Migrators\VimbAdmin;

use App\Migrators\IdMatcher;
use App\Migrators\IMigrator;
use App\Models\Mum\Domain;
use App\Models\VimbAdmin\Domain as VimbAdminDomain;
use Exception;
use function getGbFromB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use function rtrim;

class DomainMigrator implements IMigrator
{
    /** @var IdMatcher */
    protected $idMatcher;

    /** @var string */
    protected $homedirRoot;

    public function __construct(string $homedirRoot)
    {
        $this->idMatcher = app(IdMatcher::class);
        $this->homedirRoot = $homedirRoot;
    }

    public function migrate(): bool
    {
        return $this->extractChunks(function (VimbAdminDomain $domain): bool {
            return $this->createMumDomain($domain);
        });
    }

    /**
     * Query domains from ViMbAdmin's database by chunking them into groups,
     * iterating through each group and passing each domain model to the provided callback.
     *
     * @param $callback
     * @return bool
     */
    protected function extractChunks($callback): bool
    {
        return VimbAdminDomain::query()->chunk(100, function (Collection $domains) use ($callback) {
            $failed = false;
            $domains->each(function (VimbAdminDomain $domain) use ($callback, &$failed) {
                $output = $callback($domain);
                if ($output === false) {
                    $failed = true;
                    return false;
                }
            });
            if ($failed === true) {
                return false;
            }
        });
    }

    /**
     * Create a MUM domain for the provided ViMbAdmin domain.
     *
     * @param VimbAdminDomain $vimbAdminDomain
     * @return bool
     */
    protected function createMumDomain(VimbAdminDomain $vimbAdminDomain): bool
    {
        try {
            $data = [
                'domain'        => $vimbAdminDomain->domain,
                'description'   => $vimbAdminDomain->description,
                'quota'         => $vimbAdminDomain->quota ? getGbFromB($vimbAdminDomain->quota) : null,
                'max_quota'     => $vimbAdminDomain->max_quota ? getGbFromB($vimbAdminDomain->max_quota) : null,
                'max_aliases'   => $vimbAdminDomain->max_aliases ?: null,
                'max_mailboxes' => $vimbAdminDomain->max_mailboxes ?: null,
                'homedir'       => $this->getHomedir($vimbAdminDomain),
                'active'        => $vimbAdminDomain->active,
                'created_at'    => $vimbAdminDomain->created,
                'updated_at'    => $vimbAdminDomain->modified ?: $vimbAdminDomain->created
            ];

            $domain = Domain::create($data);
            $this->idMatcher->addPair('domain', $vimbAdminDomain->id, $domain->id);

            Log::info('Created domain ' . $domain->domain);

            return $domain->exists;

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            Log::debug($exception->getTraceAsString());
            return false;
        }
    }

    /**
     * Get the homedir of the provided domain.
     *
     * @param VimbAdminDomain $domain
     * @return string
     */
    protected function getHomedir(VimbAdminDomain $domain): string
    {
        return rtrim($this->homedirRoot, '/') . '/' . $domain->domain;
    }
}