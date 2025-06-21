<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // El nombre real de tu tabla
    protected $table = 'usuario';

    // Si tu tabla no tiene created_at / updated_at
    public $timestamps = false;

    // Si tu PK no es 'id', cámbialo aquí. Pero si es 'id', déjalo.
    protected $primaryKey = 'id';

    // Para que Auth::attempt sepa usar la columna 'clave'
    public function getAuthPassword()
    {
        return $this->clave;
    }

    protected $fillable = [
        'nombre',
        'clave',
        'id_tipo',
    ];

    protected $hidden = [
        'clave',
    ];
}
