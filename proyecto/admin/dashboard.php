<?php
require_once '../includes/funciones.php';
verificar_sesion();

$total_productos = $conexion->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1")->fetch_assoc()['total'];
$total_ventas = $conexion->query("SELECT COUNT(*) as total FROM ventas")->fetch_assoc()['total'];
$ingresos = $conexion->query("SELECT COALESCE(SUM(total),0) as total FROM ventas")->fetch_assoc()['total'];
$total_categorias = $conexion->query("SELECT COUNT(*) as total FROM categorias")->fetch_assoc()['total'];
$stock_bajo_count = alerta_stock_bajo($conexion);
$productos_bajo = productos_stock_bajo($conexion, 5);
$ventas_recientes = ultimas_ventas($conexion, 5);
$mas_vendidos = productos_mas_vendidos($conexion, 5);
$serie_semanal = ventas_ultimos_dias($conexion, 7);
$max_serie = max(array_column($serie_semanal, 'total')) ?: 1;
$dias_es = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mié', 'Thu' => 'Jue', 'Fri' => 'Vie', 'Sat' => 'Sáb', 'Sun' => 'Dom'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Compu Magic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <?php $titulo = 'Dashboard'; include '../includes/topbar.php'; ?>

    <div class="cards">
        <div class="card">
            <div class="card-number"><?php echo $total_productos; ?></div>
            <div class="card-label"><i class="fas fa-box"></i> Productos Registrados</div>
        </div>
        <div class="card">
            <div class="card-number"><?php echo $total_ventas; ?></div>
            <div class="card-label"><i class="fas fa-cart-plus"></i> Ventas Realizadas</div>
        </div>
        <div class="card">
            <div class="card-number"><?php echo formato_moneda($ingresos); ?></div>
            <div class="card-label"><i class="fas fa-coins"></i> Ingresos Totales</div>
        </div>
        <div class="card">
            <div class="card-number"><?php echo $total_categorias; ?></div>
            <div class="card-label"><i class="fas fa-tags"></i> Categorías</div>
        </div>
        <div class="card <?php echo $stock_bajo_count > 0 ? 'card-alerta' : ''; ?>">
            <div class="card-number"><?php echo $stock_bajo_count; ?></div>
            <div class="card-label"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</div>
        </div>
    </div>

    <div class="table-container">
        <h2>Ventas de los Últimos 7 Días</h2>
        <div class="mini-chart">
            <?php foreach ($serie_semanal as $dia): ?>
                <?php
                $etiqueta = $dias_es[date('D', strtotime($dia['fecha']))];
                $altura = $dia['total'] > 0 ? max(6, round(($dia['total'] / $max_serie) * 100)) : 2;
                ?>
                <div class="mini-chart-col" title="<?php echo htmlspecialchars(formato_moneda($dia['total'])); ?>">
                    <div class="mini-chart-bar" style="height: <?php echo $altura; ?>%;"></div>
                    <span class="mini-chart-label"><?php echo $etiqueta; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="table-container">
            <h2>Productos Más Vendidos</h2>
            <table>
                <thead>
                    <tr><th>Producto</th><th>Categoría</th><th>Unidades Vendidas</th></tr>
                </thead>
                <tbody>
                    <?php if ($mas_vendidos->num_rows == 0): ?>
                        <tr><td colspan="3">Aún no hay ventas registradas.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $mas_vendidos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['categoria_nombre']); ?></td>
                                <td><span class="badge badge-success"><?php echo $row['total_vendido']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h2>Productos con Stock Bajo</h2>
            <table>
                <thead>
                    <tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Stock</th><th>Mínimo</th></tr>
                </thead>
                <tbody>
                    <?php if ($productos_bajo->num_rows == 0): ?>
                        <tr><td colspan="5">No hay productos con stock bajo.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $productos_bajo->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['categoria_nombre']); ?></td>
                                <td class="stock-bajo"><?php echo $row['stock']; ?></td>
                                <td><?php echo $row['stock_minimo']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h2>Últimas Ventas</h2>
            <table>
                <thead>
                    <tr><th>Código</th><th>Cliente</th><th>Total</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php if ($ventas_recientes->num_rows == 0): ?>
                        <tr><td colspan="4">Aún no se han registrado ventas.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $ventas_recientes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['codigo_venta']); ?></td>
                                <td><?php echo htmlspecialchars($row['cliente_nombre']); ?></td>
                                <td><?php echo formato_moneda($row['total']); ?></td>
                                <td><?php echo formato_fecha($row['fecha_venta']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
