<?php
$en_subcarpeta = strpos($_SERVER['PHP_SELF'], '/productos/') !== false
    || strpos($_SERVER['PHP_SELF'], '/ventas/') !== false
    || strpos($_SERVER['PHP_SELF'], '/stock/') !== false;
$ruta_base = $en_subcarpeta ? '../' : '';
$pagina_actual = basename($_SERVER['PHP_SELF']);
$carpeta_actual = basename(dirname($_SERVER['PHP_SELF']));

function menu_activo($condicion) {
    return $condicion ? 'active' : '';
}
?>
<button type="button" class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-laptop-code"></i>
        <h3>Compu Magic</h3>
    </div>
    <ul class="sidebar-menu">
        <li><a href="<?php echo $ruta_base; ?>dashboard.php" class="<?php echo menu_activo($pagina_actual == 'dashboard.php'); ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="<?php echo $ruta_base; ?>productos/listar.php" class="<?php echo menu_activo($carpeta_actual == 'productos'); ?>"><i class="fas fa-box"></i> Productos</a></li>
        <li><a href="<?php echo $ruta_base; ?>categorias.php" class="<?php echo menu_activo($pagina_actual == 'categorias.php'); ?>"><i class="fas fa-tags"></i> Categorías</a></li>
        <li><a href="<?php echo $ruta_base; ?>ventas/nueva.php" class="<?php echo menu_activo($carpeta_actual == 'ventas' && $pagina_actual == 'nueva.php'); ?>"><i class="fas fa-cart-plus"></i> Nueva Venta</a></li>
        <li><a href="<?php echo $ruta_base; ?>ventas/historial.php" class="<?php echo menu_activo($carpeta_actual == 'ventas' && $pagina_actual != 'nueva.php'); ?>"><i class="fas fa-history"></i> Historial Ventas</a></li>
        <li><a href="<?php echo $ruta_base; ?>stock/gestion.php" class="<?php echo menu_activo($carpeta_actual == 'stock'); ?>"><i class="fas fa-warehouse"></i> Gestión Stock</a></li>
        <li><a href="<?php echo $ruta_base; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
    </ul>
</div>
