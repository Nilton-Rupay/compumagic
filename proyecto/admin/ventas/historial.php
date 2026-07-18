<?php
require_once '../../includes/funciones.php';
verificar_sesion();

$ventas = obtener_ventas($conexion);

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
    <title>Historial de Ventas - Compu Magic</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../../includes/sidebar.php'; ?>

<div class="main-content">
    <?php $titulo = 'Historial de Ventas'; include '../../includes/topbar.php'; ?>

    <div class="table-container">
        <div class="table-header">
            <h2>Ventas Registradas</h2>
            <a href="nueva.php" class="btn btn-success">+ Nueva Venta</a>
        </div>
        <input type="text" id="buscar-venta" class="input-buscar" onkeyup="buscarTabla('buscar-venta','tabla-ventas')" placeholder="Buscar por código o cliente...">
        <table id="tabla-ventas">
            <thead>
                <tr><th>Código</th><th>Cliente</th><th>Total</th><th>Forma de Pago</th><th>Vendedor</th><th>Fecha</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if ($ventas->num_rows == 0): ?>
                    <tr><td colspan="7">Aún no se han registrado ventas.</td></tr>
                <?php else: ?>
                    <?php while ($row = $ventas->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['codigo_venta']); ?></td>
                        <td><?php echo htmlspecialchars($row['cliente_nombre']); ?></td>
                        <td><?php echo formato_moneda($row['total']); ?></td>
                        <td><span class="badge badge-success"><?php echo $formas_pago[$row['forma_pago']] ?? $row['forma_pago']; ?></span></td>
                        <td><?php echo htmlspecialchars($row['admin_nombre']); ?></td>
                        <td><?php echo formato_fecha($row['fecha_venta']); ?></td>
                        <td><a href="detalle.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Ver</a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../../assets/js/script.js"></script>
</body>
</html>
