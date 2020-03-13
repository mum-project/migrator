<?php

namespace App\Commands;

use App\Models\Mum\Mailbox;
use App\Models\PasswordHash\PasswordHashInput;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function app;
use App\Migrators\VimbAdmin\AliasMigrator;
use App\Migrators\VimbAdmin\DomainMigrator;
use App\Migrators\VimbAdmin\MailboxMigrator;
use LaravelZero\Framework\Commands\Command;
use function fgetcsv;
use function fopen;

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

        $this->table(['Username', 'Domain', 'Hash'], $hashes->toArray());

        $confirmed =
            $this->confirm('Do you want to write these hashes to your database, thus overwriting the existing ones?');
        if (!$confirmed) {
            $this->error('You said no, aborting...');
            return;
        }

        $this->task('Migrating Passwords', function () use ($hashes) {
            DB::connection('mysql_mum')->beginTransaction();
            $hashes->each(function (array $row) {
                /** @var Mailbox $mailbox */
                $mailbox = Mailbox::whereAddress($row[0] . '@' . $row[1])->firstOrFail();
                $mailbox->password = $row[2];
                $mailbox->saveOrFail();
            });
            DB::connection('mysql_mum')->commit();
        });
    }

    /**
     * Read the supplied CSV file with password hashes.
     * We assume it has the following layout and is separated by double quotes.
     * | username | domain | hash    |
     * | -------- | ------ | ------- |
     * | joe      | doe.co | $2y$... |
     *
     * @return Collection
     */
    private function readHashes(): Collection
    {
        $file = fopen($this->option('csv-file'), 'r');
        $hashes = Collection::make();
        while ($row = fgetcsv($file, 1000, ',')) {
            $hashes->add([$row[0], $row[1], $row[2]]);
        }
        return $hashes;
    }
}
