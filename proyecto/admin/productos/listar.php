<?php
require_once '../../includes/funciones.php';
verificar_sesion();

$mensajes = [
    'agregado' => 'Producto agregado correctamente.',
    'actualizado' => 'Producto actualizado correctamente.',
    'eliminado' => 'Producto eliminado correctamente.',
    'desactivado' => 'El producto tiene ventas registradas, por lo que fue desactivado en lugar de eliminado.',
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? '';

$productos = obtener_productos($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Compu Magic</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../../includes/sidebar.php'; ?>

<div class="main-content">
    <?php $titulo = 'Productos'; include '../../includes/topbar.php'; ?>

    <?php if ($mensaje): ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>

    <div class="table-container">
        <div class="table-header">
            <h2>Listado de Productos</h2>
            <a href="agregar.php" class="btn btn-success">+ Nuevo Producto</a>
        </div>
        <input type="text" id="buscar-producto" class="input-buscar" onkeyup="buscarTabla('buscar-producto','tabla-productos')" placeholder="Buscar por código, nombre o categoría...">
        <table id="tabla-productos">
            <thead>
                <tr>
                    <th>Código</th><th>Nombre</th><th>Categoría</th>
                    <th>Precio Compra</th><th>Precio Venta</th><th>Stock</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $productos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['categoria_nombre']); ?></td>
                    <td><?php echo formato_moneda($row['precio_compra']); ?></td>
                    <td><?php echo formato_moneda($row['precio_venta']); ?></td>
                    <td class="<?php echo $row['stock'] <= $row['stock_minimo'] ? 'stock-bajo' : 'stock-normal'; ?>">
                        <?php echo $row['stock']; ?>
                    </td>
                    <td>
                        <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="eliminar.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                            onclick="return confirmarEliminar('¿Eliminar el producto &quot;<?php echo htmlspecialchars($row['nombre']); ?>&quot;?');">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../../assets/js/script.js"></script>
</body>
</html>
