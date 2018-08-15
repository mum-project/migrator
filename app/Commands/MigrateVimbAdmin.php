<?php

namespace App\Commands;

use function app;
use App\Migrators\VimbAdmin\AliasMigrator;
use App\Migrators\VimbAdmin\DomainMigrator;
use App\Migrators\VimbAdmin\MailboxMigrator;
use LaravelZero\Framework\Commands\Command;

class MigrateVimbAdmin extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'migrate:vimbadmin
                            {--homedir-root= : Root directory for homedir folders}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Migrate all ViMbAdmin data to MUM';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->option('homedir-root')) {
            $this->warn('php migrator ' . $this->getSynopsis(false));
            $this->error('Please specify the root directory for homedir folders');
            return;
        }
        $output = $this->task('Migrating Domains', function () {
            return app(DomainMigrator::class, [$this->option('homedir-root')])->migrate();
        });

        if (!$output) {
            $this->comment('Have a look in the log file for more details.');
            return;
        }

        $output = $this->task('Migrating Mailboxes', function () {
            return app(MailboxMigrator::class)->migrate();
        });

        if (!$output) {
            return;
        }

        $this->task('Migrating Aliases', function () {
            return app(AliasMigrator::class)->migrate();
        });
    }
}
