// Sistema de Ventas - Funciones JavaScript compartidas

function confirmarEliminar(mensaje) {
    return confirm(mensaje || '¿Está seguro de eliminar este registro?');
}

function buscarTabla(inputId, tablaId) {
    var input = document.getElementById(inputId);
    var filter = input.value.toUpperCase();
    var table = document.getElementById(tablaId);
    var tr = table.getElementsByTagName('tr');

    for (var i = 0; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName('td');
        var visible = td.length === 0; // no ocultar la fila de encabezado
        for (var j = 0; j < td.length; j++) {
            var txtValue = td[j].textContent || td[j].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                visible = true;
                break;
            }
        }
        tr[i].style.display = visible ? '' : 'none';
    }
}

function abrirModal(id) {
    document.getElementById(id).classList.add('abierto');
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('abierto');
}

function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    if (!sidebar) return;
    sidebar.classList.toggle('abierto');
    if (overlay) overlay.classList.toggle('abierto');
}

function abrirModalEditar(categoria) {
    document.getElementById('editar-id').value = categoria.id;
    document.getElementById('editar-nombre').value = categoria.nombre;
    document.getElementById('editar-descripcion').value = categoria.descripcion || '';
    abrirModal('modal-editar');
}

function cargarStock(id, nombre, stockActual) {
    document.getElementById('stock-producto-id').value = id;
    document.getElementById('stock-producto-nombre').textContent = nombre;
    document.getElementById('nuevo-stock').value = stockActual;
    abrirModal('modal-stock');
}

// ---------- Registro de venta (ventas/nueva.php) ----------
var itemsVenta = [];

function agregarProductoVenta() {
    var select = document.getElementById('select-producto');
    var cantidadInput = document.getElementById('input-cantidad');
    var option = select.options[select.selectedIndex];

    if (!option || !option.value) {
        alert('Seleccione un producto.');
        return;
    }

    var id = option.value;
    var nombre = option.text;
    var precio = parseFloat(option.dataset.precio);
    var stockDisponible = parseInt(option.dataset.stock, 10);
    var cantidad = parseInt(cantidadInput.value, 10) || 1;

    var existente = itemsVenta.find(function (item) { return item.id === id; });
    var cantidadActual = existente ? existente.cantidad : 0;

    if (cantidad + cantidadActual > stockDisponible) {
        alert('Stock insuficiente. Disponible: ' + stockDisponible);
        return;
    }

    if (existente) {
        existente.cantidad += cantidad;
    } else {
        itemsVenta.push({ id: id, nombre: nombre, precio: precio, cantidad: cantidad });
    }

    cantidadInput.value = 1;
    renderizarTablaVenta();
}

function eliminarProductoVenta(id) {
    itemsVenta = itemsVenta.filter(function (item) { return item.id !== id; });
    renderizarTablaVenta();
}

function renderizarTablaVenta() {
    var tbody = document.getElementById('detalle-venta-body');
    if (!tbody) return;

    tbody.innerHTML = '';
    var total = 0;

    itemsVenta.forEach(function (item) {
        var subtotal = item.precio * item.cantidad;
        total += subtotal;

        var fila = document.createElement('tr');

        var celdaAccion = document.createElement('td');
        var btnQuitar = document.createElement('button');
        btnQuitar.type = 'button';
        btnQuitar.className = 'btn btn-danger btn-sm';
        btnQuitar.textContent = 'Quitar';
        btnQuitar.addEventListener('click', function () { eliminarProductoVenta(item.id); });
        celdaAccion.appendChild(btnQuitar);

        var inputProducto = document.createElement('input');
        inputProducto.type = 'hidden';
        inputProducto.name = 'producto_id[]';
        inputProducto.value = item.id;
        celdaAccion.appendChild(inputProducto);

        var inputCantidad = document.createElement('input');
        inputCantidad.type = 'hidden';
        inputCantidad.name = 'cantidad[]';
        inputCantidad.value = item.cantidad;
        celdaAccion.appendChild(inputCantidad);

        fila.innerHTML =
            '<td>' + item.nombre + '</td>' +
            '<td>S/ ' + item.precio.toFixed(2) + '</td>' +
            '<td>' + item.cantidad + '</td>' +
            '<td>S/ ' + subtotal.toFixed(2) + '</td>';
        fila.appendChild(celdaAccion);

        tbody.appendChild(fila);
    });

    var totalEl = document.getElementById('total-venta');
    if (totalEl) totalEl.textContent = 'S/ ' + total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function () {
    var formVenta = document.getElementById('form-venta');
    if (formVenta) {
        formVenta.addEventListener('submit', function (e) {
            if (itemsVenta.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto a la venta.');
            }
        });
    }

    // Revela secciones del sitio público con un fade-in-up al hacer scroll
    var elementosRevelados = document.querySelectorAll('.reveal');
    if (elementosRevelados.length && 'IntersectionObserver' in window) {
        var observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (entrada.isIntersecting) {
                    entrada.target.classList.add('visible');
                    observador.unobserve(entrada.target);
                }
            });
        }, { threshold: 0.12 });

        elementosRevelados.forEach(function (el) { observador.observe(el); });
    } else {
        elementosRevelados.forEach(function (el) { el.classList.add('visible'); });
    }
});
