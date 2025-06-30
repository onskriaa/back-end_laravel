<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    
    protected $fillable = ['name', 'description', 'prix', 'photo'];
    
    public function ordonnances()
{
    return $this->belongsToMany(Ordonnance::class, 'ordonnance_medicament')
                ->withPivot('quantite')
                ->withTimestamps();
}

}
