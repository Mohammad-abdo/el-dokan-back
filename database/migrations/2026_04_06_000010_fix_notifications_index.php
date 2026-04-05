<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the old read_at index if it exists (may have been created before the fix)
            if ($this->indexExists('notifications', 'notifications_user_id_read_index')) {
                $table->dropIndex('notifications_user_id_read_index');
            }

            // Create the correct index on is_read (the actual column name)
            if (!$this->indexExists('notifications', 'notifications_user_id_is_read_index')) {
                $table->index(['user_id', 'is_read'], 'notifications_user_id_is_read_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if ($this->indexExists('notifications', 'notifications_user_id_is_read_index')) {
                $table->dropIndex('notifications_user_id_is_read_index');
            }
        });
    }

    protected function indexExists(string $table, string $index): bool
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                $indexes = DB::select("SHOW INDEX FROM `{$table}`");
                foreach ($indexes as $indexInfo) {
                    if ($indexInfo->Key_name === $index) {
                        return true;
                    }
                }
            }
        } catch (\Exception) {
            return false;
        }
        return false;
    }
};
