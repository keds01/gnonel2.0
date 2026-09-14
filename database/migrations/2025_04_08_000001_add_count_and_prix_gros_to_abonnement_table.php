<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountAndPrixGrosToAbonnementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('abonnement', function (Blueprint $table) {
            $table->integer('count')->nullable()->default(1)->after('prix');
            $table->double('prix_gros')->nullable()->after('count');
            $table->tinyInteger('is_pack_fixe')->nullable()->default(0)->after('prix_gros');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('abonnement', function (Blueprint $table) {
            $table->dropColumn(['count', 'prix_gros', 'is_pack_fixe']);
        });
    }
}
