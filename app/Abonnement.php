<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
	public $table = 'abonnement';

	protected $fillable = [
		'libelle',
		'prix',
		'count',
		'prix_gros',
		'is_pack_fixe',
		'description',
		'monnaie',
		'categorie',
		'prix_exo',
		'nbjours',
		'choixaut',
		'choixop',
		'is_international',
		'mon_port',
		'oper_local',
		'oper_international',
		'base_four'
	];

	public function category()
	{
		return $this->belongsTo(Categorieabonnement::class, 'categorie');
	}
}
