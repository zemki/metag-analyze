<?php

use Illuminate\Database\Seeder;

class CombinedDataSeeder extends Seeder
{
    /**
     * Run the database seeds combining realistic and MART projects.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Running Combined Data Seeder...');

        // First, run the realistic data seeder
        $this->command->info('Creating realistic standard projects...');
        $this->call(RealisticDataSeeder::class);

        // Then, run the MART project seeder
        $this->command->info('Creating MART/ESM projects...');

        // Ask about ESM data for MART projects
        $includeEsmData = $this->command->confirm(
            'Do you want to include ESM (Experience Sampling Method) data in MART projects?',
            true
        );

        // Temporarily mock the command for MartProjectSeeder
        $originalCommand = $this->command;
        $mockCommand = new class($originalCommand, $includeEsmData) extends \Illuminate\Console\Command
        {
            private $originalCommand;

            private $includeEsmData;

            public function __construct($originalCommand, $includeEsmData)
            {
                parent::__construct();
                $this->originalCommand = $originalCommand;
                $this->includeEsmData = $includeEsmData;
            }

            public function info($string)
            {
                return $this->originalCommand->info($string);
            }

            public function confirm($question, $default = false)
            {
                // Return our predetermined answer for the ESM data question
                if (strpos($question, 'ESM') !== false) {
                    return $this->includeEsmData;
                }

                return $this->originalCommand->confirm($question, $default);
            }

            public function __call($method, $parameters)
            {
                return call_user_func_array([$this->originalCommand, $method], $parameters);
            }
        };

        // Run MART seeder with our mock command
        $martSeeder = new MartProjectSeeder;
        $martSeeder->setCommand($mockCommand);
        $martSeeder->run();

        // Summary
        $this->command->info('');
        $this->command->info('Combined seeding completed!');
        $this->command->info('Created:');
        $this->command->info('- Standard projects with realistic data');
        $this->command->info('- MART/ESM projects' . ($includeEsmData ? ' with participant data' : ' (structure only)'));
        $this->command->info('');
        $this->command->info('You now have a complete dataset with both traditional and mobile ESM projects!');
    }
}
