<?php
require_once '../../includes/funciones.php';
verificar_sesion();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_nombre = trim($_POST['cliente_nombre'] ?? '') ?: 'Cliente General';
    $cliente_documento = trim($_POST['cliente_documento'] ?? '');
    $forma_pago = in_array($_POST['forma_pago'] ?? '', ['efectivo', 'tarjeta', 'transferencia', 'yape'])
        ? $_POST['forma_pago'] : 'efectivo';
    $productos_id = $_POST['producto_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];

    if (empty($productos_id)) {
        $error = 'Debe agregar al menos un producto a la venta.';
    } else {
        $conexion->begin_transaction();
        try {
            $total = 0;
            $items = [];

            for ($i = 0; $i < count($productos_id); $i++) {
                $pid = (int) $productos_id[$i];
                $cant = (int) ($cantidades[$i] ?? 0);
                if ($pid <= 0 || $cant <= 0) {
                    continue;
                }

                // SELECT ... FOR UPDATE bloquea la fila para evitar condiciones de carrera
                // si dos ventas descuentan el mismo producto al mismo tiempo.
                $stmt_lock = $conexion->prepare("SELECT * FROM productos WHERE id = ? FOR UPDATE");
                $stmt_lock->bind_param("i", $pid);
                $stmt_lock->execute();
                $producto = $stmt_lock->get_result()->fetch_assoc();
                $stmt_lock->close();

                if (!$producto) {
                    throw new Exception("Uno de los productos seleccionados ya no existe.");
                }
                if ($producto['stock'] < $cant) {
                    throw new Exception("Stock insuficiente para \"{$producto['nombre']}\". Disponible: {$producto['stock']}.");
                }

                $subtotal = $producto['precio_venta'] * $cant;
                $total += $subtotal;
                $items[] = [
                    'producto_id' => $pid,
                    'cantidad' => $cant,
                    'precio_unitario' => $producto['precio_venta'],
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($items)) {
                throw new Exception('Debe agregar al menos un producto válido.');
            }

            $codigo_venta = generar_codigo_venta($conexion);
            $admin_id = $_SESSION['admin_id'];

            $stmt = $conexion->prepare("INSERT INTO ventas (codigo_venta, admin_id, cliente_nombre, cliente_documento, total, forma_pago) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissds", $codigo_venta, $admin_id, $cliente_nombre, $cliente_documento, $total, $forma_pago);
            $stmt->execute();
            $venta_id = $conexion->insert_id;
            $stmt->close();

            $stmt_detalle = $conexion->prepare("INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt_stock = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

            foreach ($items as $item) {
                $stmt_detalle->bind_param(
                    "iiidd",
                    $venta_id,
                    $item['producto_id'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['subtotal']
                );
                $stmt_detalle->execute();

                $stmt_stock->bind_param("ii", $item['cantidad'], $item['producto_id']);
                $stmt_stock->execute();
            }
            $stmt_detalle->close();
            $stmt_stock->close();

            $conexion->commit();
            header('Location: detalle.php?id=' . $venta_id);
            exit;
        } catch (Exception $e) {
            $conexion->rollback();
            $error = $e->getMessage();
        }
    }
}

$productos = $conexion->query("SELECT * FROM productos WHERE activo = 1 AND stock > 0 ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Venta - Compu Magic</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../../includes/sidebar.php'; ?>

<div class="main-content">
    <?php $titulo = 'Nueva Venta'; include '../../includes/topbar.php'; ?>

    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST" id="form-venta">
        <div class="form-container">
            <h2>Datos del Cliente</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Cliente</label>
                    <input type="text" name="cliente_nombre" placeholder="Cliente General">
                </div>
                <div class="form-group">
                    <label>DNI / RUC</label>
                    <input type="text" name="cliente_documento">
                </div>
                <div class="form-group">
                    <label>Forma de Pago</label>
                    <select name="forma_pago">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="yape">Yape</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-container">
            <h2>Agregar Productos</h2>
            <div class="selector-producto">
                <select id="select-producto">
                    <option value="">-- Seleccione un producto --</option>
                    <?php while ($p = $productos->fetch_assoc()): ?>
                        <option value="<?php echo $p['id']; ?>" data-precio="<?php echo $p['precio_venta']; ?>" data-stock="<?php echo $p['stock']; ?>">
                            <?php echo htmlspecialchars($p['codigo'] . ' - ' . $p['nombre']); ?> (Stock: <?php echo $p['stock']; ?>) - <?php echo formato_moneda($p['precio_venta']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="number" id="input-cantidad" min="1" value="1">
                <button type="button" class="btn btn-primary" onclick="agregarProductoVenta()">+ Agregar</button>
            </div>

            <table>
                <thead>
                    <tr><th>Producto</th><th>Precio Unit.</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody id="detalle-venta-body"></tbody>
            </table>

            <div class="total-venta">
                Total: <span id="total-venta">S/ 0.00</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Registrar Venta</button>
            <a href="historial.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script src="../../assets/js/script.js"></script>
</body>
</html>
