<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SqliteImportSeeder extends Seeder
{
    /**
     * The list of tables to exclude from the import process.
     * 'sqlite_sequence' is an internal SQLite table and should always be excluded.
     * You can add other tables like 'migrations', 'jobs', 'cache', etc., if needed.
     * @var array
     */
    protected array $excludedTables = [
        'sqlite_sequence',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $sqliteDbPath = env('SQLITE_IMPORT_PATH', database_path('import_from.sqlite'));

        if (!file_exists($sqliteDbPath)) {
            $this->command->error("SQLite database file not found at: {$sqliteDbPath}");
            $this->command->info("Please set the SQLITE_IMPORT_PATH environment variable or place the file at database/import_from.sqlite.");
            return;
        }

        $sqliteConnectionName = 'sqlite_import_source';
        Config::set("database.connections.{$sqliteConnectionName}", [
            'driver' => 'sqlite',
            'database' => $sqliteDbPath,
            'prefix' => '',
        ]);

        $targetConnectionName = DB::getDefaultConnection();

        try {
            $this->command->info("Starting import from SQLite: {$sqliteDbPath} to target connection: {$targetConnectionName}");

            $sqliteTables = DB::connection($sqliteConnectionName)->getDoctrineSchemaManager()->listTableNames();

            $sqliteTables = array_filter($sqliteTables, function ($table) {
                return !in_array($table, $this->excludedTables);
            });

            if (empty($sqliteTables)) {
                $this->command->warn("No tables (or only excluded tables) found in the SQLite database.");
                return;
            }

            $this->command->info("Tables to process from SQLite: " . implode(', ', $sqliteTables));

            Schema::connection($targetConnectionName)->disableForeignKeyConstraints();
            $this->command->info("Foreign key checks disabled on target database ('{$targetConnectionName}').");

            $this->command->info("Truncating target tables...");
            foreach ($sqliteTables as $tableName) {
                if (Schema::connection($targetConnectionName)->hasTable($tableName)) {
                    DB::connection($targetConnectionName)->table($tableName)->truncate();
                    $this->command->line("  Truncated: {$tableName}");
                } else {
                    $this->command->warn("  Table '{$tableName}' does not exist in target database. Skipping truncation.");
                }
            }

            $this->command->info("Importing data...");
            foreach ($sqliteTables as $tableName) {
                $this->command->line("Processing table: {$tableName}");
                if (!Schema::connection($targetConnectionName)->hasTable($tableName)) {
                    $this->command->warn("  Table '{$tableName}' does not exist in target database. Skipping data import.");
                    continue;
                }

                $data = DB::connection($sqliteConnectionName)->table($tableName)->get();

                if ($data->isNotEmpty()) {
                    $recordsToInsert = $data->map(fn($row) => (array) $row)->toArray();

                    foreach (array_chunk($recordsToInsert, 500) as $chunk) {
                        DB::connection($targetConnectionName)->table($tableName)->insert($chunk);
                    }
                    $this->command->line("  Imported " . count($recordsToInsert) . " records into {$tableName}");
                } else {
                    $this->command->line("  No data to import for {$tableName}");
                }
            }

            $this->command->info("SQLite data import completed successfully.");
        } catch (\Exception $e) {
            $this->command->error("An error occurred during SQLite import: " . $e->getMessage());
            Log::error("SqliteImportSeeder Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->command->info("Attempting to re-enable foreign key checks after error...");
        } finally {
            Schema::connection($targetConnectionName)->enableForeignKeyConstraints();
            $this->command->info("Foreign key checks re-enabled on target database ('{$targetConnectionName}').");
            Config::set("database.connections.{$sqliteConnectionName}", null); // Clean up temporary connection
        }
    }
}
