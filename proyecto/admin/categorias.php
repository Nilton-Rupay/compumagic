<?php
require_once '../includes/funciones.php';
verificar_sesion();

$mensaje = '';
$error = '';

// Crear categoría
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_categoria'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($nombre)) {
        $error = 'El nombre de la categoría es obligatorio.';
    } else {
        $stmt = $conexion->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        $mensaje = $stmt->execute() ? 'Categoría agregada correctamente.' : '';
        if (!$mensaje) {
            $error = 'Error al agregar la categoría.';
        }
        $stmt->close();
    }
}

// Actualizar categoría
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_categoria'])) {
    $id = (int) $_POST['id'];
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($nombre)) {
        $error = 'El nombre de la categoría es obligatorio.';
    } else {
        $stmt = $conexion->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        $mensaje = $stmt->execute() ? 'Categoría actualizada correctamente.' : '';
        if (!$mensaje) {
            $error = 'Error al actualizar la categoría.';
        }
        $stmt->close();
    }
}

// Eliminar categoría (protegida por integridad referencial)
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    if (contar_productos_categoria($conexion, $id) > 0) {
        $error = 'No se puede eliminar: existen productos asociados a esta categoría.';
    } else {
        $stmt = $conexion->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $mensaje = $stmt->execute() ? 'Categoría eliminada correctamente.' : '';
        if (!$mensaje) {
            $error = 'Error al eliminar la categoría.';
        }
        $stmt->close();
    }
}

$categorias = $conexion->query("SELECT c.*, COUNT(p.id) as total_productos
                                 FROM categorias c
                                 LEFT JOIN productos p ON p.categoria_id = c.id
                                 GROUP BY c.id
                                 ORDER BY c.nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Compu Magic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <?php $titulo = 'Categorías'; include '../includes/topbar.php'; ?>

    <?php if ($mensaje): ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="table-container">
        <div class="table-header">
            <h2>Listado de Categorías</h2>
            <button type="button" class="btn btn-success" onclick="abrirModal('modal-nueva')">+ Nueva Categoría</button>
        </div>
        <input type="text" id="buscar-categoria" class="input-buscar" onkeyup="buscarTabla('buscar-categoria','tabla-categorias')" placeholder="Buscar categoría...">
        <table id="tabla-categorias">
            <thead>
                <tr><th>Nombre</th><th>Descripción</th><th>Productos</th><th>Fecha Creación</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php while ($row = $categorias->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                    <td><span class="badge badge-success"><?php echo $row['total_productos']; ?></span></td>
                    <td><?php echo formato_fecha($row['fecha_creacion']); ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm"
                            onclick='abrirModalEditar(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP); ?>)'>Editar</button>
                        <a href="categorias.php?eliminar=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                            onclick="return confirmarEliminar('¿Eliminar la categoría &quot;<?php echo htmlspecialchars($row['nombre']); ?>&quot;?');">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva Categoría -->
<div id="modal-nueva" class="modal-overlay">
    <div class="form-container">
        <h2>Nueva Categoría</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" name="agregar_categoria" class="btn btn-success">Guardar</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarModal('modal-nueva')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Categoría -->
<div id="modal-editar" class="modal-overlay">
    <div class="form-container">
        <h2>Editar Categoría</h2>
        <form method="POST">
            <input type="hidden" name="id" id="editar-id">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" id="editar-nombre" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" id="editar-descripcion" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" name="editar_categoria" class="btn btn-success">Actualizar</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarModal('modal-editar')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
