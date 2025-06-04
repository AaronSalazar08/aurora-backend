<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoContacto extends Controller
{
    /**
     * Devuelve el listado de todos los tipos de contacto (id y descripción).
     * Utiliza la función PL/pgSQL obtener_tipos_contacto().
     */
    public function index()
    {
        // Ejecutamos la función en PostgreSQL
        $tipos = DB::select('SELECT * FROM obtener_tipos_contacto()');

        // Retornamos el resultado como JSON
        return response()->json($tipos);
    }
}
