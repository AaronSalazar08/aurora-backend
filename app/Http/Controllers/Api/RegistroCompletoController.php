<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroCompletoController extends Controller
{
    /**
     * Método completo: realiza todo el proceso (usuario, dirección, teléfono, correo y cliente)
     */
    public function registrarTodo(Request $request)
    {
        try {
            DB::beginTransaction();

            // Paso 1: Agregar usuario y recuperar su ID
            DB::statement("CALL agregar_usuario(?, ?)", [
                $request->input('nombre_usuario'),
                $request->input('clave')
            ]);
            $id_usuario = DB::table('usuario')
                ->where('nombre', $request->input('nombre_usuario'))
                ->orderByDesc('id')
                ->value('id');

            // Paso 2: Agregar dirección y recuperar su ID
            DB::statement("CALL agregar_direccion(?, ?, ?, ?, ?, ?)", [
                $request->input('id_pais'),
                $request->input('id_provincia'),
                $request->input('id_canton'),
                $request->input('id_distrito'),
                $request->input('id_barrio'),
                $request->input('detalle_especifico')
            ]);
            $id_direccion = DB::table('direccion')
                ->orderByDesc('id')
                ->value('id');

            // Paso 3: Agregar teléfono
            DB::statement("CALL agregar_telefono(?, ?)", [
                $request->input('telefono'),
                $request->input('id_tipo_telefono')
            ]);

            // Paso 4: Agregar correo
            DB::statement("CALL agregar_correoelectronico(?, ?)", [
                $request->input('correo'),
                $request->input('id_tipo_correo')
            ]);

            // Paso 5: Obtener ID de contacto (asumiendo tabla 'contacto' lo almacena)
            $id_contacto = DB::table('contacto')
                ->orderByDesc('id')
                ->value('id');

            // Paso 6: Agregar cliente
            DB::statement("CALL agregar_clientes(?, ?, ?, ?, ?, ?, ?)", [
                $request->input('identificacion'),
                $request->input('nombre_cliente'),
                $request->input('primer_apellido'),
                $request->input('segundo_apellido'),
                $id_direccion,
                $id_contacto,
                $id_usuario
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Cliente registrado exitosamente con todos sus datos.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocurrió un error durante el registro del cliente',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    // Métodos individuales para cada procedimiento almacenado

    public function registrarUsuario(Request $request)
    {
        try {
            DB::statement("CALL agregar_usuario(?, ?)", [
                $request->input('nombre_usuario'),
                $request->input('clave')
            ]);
            return response()->json(['mensaje' => 'Usuario registrado correctamente'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar usuario',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarDireccion(Request $request)
    {
        try {
            DB::statement("CALL agregar_direccion(?, ?, ?, ?, ?, ?)", [
                $request->input('id_pais'),
                $request->input('id_provincia'),
                $request->input('id_canton'),
                $request->input('id_distrito'),
                $request->input('id_barrio'),
                $request->input('detalle_especifico')
            ]);
            return response()->json(['mensaje' => 'Dirección registrada correctamente'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar dirección',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarTelefono(Request $request)
    {
        try {
            DB::statement("CALL agregar_telefono(?, ?)", [
                $request->input('telefono'),
                $request->input('id_tipo_telefono')
            ]);
            return response()->json(['mensaje' => 'Teléfono registrado correctamente'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar teléfono',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarCorreo(Request $request)
    {
        try {
            DB::statement("CALL agregar_correoelectronico(?, ?)", [
                $request->input('correo'),
                $request->input('id_tipo_correo')
            ]);
            return response()->json(['mensaje' => 'Correo registrado correctamente'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar correo',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarCliente(Request $request)
    {
        try {
            DB::statement("CALL agregar_clientes(?, ?, ?, ?, ?, ?, ?)", [
                $request->input('identificacion'),
                $request->input('nombre_cliente'),
                $request->input('primer_apellido'),
                $request->input('segundo_apellido'),
                $request->input('id_direccion'),
                $request->input('id_contacto'),
                $request->input('id_usuario')
            ]);
            return response()->json(['mensaje' => 'Cliente registrado correctamente'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar cliente',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
