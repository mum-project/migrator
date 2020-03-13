<?php

namespace App\Commands;

use App\Models\Mum\Mailbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use LaravelZero\Framework\Commands\Command;
use function array_push;
use function fgetcsv;
use function fopen;
use function preg_replace;

class MigratePasswords extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'migrate:passwords
                            {--csv-file= : CSV file with new password hashes}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Migrate password hashes from one algorithm to another.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->option('csv-file')) {
            $this->warn('php migrator ' . $this->getSynopsis(false));
            $this->error('Please specify the CSV file with the new password hashes.');
            return;
        }

        $hashes = $this->readHashes();

        $this->table(['Mailbox', 'Hash'], $hashes);

        $confirmed =
            $this->confirm('Do you want to write these hashes to your database, thus overwriting the existing ones?');
        if (!$confirmed) {
            $this->error('You said no, aborting...');
            return;
        }

        $output = $this->task('Migrating Passwords', function () use ($hashes) {
            try {
                DB::connection('mysql_mum')->beginTransaction();
                foreach ($hashes as $row) {
                    /** @var Mailbox $mailbox */
                    $mailbox = Mailbox::whereAddress($row['mailbox'])->firstOrFail();
                    $mailbox->password = $row['hash'];
                    $mailbox->saveOrFail();
                    Log::debug('Overwritten password hash for ' . $row['mailbox']);
                }
                DB::connection('mysql_mum')->commit();
            } catch (Throwable $exception) {
                DB::connection('mysql_mum')->rollBack();
                Log::error('Failed to migrate passwords: ' . $exception);
                return 1;
            }
        });

        if (!$output) {
            $this->comment('Have a look in the log file for more details.');
        }
    }

    /**
     * Read the supplied CSV file with password hashes.
     * We assume it has the following layout and is separated by double quotes.
     *
     *      | mailbox        | hash               |
     *      | -------------- | ------------------ |
     *      | jon @ doe.com  | $2y$...            |
     *      | jane @ doe.com | {BLF-CRYPT}$2y$... |
     *
     * If your hash has a prefix appended by Dovecot's `doveadm`,
     * this method will remove it for you.
     *
     * @return array
     */
    private function readHashes(): array
    {
        $file = fopen($this->option('csv-file'), 'r');
        $hashes = [];
        while ($row = fgetcsv($file, 1000, ',')) {
            $mailbox = $row[0];
            $hash = preg_replace('/({\S+})?(\$\S{1,2}\$.+$)/', '$2', $row[1]);
            array_push($hashes, ['mailbox' => $mailbox, 'hash' => $hash]);
        }
        return $hashes;
    }
}
