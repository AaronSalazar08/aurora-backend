<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FacturaProcesadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $factura;
    public $productos;

    public function __construct($factura, $productos)
    {
        $this->factura = $factura;
        $this->productos = $productos;
    }

    public function build()
    {
        return $this->subject('Factura procesada - Aurora Boutique')
                    ->markdown('emails.factura')
                    ->with([
                        'factura' => $this->factura,
                        'productos' => $this->productos,
                    ]);
    }
}
