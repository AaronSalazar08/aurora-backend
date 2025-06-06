<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Registra un producto usando el procedimiento almacenado `agregar_productos`
     */
    public function agregarProducto(Request $request)
    {
        // Validación básica
        $request->validate([
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0',
            'talla' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'cantidad_stock' => 'required|integer|min:0',
            'id_categoria' => 'required|integer|exists:categoria_productos,id'
        ]);

        try {
           DB::statement("CALL agregar_productos(?, ?, ?, ?, ?, ?, ?, ?)", [
    $request->input('codigo'),
    $request->input('nombre'),
    $request->input('descripcion'),
    $request->input('precio'),
    $request->input('talla'),
    $request->input('color'),
    $request->input('cantidad_stock'),
    $request->input('id_categoria')
]);


            return response()->json([
                'mensaje' => 'Producto agregado correctamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo registrar el producto.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
