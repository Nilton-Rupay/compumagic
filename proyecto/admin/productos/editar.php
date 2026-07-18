<?php
require_once '../../includes/funciones.php';
verificar_sesion();

$id = (int) ($_GET['id'] ?? 0);
$producto = obtener_producto_por_id($conexion, $id);

if (!$producto) {
    header('Location: listar.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria_id = (int) ($_POST['categoria_id'] ?? 0);
    $precio_compra = (float) ($_POST['precio_compra'] ?? 0);
    $precio_venta = (float) ($_POST['precio_venta'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $stock_minimo = (int) ($_POST['stock_minimo'] ?? 5);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($codigo) || empty($nombre) || $categoria_id <= 0) {
        $error = 'Por favor complete los campos obligatorios: código, nombre y categoría.';
    } elseif ($precio_compra <= 0 || $precio_venta <= 0) {
        $error = 'Los precios deben ser mayores a cero.';
    } elseif ($precio_venta < $precio_compra) {
        $error = 'El precio de venta no puede ser menor al precio de compra.';
    } elseif (codigo_producto_existe($conexion, $codigo, $id)) {
        $error = 'Ya existe otro producto con ese código.';
    } else {
        $stmt = $conexion->prepare("UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, categoria_id = ?, precio_compra = ?, precio_venta = ?, stock = ?, stock_minimo = ?, activo = ? WHERE id = ?");
        $stmt->bind_param(
            "sssiddiiii",
            $codigo, $nombre, $descripcion, $categoria_id,
            $precio_compra, $precio_venta, $stock, $stock_minimo, $activo, $id
        );
        if ($stmt->execute()) {
            header('Location: listar.php?msg=actualizado');
            exit;
        }
        $error = 'Error al actualizar el producto.';
        $stmt->close();
    }

    // Conservar los datos ingresados si hubo error de validación
    $producto = compact('codigo', 'nombre', 'descripcion', 'categoria_id', 'precio_compra', 'precio_venta', 'stock', 'stock_minimo', 'activo') + $producto;
}

$categorias = obtener_categorias($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Compu Magic</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../../includes/sidebar.php'; ?>

<div class="main-content">
    <?php $titulo = 'Editar Producto'; include '../../includes/topbar.php'; ?>

    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="form-container form-container-page">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Código *</label>
                    <input type="text" name="codigo" value="<?php echo htmlspecialchars($producto['codigo']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Categoría *</label>
                    <select name="categoria_id" required>
                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $producto['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio de Compra (S/) *</label>
                    <input type="number" step="0.01" min="0" name="precio_compra" value="<?php echo $producto['precio_compra']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Precio de Venta (S/) *</label>
                    <input type="number" step="0.01" min="0" name="precio_venta" value="<?php echo $producto['precio_venta']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" min="0" name="stock" value="<?php echo $producto['stock']; ?>">
                </div>
                <div class="form-group">
                    <label>Stock Mínimo</label>
                    <input type="number" min="0" name="stock_minimo" value="<?php echo $producto['stock_minimo']; ?>">
                </div>
            </div>

            <div class="form-group form-check">
                <label><input type="checkbox" name="activo" <?php echo $producto['activo'] ? 'checked' : ''; ?>> Producto activo</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Actualizar Producto</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
