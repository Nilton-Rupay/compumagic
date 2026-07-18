<?php
require_once 'config.php';

// ---------- Sesión ----------
// header('Location: ...') con ruta relativa se resuelve por el NAVEGADOR contra
// la URL actual, no contra la ubicación del archivo PHP. Por eso no basta con
// 'login.php': hay que calcular cuántos niveles bajo admin/ está el script actual.
function ruta_admin($archivo) {
    $script = $_SERVER['SCRIPT_NAME'];
    $pos = strpos($script, '/admin/');
    if ($pos === false) {
        return $archivo;
    }
    $resto = substr($script, $pos + strlen('/admin/'));
    $profundidad = substr_count($resto, '/');
    return str_repeat('../', $profundidad) . $archivo;
}

function verificar_sesion() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . ruta_admin('login.php'));
        exit;
    }
}

// ---------- Categorías ----------
function obtener_categorias($conexion) {
    $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
    return $conexion->query($sql);
}

function obtener_categoria_por_id($conexion, $id) {
    $stmt = $conexion->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function contar_productos_categoria($conexion, $categoria_id) {
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?");
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

// ---------- Productos ----------
function obtener_productos($conexion, $solo_activos = true) {
    $sql = "SELECT p.*, c.nombre as categoria_nombre
            FROM productos p
            JOIN categorias c ON p.categoria_id = c.id";
    if ($solo_activos) {
        $sql .= " WHERE p.activo = 1";
    }
    $sql .= " ORDER BY p.nombre ASC";
    return $conexion->query($sql);
}

function obtener_producto_por_id($conexion, $id) {
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function codigo_producto_existe($conexion, $codigo, $excluir_id = null) {
    if ($excluir_id) {
        $stmt = $conexion->prepare("SELECT id FROM productos WHERE codigo = ? AND id != ?");
        $stmt->bind_param("si", $codigo, $excluir_id);
    } else {
        $stmt = $conexion->prepare("SELECT id FROM productos WHERE codigo = ?");
        $stmt->bind_param("s", $codigo);
    }
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function producto_tiene_ventas($conexion, $id) {
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM detalle_venta WHERE producto_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] > 0;
}

// ---------- Stock ----------
function alerta_stock_bajo($conexion) {
    $sql = "SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo AND activo = 1";
    $result = $conexion->query($sql);
    return $result->fetch_assoc()['total'];
}

function productos_stock_bajo($conexion, $limite = 5) {
    $stmt = $conexion->prepare("SELECT p.*, c.nombre as categoria_nombre
                                 FROM productos p JOIN categorias c ON p.categoria_id = c.id
                                 WHERE p.stock <= p.stock_minimo AND p.activo = 1
                                 ORDER BY p.stock ASC LIMIT ?");
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    return $stmt->get_result();
}

// ---------- Ventas ----------
function generar_codigo_venta($conexion) {
    $fecha = date('Ymd');
    $sql = "SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha_venta) = CURDATE()";
    $result = $conexion->query($sql);
    $row = $result->fetch_assoc();
    $numero = $row['total'] + 1;
    return "VEN-" . $fecha . "-" . str_pad($numero, 4, "0", STR_PAD_LEFT);
}

function obtener_ventas($conexion) {
    $sql = "SELECT v.*, a.nombre as admin_nombre
            FROM ventas v JOIN admin a ON v.admin_id = a.id
            ORDER BY v.fecha_venta DESC";
    return $conexion->query($sql);
}

function obtener_venta_por_id($conexion, $id) {
    $stmt = $conexion->prepare("SELECT v.*, a.nombre as admin_nombre
                                 FROM ventas v JOIN admin a ON v.admin_id = a.id
                                 WHERE v.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function obtener_detalle_venta($conexion, $venta_id) {
    $stmt = $conexion->prepare("SELECT dv.*, p.nombre as producto_nombre, p.codigo as producto_codigo
                                 FROM detalle_venta dv JOIN productos p ON dv.producto_id = p.id
                                 WHERE dv.venta_id = ?");
    $stmt->bind_param("i", $venta_id);
    $stmt->execute();
    return $stmt->get_result();
}

function ultimas_ventas($conexion, $limite = 5) {
    $stmt = $conexion->prepare("SELECT v.*, a.nombre as admin_nombre
                                 FROM ventas v JOIN admin a ON v.admin_id = a.id
                                 ORDER BY v.fecha_venta DESC LIMIT ?");
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    return $stmt->get_result();
}

function productos_mas_vendidos($conexion, $limite = 5) {
    $stmt = $conexion->prepare("SELECT p.*, c.nombre as categoria_nombre, COALESCE(SUM(dv.cantidad), 0) as total_vendido
                                 FROM productos p
                                 JOIN categorias c ON p.categoria_id = c.id
                                 LEFT JOIN detalle_venta dv ON dv.producto_id = p.id
                                 WHERE p.activo = 1
                                 GROUP BY p.id
                                 ORDER BY total_vendido DESC, p.nombre ASC
                                 LIMIT ?");
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    return $stmt->get_result();
}

function ventas_ultimos_dias($conexion, $dias = 7) {
    $stmt = $conexion->prepare("SELECT DATE(fecha_venta) as dia, SUM(total) as total
                                 FROM ventas
                                 WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                                 GROUP BY DATE(fecha_venta)");
    $dias_intervalo = $dias - 1;
    $stmt->bind_param("i", $dias_intervalo);
    $stmt->execute();
    $result = $stmt->get_result();

    $totales_por_dia = [];
    while ($row = $result->fetch_assoc()) {
        $totales_por_dia[$row['dia']] = (float) $row['total'];
    }

    $serie = [];
    for ($i = $dias - 1; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        $serie[] = ['fecha' => $fecha, 'total' => $totales_por_dia[$fecha] ?? 0];
    }
    return $serie;
}

// ---------- Utilidades ----------
function formato_moneda($cantidad) {
    return 'S/ ' . number_format($cantidad, 2, '.', ',');
}

function formato_fecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}

function numero_a_letras_grupo($n) {
    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas10 = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    if ($n == 100) {
        return 'CIEN';
    }

    $texto = '';
    if ($n >= 100) {
        $texto .= $centenas[intdiv($n, 100)] . ' ';
        $n %= 100;
    }
    if ($n >= 10 && $n < 20) {
        $texto .= $decenas10[$n - 10];
        return trim($texto);
    }
    if ($n >= 20) {
        $texto .= $decenas[intdiv($n, 10)];
        $n %= 10;
        if ($n > 0) {
            $texto .= ' Y ' . $unidades[$n];
        }
        return trim($texto);
    }
    if ($n > 0) {
        $texto .= $unidades[$n];
    }
    return trim($texto);
}

function numero_a_letras($numero) {
    $entero = intdiv((int) round($numero * 100), 100);
    $centavos = round($numero * 100) % 100;

    if ($entero == 0) {
        $texto = 'CERO';
    } elseif ($entero == 1) {
        $texto = 'UN';
    } else {
        $miles = intdiv($entero, 1000);
        $resto = $entero % 1000;
        $texto = '';
        if ($miles > 0) {
            $texto .= ($miles == 1 ? 'MIL' : numero_a_letras_grupo($miles) . ' MIL') . ' ';
        }
        if ($resto > 0) {
            $texto .= numero_a_letras_grupo($resto);
        }
        $texto = trim($texto);
    }

    return $texto . ' CON ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
}
