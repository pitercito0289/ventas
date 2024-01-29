<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('tcpdf/tcpdf.php');
include_once "funciones.php";

// Inicia la sesión
session_start();

$productos = isset($_SESSION['lista']) ? $_SESSION['lista'] : [];
$idUsuario = isset($_SESSION['idUsuario']) ? $_SESSION['idUsuario'] : null;
$total = calcularTotalLista($productos);
$idCliente = isset($_SESSION['clienteVenta']) ? $_SESSION['clienteVenta'] : null;

//$idCliente = $_SESSION['clienteVenta'];

// Registra la venta y obtiene el ID de la venta
$resultado = registrarVenta($productos, $idUsuario, $idCliente, $total);

if (!$resultado) {
    echo "Error al registrar la venta";
    return;
}
// Obtén el nombre del usuario que realizó la venta
$nombreUsuario = obtenerNombreUsuario($idUsuario);

// Crea un nuevo objeto TCPDF
$pdf = new TCPDF();

// Configura el PDF
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

// Agrega contenido al PDF
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Recibo de Venta', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Fecha: ' . date('Y-m-d H:i:s'), 0, 1, 'L');
$pdf->Cell(0, 10, 'Cliente: ' . ($idCliente ? obtenerNombreCliente($idCliente) : 'Mostrador'), 0, 1, 'L');
// Agrega el nombre del usuario al recibo si está definido
if (isset($nombreUsuario)) {
    $pdf->Cell(0, 10, 'Atendido por: ' . $nombreUsuario, 0, 1, 'L');
}

$pdf->Cell(0, 10, '', 0, 1); // Salto de línea

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 10, 'Código', 1, 0, 'C');
$pdf->Cell(70, 10, 'Producto', 1, 0, 'C');
$pdf->Cell(30, 10, 'Precio', 1, 0, 'C');
$pdf->Cell(20, 10, 'Cantidad', 1, 0, 'C');
$pdf->Cell(40, 10, 'Subtotal', 1, 1, 'C');

foreach ($productos as $producto) {
    $pdf->Cell(30, 10, $producto->codigo, 1, 0, 'C');
    $pdf->Cell(70, 10, $producto->nombre, 1, 0, 'C');
    $pdf->Cell(30, 10, '$' . $producto->venta, 1, 0, 'C');
    $pdf->Cell(20, 10, $producto->cantidad, 1, 0, 'C');
    $pdf->Cell(40, 10, '$' . number_format($producto->cantidad * $producto->venta, 2), 1, 1, 'C');
}

$pdf->Cell(0, 10, '', 0, 1); // Salto de línea

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(170, 10, 'Total:', 1, 0, 'C');
$pdf->Cell(40, 10, '$' . number_format($total, 2), 1, 0, 'C');

// Guarda el PDF o lo envía al navegador
$pdf->Output('recibo_ventas.pdf', 'I');

// Limpia la lista de productos y el cliente en sesión
$_SESSION['lista'] = [];
$_SESSION['clienteVenta'] = "";

echo "<script type='text/javascript'>alert('Venta realizada con éxito');</script>";

// Abre una nueva ventana para la descarga del PDF
echo "<script type='text/javascript'>window.open('data:application/pdf;base64,".base64_encode($pdfOutput)."', '_blank');</script>";
?>
?>
