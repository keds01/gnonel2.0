<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertirAutorite extends Command
{
    protected $signature = 'user:convertir-autorite {email}';
    protected $description = 'Convertir un utilisateur en autorité contractante';

    public function handle()
    {
        $email = $this->argument('email');
        
        // Chercher l'utilisateur
        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            $this->error('Utilisateur non trouvé: ' . $email);
            return 1;
        }
        
        $this->info('Utilisateur trouvé: ' . $user->name . ' ' . ($user->prenom ?? '') . ' (ID: ' . $user->id . ')');
        $this->info('Type actuel: ' . $user->type_user);
        
        // Créer une autorité contractante si n'existe pas
        $autorite = DB::table('autoritecontractantes')->where('id_user', $user->id)->first();
        if (!$autorite) {
            $pays_id = DB::table('pays')->first()->id ?? 1;
            $autorite_id = DB::table('autoritecontractantes')->insertGetId([
                'raison_social' => $user->name . ' ' . ($user->prenom ?? ''),
                'id_pays' => $pays_id,
                'gnonelid' => 'AC' . time(),
                'id_user' => $user->id,
                'created_at' => NOW(),
                'updated_at' => NOW(),
            ]);
            $this->info('Nouvelle autorité contractante créée (ID: ' . $autorite_id . ')');
        } else {
            $autorite_id = $autorite->id;
            $this->info('Autorité contractante existante (ID: ' . $autorite_id . ')');
        }
        
        // Mettre à jour l'utilisateur
        DB::table('users')->where('id', $user->id)->update([
            'type_user' => 5,
            'ratache_operateur' => null,
            'ratache_autorite' => $autorite_id,
        ]);
        
        $this->info('Utilisateur converti en autorité contractante avec succès !');
        $this->info('Email: ' . $email);
        $this->info('Nouveau type: 5 (autorité contractante)');
        $this->info('Autorité associée: ' . $autorite_id);
        
        return 0;
    }
}
