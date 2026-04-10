<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role')->index();
            }

            if (! Schema::hasColumn('users', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('is_active');
            }

            if (! Schema::hasColumn('users', 'deactivation_reason')) {
                $table->string('deactivation_reason', 255)->nullable()->after('deactivated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('users', 'deactivation_reason')) {
                $columns[] = 'deactivation_reason';
            }

            if (Schema::hasColumn('users', 'deactivated_at')) {
                $columns[] = 'deactivated_at';
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $columns[] = 'is_active';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
