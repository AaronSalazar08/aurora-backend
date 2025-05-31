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
        // 1. Validación de los datos de entrada
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
            // 2. Consultar si el usuario existe usando la función de la base de datos
            // Es buena práctica usar un alias para la columna para que sea más fácil acceder a ella.
            $resultado_existe = DB::select('SELECT usuario_existe(?) AS usuario_ya_existe', [$request->nombre]);

            // 3. Acceder al valor booleano real del resultado
            // DB::select devuelve un array de objetos (o arrays asociativos).
            // Queremos el primer (y único) elemento del array, y luego la propiedad 'usuario_ya_existe'.
            // Si por alguna razón no se encuentra, default a false.
            $usuarioExiste = $resultado_existe[0]->usuario_ya_existe ?? false;

            // 4. Lógica condicional basada en si el usuario existe
            if ($usuarioExiste) { // Ahora esto compara el valor booleano 'true' o 'false'
                return response()->json(['mensaje' => 'El usuario ya existe'], 409);
            } else {
                // 5. Si el usuario no existe, se procede a insertarlo
                DB::statement('CALL agregar_usuario(?, ?)', [
                    $request->nombre,
                    $request->clave
                ]);

                return response()->json(['mensaje' => 'Usuario insertado correctamente'], 201);
            }

        } catch (\Exception $e) {
            // 6. Manejo de errores en caso de problemas con la base de datos
            return response()->json(['error' => 'Error al insertar el usuario: ' . $e->getMessage()], 500);
        }
    }

    // El método index está bien como está si la función obtener_usuarios() devuelve lo esperado.
    public function index()
    {
        $usuarios = DB::select('SELECT * FROM obtener_usuarios();');
        return response()->json($usuarios);
    }
}