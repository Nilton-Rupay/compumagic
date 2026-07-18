<?php
require_once '../../includes/funciones.php';
verificar_sesion();

$id = (int) ($_GET['id'] ?? 0);
$venta = obtener_venta_por_id($conexion, $id);

if (!$venta) {
    header('Location: historial.php');
    exit;
}

$detalle = obtener_detalle_venta($conexion, $id);

$formas_pago = [
    'efectivo' => 'Efectivo',
    'tarjeta' => 'Tarjeta',
    'transferencia' => 'Transferencia',
    'yape' => 'Yape',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante <?php echo htmlspecialchars($venta['codigo_venta']); ?> - Compu Magic</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="no-print">
    <?php include '../../includes/sidebar.php'; ?>
</div>

<div class="main-content">
    <div class="no-print">
        <?php $titulo = 'Comprobante de Venta'; include '../../includes/topbar.php'; ?>
    </div>

    <div class="comprobante">
        <div class="comprobante-header">
            <div>
                <h2><i class="fas fa-laptop-code"></i> Compu Magic</h2>
                <p>Sistema de Venta de Computadoras</p>
            </div>
            <div class="comprobante-codigo">
                <strong>N° <?php echo htmlspecialchars($venta['codigo_venta']); ?></strong>
                <p><?php echo formato_fecha($venta['fecha_venta']); ?></p>
            </div>
        </div>

        <div class="comprobante-datos">
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre']); ?></p>
            <p><strong>Documento:</strong> <?php echo htmlspecialchars($venta['cliente_documento'] ?: '-'); ?></p>
            <p><strong>Forma de pago:</strong> <?php echo $formas_pago[$venta['forma_pago']] ?? $venta['forma_pago']; ?></p>
            <p><strong>Atendido por:</strong> <?php echo htmlspecialchars($venta['admin_nombre']); ?></p>
        </div>

        <table>
            <thead>
                <tr><th>Código</th><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php while ($item = $detalle->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['producto_codigo']); ?></td>
                    <td><?php echo htmlspecialchars($item['producto_nombre']); ?></td>
                    <td><?php echo $item['cantidad']; ?></td>
                    <td><?php echo formato_moneda($item['precio_unitario']); ?></td>
                    <td><?php echo formato_moneda($item['subtotal']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="comprobante-total">
            <strong>TOTAL: <?php echo formato_moneda($venta['total']); ?></strong>
        </div>
        <div class="comprobante-letras">
            Son: <?php echo numero_a_letras($venta['total']); ?>
        </div>

        <div class="form-actions no-print">
            <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <a href="historial.php" class="btn btn-secondary">Volver al Historial</a>
        </div>
    </div>
</div>
</body>
</html>
