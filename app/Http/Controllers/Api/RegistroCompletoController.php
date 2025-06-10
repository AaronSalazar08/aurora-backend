<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroCompletoController extends Controller
{
    /**
     * Registra en una sola transacción:
     *   1) Usuario
     *   2) Dirección
     *   3) Teléfono
     *   4) Correo electrónico
     *   5) Cliente
     *   6) Contacto (vincula cliente con teléfono y correo)
     */
    public function registrarTodo(Request $request)
    {
        // 1) Validación de los campos requeridos
        $request->validate([
            'nombre_usuario' => 'required|string|max:255',
            'clave' => 'required|string|max:255',
            'id_pais' => 'required|integer',
            'id_provincia' => 'required|integer',
            'id_canton' => 'required|integer',
            'id_distrito' => 'required|integer',
            'id_barrio' => 'required|integer',
            'telefono' => 'required|integer',
            'id_tipo_telefono' => 'required|integer',
            'correo' => 'required|email|max:255',
            'id_tipo_correo' => 'required|integer',
            'identificacion' => 'required|integer',
            'nombre_cliente' => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            //
            // 2) Agregar usuario
            //
            DB::statement("CALL agregar_usuario(?, ?)", [
                $request->input('nombre_usuario'),
                $request->input('clave'),
            ]);
            // Obtener el ID del usuario recién creado
            $id_usuario = DB::table('usuario')
                ->where('nombre', $request->input('nombre_usuario'))
                ->orderByDesc('id')
                ->value('id');

            //
            // 3) Agregar dirección
            //
            DB::statement("CALL agregar_direccion(?, ?, ?, ?, ?)", [
    $request->input('id_pais'),
    $request->input('id_provincia'),
    $request->input('id_canton'),
    $request->input('id_distrito'),
    $request->input('id_barrio'),
    
]);

            // Obtener el ID de la dirección recién creada
            $id_direccion = DB::table('direccion')
                ->orderByDesc('id')
                ->value('id');

            //
            // 4) Agregar teléfono
            //
            DB::statement("CALL agregar_telefono(?, ?)", [
                $request->input('telefono'),
                $request->input('id_tipo_telefono'),
            ]);
            // Obtener el ID del teléfono recién creado
            $id_telefono = DB::table('telefono')
                ->orderByDesc('id')
                ->value('id');

            //
            // 5) Agregar correo electrónico
            //
            DB::statement("CALL agregar_correoelectronico(?, ?)", [
                $request->input('correo'),
                $request->input('id_tipo_correo'),
            ]);
            // Obtener el ID del correo recién creado
            $id_correo = DB::table('correo_electronico')
                ->orderByDesc('id')
                ->value('id');

            //
            // 6) Agregar cliente con id_contacto = NULL
            //
            DB::table('clientes')->insert([
                'identificacion' => $request->input('identificacion'),
                'nombre' => $request->input('nombre_cliente'),
                'primer_apellido' => $request->input('primer_apellido'),
                'segundo_apellido' => $request->input('segundo_apellido'),
                'id_direccion' => $id_direccion,
                'id_contacto' => null,
                'id_usuario' => $id_usuario,
            ]);

            //
            // 7) Agregar registro en 'contacto' uniendo al cliente con teléfono y correo
            //
            DB::table('contacto')->insert([
                'identificacion_cliente' => $request->input('identificacion'),
                'id_telefono' => $id_telefono,
                'id_correo_electronico' => $id_correo,
            ]);
            // Obtener el ID del contacto recién creado
            $id_contacto = DB::table('contacto')
                ->orderByDesc('id')
                ->value('id');

            //
            // 8) Actualizar el registro en 'clientes' para asignar id_contacto
            //
            DB::table('clientes')
                ->where('identificacion', $request->input('identificacion'))
                ->update(['id_contacto' => $id_contacto]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Cliente registrado exitosamente con todos sus datos.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocurrió un error durante el registro del cliente.',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }
}
