<?php
require_once '../../includes/funciones.php';
verificar_sesion();

$error = '';
$datos = ['codigo' => '', 'nombre' => '', 'descripcion' => '', 'categoria_id' => '', 'precio_compra' => '', 'precio_venta' => '', 'stock' => 0, 'stock_minimo' => 5];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos['codigo'] = trim($_POST['codigo'] ?? '');
    $datos['nombre'] = trim($_POST['nombre'] ?? '');
    $datos['descripcion'] = trim($_POST['descripcion'] ?? '');
    $datos['categoria_id'] = (int) ($_POST['categoria_id'] ?? 0);
    $datos['precio_compra'] = (float) ($_POST['precio_compra'] ?? 0);
    $datos['precio_venta'] = (float) ($_POST['precio_venta'] ?? 0);
    $datos['stock'] = (int) ($_POST['stock'] ?? 0);
    $datos['stock_minimo'] = (int) ($_POST['stock_minimo'] ?? 5);

    if (empty($datos['codigo']) || empty($datos['nombre']) || $datos['categoria_id'] <= 0) {
        $error = 'Por favor complete los campos obligatorios: código, nombre y categoría.';
    } elseif ($datos['precio_compra'] <= 0 || $datos['precio_venta'] <= 0) {
        $error = 'Los precios deben ser mayores a cero.';
    } elseif ($datos['precio_venta'] < $datos['precio_compra']) {
        $error = 'El precio de venta no puede ser menor al precio de compra.';
    } elseif (codigo_producto_existe($conexion, $datos['codigo'])) {
        $error = 'Ya existe un producto con ese código.';
    } else {
        $stmt = $conexion->prepare("INSERT INTO productos (codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssiddii",
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['categoria_id'],
            $datos['precio_compra'],
            $datos['precio_venta'],
            $datos['stock'],
            $datos['stock_minimo']
        );
        if ($stmt->execute()) {
            header('Location: listar.php?msg=agregado');
            exit;
        }
        $error = 'Error al guardar el producto.';
        $stmt->close();
    }
}

$categorias = obtener_categorias($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - Compu Magic</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../../includes/sidebar.php'; ?>

<div class="main-content">
    <?php $titulo = 'Nuevo Producto'; include '../../includes/topbar.php'; ?>

    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="form-container form-container-page">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Código *</label>
                    <input type="text" name="codigo" value="<?php echo htmlspecialchars($datos['codigo']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Categoría *</label>
                    <select name="categoria_id" required>
                        <option value="">-- Seleccione --</option>
                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $datos['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($datos['nombre']); ?>" required>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($datos['descripcion']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio de Compra (S/) *</label>
                    <input type="number" step="0.01" min="0" name="precio_compra" value="<?php echo $datos['precio_compra']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Precio de Venta (S/) *</label>
                    <input type="number" step="0.01" min="0" name="precio_venta" value="<?php echo $datos['precio_venta']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock Inicial</label>
                    <input type="number" min="0" name="stock" value="<?php echo $datos['stock']; ?>">
                </div>
                <div class="form-group">
                    <label>Stock Mínimo</label>
                    <input type="number" min="0" name="stock_minimo" value="<?php echo $datos['stock_minimo']; ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Guardar Producto</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
