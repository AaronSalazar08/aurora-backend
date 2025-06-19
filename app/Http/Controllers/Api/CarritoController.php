<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    /** Listar todos los ítems de un carrito (pedido) */
    public function index($codigo_pedido)
    {
        try {
            $items = DB::select('SELECT * FROM public.productos_en_carrito WHERE codigo_pedido = ?', [
                $codigo_pedido
            ]);
            return response()->json($items, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron listar los items del carrito.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /** Agregar un producto al carrito */
    public function agregarProducto(Request $request)
    {
        $request->validate([
            'codigo_producto' => 'required|integer|exists:productos,codigo',
            'codigo_pedido' => 'required|integer',
            'cantidad' => 'required|integer|min:1',
        ]);

        try {
            DB::statement('CALL public.agregar_producto_al_carrito(?, ?, ?)', [
                $request->codigo_producto,
                $request->codigo_pedido,
                $request->cantidad,
            ]);
            return response()->json(['mensaje' => 'Producto agregado al carrito.'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo agregar el producto.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /** Actualizar cantidad de un item en el carrito */
    public function actualizarProducto(Request $request, $id)
    {
        $request->validate([
            'nueva_cantidad' => 'required|integer|min:1',
        ]);

        try {
            DB::statement('CALL public.actualizar_producto_en_carrito(?, ?)', [
                $id,
                $request->nueva_cantidad,
            ]);
            return response()->json(['mensaje' => 'Cantidad actualizada.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar la cantidad.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /** Eliminar un item del carrito */
    public function eliminarProducto($id)
    {
        try {
            DB::statement('CALL public.eliminar_producto_del_carrito(?)', [
                $id
            ]);
            return response()->json(['mensaje' => 'Producto eliminado del carrito.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo eliminar el producto.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
