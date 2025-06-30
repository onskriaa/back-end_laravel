<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    
    protected $fillable = ['user_id', 'nom', 'prenom', 'date_naissance', 'adresse'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ordonnances()
{
    return $this->hasMany(Ordonnance::class);
}

}
