<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Import correct du trait HasFactory
use Illuminate\Database\Eloquent\Model;

class Ordonnance extends Model
{
    use HasFactory;

    protected $fillable = [
        'medecin_id',
        'patient_id',
        'date',
        'details',
    ];

    // Relation avec le médecin
    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }

    // Relation avec le patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relation avec les médicaments (many-to-many)
    public function medicaments()
    {
        return $this->belongsToMany(Medicament::class, 'ordonnance_medicament')
                    ->withPivot('quantite')
                    ->withTimestamps();
    }
}
