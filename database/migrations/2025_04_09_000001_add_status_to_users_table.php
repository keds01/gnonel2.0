<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status')) {
                $table->tinyInteger('status')->default(0)->after('type_user');
            }
        });

        // Mettre à jour les utilisateurs existants : actifs seulement s'ils ont un abonnement en cours
        $users = DB::table('users')->where('type_user', '!=', 0)->get();
        foreach ($users as $user) {
            $hasActiveSubscription = DB::table('souscriptions')
                ->where('iduser', $user->id)
                ->where('date_fin', '>=', date('Y-m-d'))
                ->exists();
            
            DB::table('users')
                ->where('id', $user->id)
                ->update(['status' => $hasActiveSubscription ? 1 : 0]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
