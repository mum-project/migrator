<?php

namespace App\Migrators\VimbAdmin;

use App\Migrators\IdMatcher;
use App\Migrators\IMigrator;
use App\Models\Mum\Alias;
use App\Models\Mum\Mailbox;
use App\Models\VimbAdmin\Alias as VimbAdminAlias;
use Exception;
use function explode;
use function getLocalPartOfEmailAddress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

class AliasMigrator implements IMigrator
{
    /** @var IdMatcher */
    protected $idMatcher;

    public function __construct()
    {
        $this->idMatcher = app(IdMatcher::class);
    }

    /**
     * Migrate the ViMbAdmin data from it's database into MUM's database.
     *
     * @return bool
     */
    public function migrate(): bool
    {
        return !!$this->extractChunks(function (VimbAdminAlias $alias) {
            return $this->createMumAlias($alias);
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
        return VimbAdminAlias::query()->chunk(100, function (Collection $aliases) use ($callback) {
            $failed = false;
            $aliases->each(function (VimbAdminAlias $alias) use ($callback, &$failed) {
                $output = $callback($alias);
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
     * @param VimbAdminAlias $vimbAdminAlias
     * @return bool
     */
    protected function createMumAlias(VimbAdminAlias $vimbAdminAlias): bool
    {
        try {
            $domainId = $this->idMatcher->getMumId('domain', $vimbAdminAlias->Domain_id);
            if (!$domainId) {
                throw new UnexpectedValueException('no domain id');
            }
            $data = [
                'local_part'    => getLocalPartOfEmailAddress($vimbAdminAlias->address),
                'description'   => null,
                'domain_id'     => $domainId,
                'active'        => $vimbAdminAlias->active,
                'deactivate_at' => null,
                'created_at'    => $vimbAdminAlias->created,
                'updated_at'    => $vimbAdminAlias->modified ?: $vimbAdminAlias->created,
            ];

            DB::connection('mysql_mum')->beginTransaction();

            $alias = Alias::create($data);
            $this->createMumAliasSendersAndRecipients($alias, $vimbAdminAlias->goto);
            $this->idMatcher->addPair('alias', $vimbAdminAlias->id, $alias->id);

            DB::connection('mysql_mum')->commit();

            Log::info('Created alias ' . $alias->address());

            return $alias->exists;

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            Log::debug($exception->getTraceAsString());
            DB::connection('mysql_mum')->rollBack();
            return false;
        }
    }

    /**
     * Create sender and recipient entries for the provided alias.
     *
     * @param Alias  $alias
     * @param string $goto
     * @return void
     */
    protected function createMumAliasSendersAndRecipients(Alias $alias, string $goto): void
    {
        $recipients = explode(',', $goto);

        Collection::make($recipients)->map(function (string $address) use ($alias) {
            /** @var Mailbox $mailbox */
            $mailbox = Mailbox::whereAddress($address)->first();

            DB::connection('mysql_mum')->table('alias_recipients')->insert([
                'alias_id'          => $alias->id,
                'recipient_address' => $address,
                'mailbox_id'        => $mailbox ? $mailbox->id : null,
            ]);

            if (!$mailbox) {
                return;
            }

            DB::connection('mysql_mum')->table('alias_senders')->insert([
                'mailbox_id' => $mailbox->id,
                'alias_id'   => $alias->id,
            ]);
        });
    }
}