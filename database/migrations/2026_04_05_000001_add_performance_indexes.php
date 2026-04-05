<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_status_index')) {
                $table->index('status', 'users_status_index');
            }
            if (!$this->indexExists('users', 'users_created_at_index')) {
                $table->index('created_at', 'users_created_at_index');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('orders', 'orders_user_id_index')) {
                    $table->index(['user_id', 'status'], 'orders_user_id_status_index');
                }
                if (!$this->indexExists('orders', 'orders_shop_id_index')) {
                    $table->index(['shop_id', 'status'], 'orders_shop_id_status_index');
                }
                if (!$this->indexExists('orders', 'orders_payment_status_index')) {
                    $table->index('payment_status', 'orders_payment_status_index');
                }
                if (!$this->indexExists('orders', 'orders_created_at_index')) {
                    $table->index('created_at', 'orders_created_at_index');
                }
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('products', 'products_shop_id_index')) {
                    $table->index('shop_id', 'products_shop_id_index');
                }
                if (!$this->indexExists('products', 'products_category_id_index')) {
                    $table->index('category_id', 'products_category_id_index');
                }
                if (!$this->indexExists('products', 'products_status_index')) {
                    $table->index('status', 'products_status_index');
                }
                if (!$this->indexExists('products', 'products_created_at_index')) {
                    $table->index('created_at', 'products_created_at_index');
                }
            }
        });

        Schema::table('shops', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('shops', 'shops_status_index')) {
                    $table->index('status', 'shops_status_index');
                }
                if (!$this->indexExists('shops', 'shops_vendor_status_index')) {
                    $table->index('vendor_status', 'shops_vendor_status_index');
                }
            }
        });

        Schema::table('doctors', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('doctors', 'doctors_status_index')) {
                    $table->index('status', 'doctors_status_index');
                }
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('drivers', 'drivers_status_index')) {
                    $table->index('status', 'drivers_status_index');
                }
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('notifications', 'notifications_user_id_read_index')) {
                    $table->index(['user_id', 'read_at'], 'notifications_user_id_read_index');
                }
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('messages', 'messages_conversation_index')) {
                    $table->index(['sender_id', 'receiver_id'], 'messages_conversation_index');
                }
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('carts', 'carts_user_product_index')) {
                    $table->index(['user_id', 'product_id'], 'carts_user_product_index');
                }
            }
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            if (!DB::getDriverName() === 'sqlite') {
                if (!$this->indexExists('financial_transactions', 'fin_trans_user_type_index')) {
                    $table->index(['user_id', 'type'], 'fin_trans_user_type_index');
                }
                if (!$this->indexExists('financial_transactions', 'fin_trans_created_at_index')) {
                    $table->index('created_at', 'fin_trans_created_at_index');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_index');
            $table->dropIndex('users_created_at_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_status_index');
            $table->dropIndex('orders_shop_id_status_index');
            $table->dropIndex('orders_payment_status_index');
            $table->dropIndex('orders_created_at_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_shop_id_index');
            $table->dropIndex('products_category_id_index');
            $table->dropIndex('products_status_index');
            $table->dropIndex('products_created_at_index');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex('shops_status_index');
            $table->dropIndex('shops_vendor_status_index');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex('doctors_status_index');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropIndex('drivers_status_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_id_read_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_index');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('carts_user_product_index');
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropIndex('fin_trans_user_type_index');
            $table->dropIndex('fin_trans_created_at_index');
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
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }
};
