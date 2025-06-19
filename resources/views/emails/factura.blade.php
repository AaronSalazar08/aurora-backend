@component('mail::message')
# Detalle de Factura

**Factura ID:** {{ $factura->id }}  
**Cliente:** {{ $factura->cliente_nombre }}  
**Fecha:** {{ $factura->fecha }}

@component('mail::table')
| Producto | Cantidad | Precio |
|----------|----------|--------|
@foreach ($productos as $prod)
| {{ $prod->nombre }} | {{ $prod->cantidad }} | ₡{{ number_format((float) $prod->precio, 2) }} |
@endforeach
@endcomponent

**Subtotal:** ₡{{ number_format((float) $factura->monto, 2) }}  
**Impuesto:** ₡{{ number_format((float) $factura->impuesto, 2) }}  
**Descuento:** ₡{{ number_format((float) $factura->descuento, 2) }}  
**Total:** ₡{{ number_format((float) $factura->monto_final, 2) }}

Gracias por su compra.<br>
Aurora Boutique
@endcomponent
