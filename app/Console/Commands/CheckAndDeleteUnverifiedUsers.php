<?php

namespace App\Console\Commands;

use App\Mail\UnverifiedUsersReport;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CheckAndDeleteUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:check-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks and deletes users who haven\'t verified their email within 48 hours.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if ($this->confirm('Do you want to check and delete unverified users from last 48h? No will check the ones in the whole db')) {
            $timeLimit = Carbon::now()->subHours(48);

            $unverifiedUsers = User::whereNull('email_verified_at')
                ->where('created_at', '<', $timeLimit)
                ->get(['email', 'created_at']);

        } else {
            $unverifiedUsers = User::whereNull('email_verified_at')->get(['email', 'created_at']);
        }

        $filePath = $this->generateCsv($unverifiedUsers);

        if ($this->confirm('There are ' . $unverifiedUsers->count() . ' users without verified email. Do you want to delete them?', false)) {
            $this->warn('Deleting the users...');
            $this->deleteUsers($unverifiedUsers);
        }

        if ($this->confirm('You want to receive the csv file?', false)) {
            // ADMINS is a comma-separated list, so an unset value explodes to
            // [''] rather than to an empty array - which would reach Mail::to()
            // as a single empty recipient.
            $recipients = array_values(array_filter(
                array_map('trim', (array) config('utilities.adminemails')),
                fn ($address) => filter_var($address, FILTER_VALIDATE_EMAIL) !== false
            ));

            if (empty($recipients)) {
                $this->error('No valid administrator address configured - set ADMINS in .env.');
                $this->line('The report was not sent. The CSV was at: ' . $filePath);
            } else {
                $this->info('Sending the report to ' . implode(', ', $recipients) . '...');

                try {
                    Mail::to($recipients)->send(new UnverifiedUsersReport($filePath));
                    $this->info('Report sent.');
                } catch (Throwable $e) {
                    Log::error('Failed to send the unverified-users report', [
                        'recipients' => $recipients,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error('Sending the report failed: ' . $e->getMessage());
                    $this->line('The users were still processed as chosen above.');
                }
            }
        }

        // remove the csv file
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    private function generateCsv($unverifiedUsers): string
    {
        $headers = ['Email', 'Created At'];
        $filename = 'unverified_users_' . date('Y-m-d_His') . '.csv';
        $filePath = storage_path('app/' . $filename);

        $file = fopen($filePath, 'w');
        fputcsv($file, $headers);

        foreach ($unverifiedUsers as $user) {
            fputcsv($file, [$user->email, $user->created_at]);
        }

        fclose($file);

        return $filePath;
    }

    private function deleteUsers($unverifiedUsers): void
    {
        foreach ($unverifiedUsers as $userEmail) {

            $user = User::where('email', $userEmail->email)->first();
            if ($user->profile) {
                $user->profile->delete();
            }
            $user->forceDelete();
        }
    }
}
