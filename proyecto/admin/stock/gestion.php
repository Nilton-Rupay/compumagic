<?php
require_once '../../includes/funciones.php';
verificar_sesion();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_stock'])) {
    $producto_id = (int) $_POST['producto_id'];
    $nuevo_stock = (int) $_POST['nuevo_stock'];
    $motivo = trim($_POST['motivo'] ?? '');

    if ($nuevo_stock < 0) {
        $error = 'El stock no puede ser negativo.';
    } else {
        $stmt = $conexion->prepare("UPDATE productos SET stock = ? WHERE id = ?");
        $stmt->bind_param("ii", $nuevo_stock, $producto_id);
        if ($stmt->execute()) {
            // Nota: el MER no incluye una tabla de auditoría de movimientos,
            // así que el motivo solo se refleja en el mensaje de confirmación.
            $mensaje = 'Stock actualizado correctamente.' . ($motivo ? ' Motivo: ' . htmlspecialchars($motivo) : '');
        } else {
            $error = 'Error al actualizar el stock.';
        }
        $stmt->close();
    }
}

$productos = $conexion->query("SELECT p.*, c.nombre as categoria_nombre
                                FROM productos p JOIN categorias c ON p.categoria_id = c.id
                                WHERE p.activo = 1
                                ORDER BY p.stock ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Stock - Compu Magic</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../../includes/sidebar.php'; ?>

<div class="main-content">
    <?php $titulo = 'Gestión de Stock'; include '../../includes/topbar.php'; ?>

    <?php if ($mensaje): ?><div class="alert alert-success"><?php echo $mensaje; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="table-container">
        <h2>Inventario General</h2>
        <input type="text" id="buscar-stock" class="input-buscar" onkeyup="buscarTabla('buscar-stock','tabla-stock')" placeholder="Buscar producto...">
        <table id="tabla-stock">
            <thead>
                <tr>
                    <th>Código</th><th>Producto</th><th>Categoría</th>
                    <th>Stock</th><th>Stock Mín.</th><th>Estado</th><th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $productos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['categoria_nombre']); ?></td>
                    <td><?php echo $row['stock']; ?></td>
                    <td><?php echo $row['stock_minimo']; ?></td>
                    <td>
                        <?php if ($row['stock'] == 0): ?>
                            <span class="badge badge-danger">Sin Stock</span>
                        <?php elseif ($row['stock'] <= $row['stock_minimo']): ?>
                            <span class="badge badge-warning">Stock Bajo</span>
                        <?php else: ?>
                            <span class="badge badge-success">Disponible</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm"
                            onclick="cargarStock(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES); ?>', <?php echo $row['stock']; ?>)">Ajustar</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para ajustar stock -->
<div id="modal-stock" class="modal-overlay">
    <div class="form-container">
        <h2>Ajustar Stock</h2>
        <p id="stock-producto-nombre"></p>
        <form method="POST">
            <input type="hidden" name="producto_id" id="stock-producto-id">
            <div class="form-group">
                <label>Nuevo Stock</label>
                <input type="number" name="nuevo_stock" id="nuevo-stock" min="0" required>
            </div>
            <div class="form-group">
                <label>Motivo del ajuste</label>
                <textarea name="motivo" rows="2" placeholder="Ej: Ingreso de mercadería, inventario físico, producto dañado..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" name="actualizar_stock" class="btn btn-success">Actualizar Stock</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarModal('modal-stock')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/script.js"></script>
</body>
</html>
