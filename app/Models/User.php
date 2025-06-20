<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Para que $user->createToken() funcione

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * INDISPENSABLE: El nombre exacto de tu tabla de usuarios en la base de datos.
     * Laravel asume 'users', pero si se llama 'usuario' o 'usuarios', debes especificarlo.
     */
    protected $table = 'usuario'; // O 'usuarios', el nombre que tenga tu tabla

    /**
     * INDISPENSABLE: Indica si el modelo debe usar las columnas 'created_at' y 'updated_at'.
     * Si tu tabla no las tiene, esto debe ser 'false' para evitar errores.
     */
    public $timestamps = false;

    /**
     * INDISPENSABLE: El nombre de la clave primaria si no es 'id'.
     */
    protected $primaryKey = 'id'; // Ajusta si tu PK se llama diferente

    /**
     * IMPORTANTE: Le dice al Auth::attempt() que tu columna de contraseña se llama 'clave',
     * no 'password' (que es el valor por defecto de Laravel).
     */
    public function getAuthPassword()
    {
        return $this->clave;
    }

    /**
     * Los atributos que se pueden asignar masivamente (opcional, pero buena práctica).
     */
    protected $fillable = [
        'nombre',
        'clave', // Importante para el registro
        'id_tipo',
    ];

    /**
     * Los atributos que deben ocultarse cuando el modelo se convierte a JSON.
     * Siempre es buena práctica ocultar la contraseña.
     */
    protected $hidden = [
        'clave',
    ];
}