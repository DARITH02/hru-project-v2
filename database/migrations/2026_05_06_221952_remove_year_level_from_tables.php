    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'year_level')) {
                $table->dropColumn('year_level');
            }
        });
        Schema::table('class_groups', function (Blueprint $table) {
            if (Schema::hasColumn('class_groups', 'year_level')) {
                $table->dropColumn('year_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->integer('year_level')->default(1);
        });
        Schema::table('class_groups', function (Blueprint $table) {
            $table->integer('year_level')->nullable();
        });
    }
};
