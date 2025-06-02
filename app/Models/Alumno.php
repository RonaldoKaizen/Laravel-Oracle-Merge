<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $connection = 'oracle';
    protected $table      = 'alumno';
    protected $primaryKey = 'id_alumno';
    public $incrementing  = false; 
    public $timestamps    = false;

    protected $fillable = [
        'id_alumno',
        'nombre',
        'apellido',
        'email',
        'fecha_nac',
        'modalidad',
    ];
}
