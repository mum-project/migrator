<p align="center"><img src="https://mum-project.github.io/docs/img/mum.svg" height="64"></p>

# MUM Migrator

This is a tool for migrating old data from other mailbox administration systems into [MUM](https://mum-project.github.io/docs/).
The idea of this console program is to save you from having to copy every address over by hand.
Instead, configure the necessary database connection options in a `.env` file and let this tool do the rest.
We chunk results from the database, so there should not be a problem with large numbers of domains / mailboxes / aliases.
MUM Migrator currently only supports migrating data from ViMbAdmin.

## Installation

Simply clone this repository and configure the necessary database options.

```bash
git clone https://github.com/mum-project/migrator.git
cd migrator/
cp .env.example .env
nano .env
```

## Migrating from ViMbAdmin

The first step is to configure all database options for both the already migrated MUM database (prefixed with `MUM_DB_`
in the `.env` file) and the ViMbAdmin database (prefixed with `VIMBADMIN_DB_` in the `.env` file).

Since ViMbAdmin does not save a root folder path for home directories in their database, you will need to provide
that path with a console option when calling the migrator script. We assume your home directories are created according
to the following scheme: `/path/to/root/%d/%m` where `%d` stands for the domain and `%m` for the mailbox (it does not
matter if this is the local part or the address).

To migrate your data, call MUM Migrator:

```bash
php migrator migrate:vimbadmin --homedir-root /path/to/root
```

MUM's database should now contain your migrated data.

## Contributing
You want to help? Awesome! Have a look at the [Contribution Guide](CONTRIBUTING.md) and start coding.

## License
The [MIT license](https://opensource.org/licenses/MIT). 
Please see the [license file](LICENSE.md) for more information.

Copyright &copy; 2018 Martin Bock.