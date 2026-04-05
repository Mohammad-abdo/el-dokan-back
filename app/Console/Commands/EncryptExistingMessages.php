<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class EncryptExistingMessages extends Command
{
    protected $signature = 'messages:encrypt-existing
                            {--dry-run : Show count without making changes}';

    protected $description = 'One-time command: encrypt plain-text content/voice_url in the messages table';

    public function handle(): int
    {
        $messages = DB::table('messages')
            ->whereNotNull('content')
            ->orWhereNotNull('voice_url')
            ->get(['id', 'content', 'voice_url']);

        $total = $messages->count();
        $this->info("Found {$total} messages to process.");

        if ($this->option('dry-run')) {
            $this->line('Dry-run mode — no changes made.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($messages as $message) {
            $update = [];

            if ($message->content !== null) {
                try {
                    // Try to decrypt to check if already encrypted
                    Crypt::decryptString($message->content);
                } catch (\Exception) {
                    $update['content'] = Crypt::encryptString($message->content);
                }
            }

            if ($message->voice_url !== null) {
                try {
                    Crypt::decryptString($message->voice_url);
                } catch (\Exception) {
                    $update['voice_url'] = Crypt::encryptString($message->voice_url);
                }
            }

            if (!empty($update)) {
                DB::table('messages')->where('id', $message->id)->update($update);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done. All existing messages have been encrypted.');

        return self::SUCCESS;
    }
}
