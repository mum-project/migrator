<?php

namespace App\Migrators\VimbAdmin;

use App\Migrators\IdMatcher;
use App\Migrators\IMigrator;
use App\Models\Mum\Mailbox;
use App\Models\VimbAdmin\Mailbox as VimbAdminMailbox;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

class MailboxMigrator implements IMigrator
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
        return $this->extractChunks(function (VimbAdminMailbox $mailbox) {
            return $this->createMumMailbox($mailbox);
        });
    }

    /**
     * Query mailboxes from ViMbAdmin's database by chunking them into groups,
     * iterating through each group and passing each mailbox model to the provided callback.
     *
     * @param $callback
     * @return bool
     */
    protected function extractChunks($callback): bool
    {
        return VimbAdminMailbox::query()->chunk(100, function (Collection $mailboxes) use ($callback) {
            $failed = false;
            $mailboxes->each(function (VimbAdminMailbox $mailbox) use ($callback, &$failed) {
                $output = $callback($mailbox);
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
     * Create a MUM mailbox for the provided ViMbAdmin mailbox.
     *
     * @param VimbAdminMailbox $vimbAdminMailbox
     * @return bool
     */
    protected function createMumMailbox(VimbAdminMailbox $vimbAdminMailbox): bool
    {
        try {
            $domainId = $this->idMatcher->getMumId('domain', $vimbAdminMailbox->Domain_id);
            if (!$domainId) {
                throw new UnexpectedValueException('no domain id');
            }
            $data = [
                'local_part'        => $vimbAdminMailbox->local_part,
                'password'          => $vimbAdminMailbox->password,
                'remember_token'    => null,
                'name'              => $vimbAdminMailbox->name,
                'domain_id'         => $domainId,
                'alternative_email' => $vimbAdminMailbox->alt_email ?: null,
                'quota'             => $vimbAdminMailbox->quota ? getGbFromB($vimbAdminMailbox->quota) : null,
                'homedir'           => $vimbAdminMailbox->homedir,
                'maildir'           => $vimbAdminMailbox->maildir,
                'is_super_admin'    => false,
                'send_only'         => false,
                'active'            => $vimbAdminMailbox->active,
                'created_at'        => $vimbAdminMailbox->created,
                'updated_at'        => $vimbAdminMailbox->modified ?: $vimbAdminMailbox->created
            ];

            $mailbox = Mailbox::create($data);
            $this->idMatcher->addPair('mailbox', $vimbAdminMailbox->id, $mailbox->id);
            Log::info('Created mailbox ' . $mailbox->address());
            return $mailbox->exists;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            Log::debug($exception->getTraceAsString());
            return false;
        }
    }

}