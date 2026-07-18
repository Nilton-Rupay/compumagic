<?php
require_once 'includes/funciones.php';

$categoria_seleccionada = (int) ($_GET['categoria'] ?? 0);

$categorias = $conexion->query("SELECT c.*, COUNT(p.id) as total_productos
                                 FROM categorias c
                                 LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = 1
                                 GROUP BY c.id
                                 ORDER BY c.nombre ASC");

$mas_vendidos = productos_mas_vendidos($conexion, 4);

if ($categoria_seleccionada > 0) {
    $stmt = $conexion->prepare("SELECT p.*, c.nombre as categoria_nombre
                                 FROM productos p JOIN categorias c ON p.categoria_id = c.id
                                 WHERE p.activo = 1 AND p.categoria_id = ?
                                 ORDER BY p.nombre ASC");
    $stmt->bind_param("i", $categoria_seleccionada);
    $stmt->execute();
    $catalogo = $stmt->get_result();
} else {
    $catalogo = obtener_productos($conexion);
}

$total_productos_activos = $conexion->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1")->fetch_assoc()['total'];

$imagenes_categoria = [
    'Computadoras' => 'cat-computadoras.jpg',
    'Laptops' => 'cat-laptops.jpg',
    'Accesorios' => 'cat-accesorios.jpg',
    'Monitores' => 'cat-monitores.jpg',
    'Almacenamiento' => 'cat-almacenamiento.jpg',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compu Magic - Computadoras, Laptops y Accesorios</title>
    <meta name="description" content="Catálogo de computadoras, laptops y accesorios. Encuentra los equipos disponibles y los más vendidos.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <noscript><style>.reveal { opacity: 1 !important; transform: none !important; }</style></noscript>
</head>
<body class="public-body">

<header class="public-header">
    <div class="public-header-inner">
        <div class="public-logo"><i class="fas fa-laptop-code"></i> Compu Magic</div>
        <nav class="public-nav">
            <a href="index.php">Inicio</a>
            <a href="#catalogo">Catálogo</a>
            <a href="#mas-vendidos">Más Vendidos</a>
            <a href="#categorias">Categorías</a>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-inner">
        <div class="hero-texto">
            <span class="hero-eyebrow"><i class="fas fa-bolt"></i> Tienda especializada en cómputo</span>
            <h1>Equipos y accesorios <span>que sí rinden</span></h1>
            <p>Computadoras, laptops y accesorios para el hogar, la oficina y el gaming. Explora el catálogo disponible y descubre lo que otros clientes ya están comprando.</p>
            <div class="hero-acciones">
                <a href="#catalogo" class="btn btn-lg btn-hero">Ver Catálogo</a>
                <a href="#mas-vendidos" class="btn btn-lg btn-hero-secundario">Más Vendidos</a>
            </div>
            <div class="hero-trust">
                <div><i class="fas fa-shield-halved"></i> Equipos con garantía</div>
                <div><i class="fas fa-boxes-stacked"></i> Stock verificado</div>
                <div><i class="fas fa-headset"></i> Asesoría personalizada</div>
            </div>
        </div>
        <div class="hero-imagen">
            <div class="hero-imagen-marco">
                <img src="assets/img/hero.jpg" alt="Escritorio con equipo de cómputo Compu Magic">
            </div>
            <div class="hero-imagen-chip">
                <i class="fas fa-box-open"></i>
                <div>
                    <strong><?php echo $total_productos_activos; ?></strong>
                    <span>Productos disponibles</span>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="public-main">

    <section id="categorias" class="public-section reveal">
        <div class="public-section-head">
            <h2>Categorías</h2>
            <p>Filtra el catálogo por tipo de equipo</p>
        </div>
        <div class="categoria-grid">
            <a href="index.php" class="categoria-card sin-foto <?php echo $categoria_seleccionada == 0 ? 'activa' : ''; ?>">
                <div class="categoria-card-info">
                    <i class="fas fa-border-all"></i>
                    <span>Todas</span>
                    <small><?php echo $total_productos_activos; ?> productos</small>
                </div>
            </a>
            <?php while ($cat = $categorias->fetch_assoc()): ?>
                <?php $imagen_cat = $imagenes_categoria[$cat['nombre']] ?? null; ?>
                <a href="index.php?categoria=<?php echo $cat['id']; ?>#catalogo" class="categoria-card <?php echo $imagen_cat ? '' : 'sin-foto'; ?> <?php echo $categoria_seleccionada == $cat['id'] ? 'activa' : ''; ?>">
                    <?php if ($imagen_cat): ?>
                        <img src="assets/img/<?php echo $imagen_cat; ?>" alt="<?php echo htmlspecialchars($cat['nombre']); ?>" loading="lazy">
                        <div class="velo-azul"></div>
                    <?php endif; ?>
                    <div class="categoria-card-info">
                        <i class="fas fa-tag"></i>
                        <span><?php echo htmlspecialchars($cat['nombre']); ?></span>
                        <small><?php echo $cat['total_productos']; ?> productos</small>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="mas-vendidos" class="public-section reveal">
        <div class="public-section-head">
            <h2>Los Más Vendidos</h2>
            <p>Los favoritos de nuestros clientes</p>
        </div>
        <?php if ($mas_vendidos->num_rows == 0): ?>
            <p class="texto-vacio">Todavía no hay ventas registradas para mostrar un ranking.</p>
        <?php else: ?>
            <div class="producto-grid">
                <?php while ($p = $mas_vendidos->fetch_assoc()): ?>
                    <div class="producto-card destacado">
                        <span class="badge-vendido"><i class="fas fa-fire"></i> Más vendido</span>
                        <div class="producto-icono"><i class="fas fa-laptop"></i></div>
                        <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
                        <span class="producto-categoria"><?php echo htmlspecialchars($p['categoria_nombre']); ?></span>
                        <div class="producto-precio"><?php echo formato_moneda($p['precio_venta']); ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </section>

    <section id="catalogo" class="public-section reveal">
        <div class="public-section-head">
            <h2><?php echo $categoria_seleccionada > 0 ? 'Catálogo Filtrado' : 'Catálogo Disponible'; ?></h2>
            <p><?php echo $catalogo->num_rows; ?> equipo<?php echo $catalogo->num_rows == 1 ? '' : 's'; ?> encontrados</p>
        </div>
        <?php if ($catalogo->num_rows == 0): ?>
            <p class="texto-vacio">No hay productos disponibles en esta categoría.</p>
        <?php else: ?>
            <div class="producto-grid">
                <?php while ($p = $catalogo->fetch_assoc()): ?>
                    <div class="producto-card">
                        <div class="producto-icono"><i class="fas fa-desktop"></i></div>
                        <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
                        <span class="producto-categoria"><?php echo htmlspecialchars($p['categoria_nombre']); ?></span>
                        <div class="producto-precio"><?php echo formato_moneda($p['precio_venta']); ?></div>
                        <span class="producto-stock <?php echo $p['stock'] <= $p['stock_minimo'] ? 'bajo' : ''; ?>">
                            <?php echo $p['stock'] > 0 ? $p['stock'] . ' disponibles' : 'Sin stock'; ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<footer class="public-footer">
    <div class="public-footer-inner">
        <div class="public-footer-brand"><i class="fas fa-laptop-code"></i> Compu Magic</div>
        <p>Sistema de venta de computadoras, laptops y accesorios.</p>
        <nav class="public-footer-links">
            <a href="#categorias">Categorías</a>
            <a href="#mas-vendidos">Más Vendidos</a>
            <a href="#catalogo">Catálogo</a>
        </nav>
    </div>
</footer>

<script src="assets/js/script.js"></script>
</body>
</html>
