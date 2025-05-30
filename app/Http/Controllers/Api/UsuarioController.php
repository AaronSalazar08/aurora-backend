<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function AgregueUnUsuario(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:30',
            'clave' => 'required|string|min:8',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 30 caracteres.',
            'clave.required' => 'La clave es obligatoria.',
            'clave.string' => 'La clave debe ser una cadena de texto.',
            'clave.min' => 'La clave debe tener al menos 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {

            $existe = DB::select('SELECT * FROM usuario_existe(?)', [$request->nombre]);
            if ($existe == true) {
                return response()->json(['mensaje' => 'El usuario ya existe'], 409);
            }

            DB::statement('CALL agregar_usuario(?, ?)', [
                $request->nombre,
                $request->clave

            ]);

            return response()->json(['mensaje' => 'Usuario insertado correctamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al insertar el usuario: ' . $e->getMessage()], 500);
        }
    }



    public function index()
    {
        $usuarios = DB::select('SELECT * FROM obtener_usuarios();');


        return response()->json($usuarios);
    }
}
