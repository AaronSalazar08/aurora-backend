{{-- Código CORREGIDO usando sintaxis de array --}}
{{-- El nombre de las claves ('factura', 'id', 'cliente', 'nombre')
depende de la estructura del JSON que genera tu función get_factura_detalle() --}}

<h1>Factura #{{ $detalle['factura']['id'] }}</h1>
<p>Hola {{ $detalle['cliente']['nombre'] }},</p>

<p>Gracias por tu compra. Aquí está el resumen de tu pedido:</p>
<ul>
    @foreach ($productos as $producto)
        <li>{{ $producto['cantidad'] }}x {{ $producto['nombre'] }} - ${{ number_format($producto['precio_total'], 2) }}</li>
    @endforeach
</ul>

<p><strong>Subtotal:</strong> ${{ number_format($detalle['factura']['monto'], 2) }}</p>
<p><strong>Impuesto (13%):</strong> ${{ number_format($detalle['factura']['impuesto'], 2) }}</p>
@if (isset($detalle['factura']['descuento']) && $detalle['factura']['descuento'] > 0)
    <p><strong>Descuento:</strong> -${{ number_format($detalle['factura']['descuento'], 2) }}</p>
@endif
<p><strong>Total Pagado:</strong> ${{ number_format($detalle['factura']['monto_final'], 2) }}</p>

<p>Saludos,</p>
<p>El equipo de Aurora Boutique</p>