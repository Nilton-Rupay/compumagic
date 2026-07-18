<?php
require_once '../../includes/funciones.php';
verificar_sesion();

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    if (producto_tiene_ventas($conexion, $id)) {
        // El producto ya aparece en detalle_venta (FK RESTRICT), así que se
        // desactiva en lugar de borrarlo para no romper el historial de ventas.
        $stmt = $conexion->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header('Location: listar.php?msg=desactivado');
        exit;
    }

    $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: listar.php?msg=eliminado');
    exit;
}

header('Location: listar.php');
exit;
