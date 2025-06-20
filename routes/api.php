<?php


use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\DireccionController;
use App\Http\Controllers\Api\TipoContacto;
use App\Http\Controllers\Api\RegistroCompletoController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\PedidoEstadoController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\ClienteConsultaController;
use App\Http\Controllers\Api\ProductoFiltroController;
use App\Http\Controllers\Api\ResenaController;
use App\Http\Controllers\Api\MetodoPagoController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\ProductosCompradosController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::post('login', [LoginController::class, 'login']);
Route::controller(DireccionController::class)->group(function () {
    Route::get('paises', 'getPaises');
    Route::get('paises/provincias/{id_pais}', 'getProvincias');
    Route::get('paises/provincias/cantones/{id_provincia}', 'getCantones');
    Route::get('paises/provincias/cantones/distritos/{id_canton}', 'getDistritos');
    Route::get('paises/provincias/cantones/distritos/barrios/{id_distrito}', 'getBarrios');
});

Route::get('tipos-contacto', [TipoContacto::class, 'index']);


Route::post('registro-completo', [RegistroCompletoController::class, 'registrarTodo']);

Route::prefix('registro')->controller(RegistroCompletoController::class)->group(function () {
    Route::post('usuario', 'registrarUsuario');
    Route::post('direccion', 'registrarDireccion');
    Route::post('telefono', 'registrarTelefono');
    Route::post('correo', 'registrarCorreo');
});

Route::get('productos', [ProductoController::class, 'listarProductos']);
Route::get('productos/categorias', [ProductoController::class, 'listarCategorias']);
Route::get('productos/detalle/{codigo}', [ProductoController::class, 'detalleProducto']);
Route::get('pedidos', [PedidoController::class, 'listarPedidos']);

Route::get('tipos-pago', [MetodoPagoController::class, 'listarTipos']);


Route::get('productos/ropa', [ProductoFiltroController::class, 'ropa']);
Route::get('productos/accesorios', [ProductoFiltroController::class, 'accesorios']);
Route::get('productos/moda', [ProductoFiltroController::class, 'moda']);

Route::get('usuarios/personal-envios', [UsuarioController::class, 'listarPersonalEnvios']);
Route::get('resenas', [ResenaController::class, 'ListarResenas']);

Route::post('usuarios', [UsuarioController::class, 'agregarUsuario']);
/* 
|--------------------------------------------------------------------------
| Rutas privadas
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {


    Route::put('/productos/reducir-stock', [ProductoController::class, 'reducir_stock']);
    Route::post('/carrito/limpiar/{codigoPedido}', [PedidoController::class, 'limpiarCarrito']);


    // Pedidos (lectura pública)
    Route::get('estados-pedido', [PedidoEstadoController::class, 'listarEstadosAprobacion']);
    Route::get('pedidos/cliente/{cedula}', [PedidoController::class, 'pedidosPorCliente']);
    Route::get('pedidos/pendientes', [PedidoController::class, 'pendientes']);
    Route::get('pedidos/enproceso', [PedidoController::class, 'enProceso']);
    Route::get('pedidos/enviados', [PedidoController::class, 'enviados']);
    Route::get('pedidos/entregados', [PedidoController::class, 'entregados']);
    Route::get('pedidos/{codigo}', [PedidoController::class, 'buscarPedido']);

    // Facturas (lectura pública)

    Route::get('facturas/cliente/{id}', [FacturaController::class, 'porCliente']);
    Route::get('facturas/{id}', [FacturaController::class, 'detalle']);

    // Consultas de clientes
    Route::get('clientes', [ClienteConsultaController::class, 'todos']);
    Route::get('clientes/{cedula}', [ClienteConsultaController::class, 'porIdentificacion']);
    Route::get('clientes/usuario/{id}', [ClienteConsultaController::class, 'porUsuario']);

    // Filtros de productos


    // Reseñas y métodos de pago (públicos)
    Route::get('buscar-resena/{id}', [ResenaController::class, 'buscarResena']);


    /*
    |--------------------------------------------------------------------------
    | Rutas protegidas (requieren token Sanctum)
    |--------------------------------------------------------------------------
    */

    // Perfil
    Route::get('user', [LoginController::class, 'perfil']);

    // Usuarios
    Route::get('usuarios', [UsuarioController::class, 'index']);


    // Personal de envíos

    Route::post('usuarios/personal-envios', [UsuarioController::class, 'agregarPersonalEnvios']);
    Route::delete('usuarios/personal-envios/{id}', [UsuarioController::class, 'eliminarPersonalEnvios']);

    // CRUD Productos
    Route::post('productos', [ProductoController::class, 'agregarProducto']);
    Route::put('productos/{codigo}', [ProductoController::class, 'actualizarProducto']);
    Route::delete('productos/{codigo}', [ProductoController::class, 'eliminarProducto']);
    Route::get('productos/{codigo}', [ProductoController::class, 'buscarProducto']);


    // CRUD Pedidos
    Route::post('/pedidos/auto', [PedidoController::class, 'agregarPedidoAuto']);
    Route::put('pedidos/{codigo}', [PedidoController::class, 'actualizarPedido']);
    Route::delete('pedidos/{codigo}', [PedidoController::class, 'eliminarPedido']);
    Route::get('/mis-pedidos', [PedidoController::class, 'misPedidos']);

    // Clientes (update/delete)
    Route::put('clientes/{identificacion}', [ClienteController::class, 'actualizarCliente']);
    Route::delete('clientes/{identificacion}', [ClienteController::class, 'eliminarCliente']);
    Route::get('clientes/usuario/{idUsuario}', [ClienteController::class, 'porUsuario']);


    // Estados de pedido
    Route::put('pedidos/admin/estado', [PedidoEstadoController::class, 'actualizarEstadoAdmin']);
    Route::put('pedidos/envios/estado', [PedidoEstadoController::class, 'actualizarEstadoEnvios']);

    Route::get('mis-facturas', [FacturaController::class, 'misFacturas']);
    Route::get('facturas', [FacturaController::class, 'listarFacturas']);
    Route::post('facturas/procesar/{codigo}', [FacturaController::class, 'procesarFactura']);
    Route::put('facturas/{id}', [FacturaController::class, 'actualizar']);
    Route::delete('facturas/{id}', [FacturaController::class, 'eliminar']);
    Route::get('facturas/{id}', [FacturaController::class, 'verDetalle']);

    // Reseñas (CRUD)

    Route::get('resenas/{id}', [ResenaController::class, 'verdetalleResena']);
    Route::post('resenas', [ResenaController::class, 'agregarResena']);
    Route::delete('resenas/{id}', [ResenaController::class, 'eliminarResena']);
    Route::get('pedidos-para-resena', [PedidoController::class, 'pedidosParaResena']);

    // Carrito
    Route::prefix('carrito')->group(function () {
        Route::get('{codigo_pedido}', [CarritoController::class, 'index']);
        Route::post('agregar', [CarritoController::class, 'agregarProducto']);
        Route::put('{id}', [CarritoController::class, 'actualizarProducto']);
        Route::delete('{id}', [CarritoController::class, 'eliminarProducto']);
    });

    Route::post('productos-comprados', [ProductosCompradosController::class, 'agregasProductosComprados']);
    Route::put('pedidos/{codigo}/metodo-pago', [PedidoController::class, 'actualizarMetodoPago']);


});