/* assets/script.js EXPERT */
$(document).ready(function () {
    // 1. Initialize DataTables
    var table = $('#inventoryTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        columns: [
            { data: 'sku' },
            { data: 'name' },
            { data: 'brand' },
            { data: 'size' },
            { data: 'formatted_price' },
            { data: 'stock' },
            { data: 'warehouse_location' },
            {
                data: null,
                render: function (data, type, row) {
                    if (currentUserRole !== 'admin') {
                        return '<span class="badge bg-secondary">Solo Lectura</span>';
                    }
                    return `
                        <button class="btn btn-action btn-light text-primary me-2" onclick="editProduct(${row.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-action btn-light text-danger" onclick="deleteProduct(${row.id})"><i class="fas fa-trash-alt"></i></button>
                    `;
                }
            }
        ],
        order: [[5, 'desc']]
    });

    loadInventory();

    // 2. Marketplace Checkbox Logic
    $('.mp-check').change(function () {
        var targetId = $(this).data('target');
        if ($(this).is(':checked')) {
            $('#' + targetId).removeClass('d-none').focus();
        } else {
            $('#' + targetId).addClass('d-none').val('');
        }
    });

    // 3. Enter Key for Sale Search
    $('#saleSearchTerm').on('keypress', function (e) {
        if (e.which === 13) {
            searchSaleProduct();
        }
    });

    // 4. Theme Toggle Logic (Delegated event for robustness)
    function updateThemeIcon() {
        var t = document.documentElement.getAttribute('data-theme');
        var i = $('#themeToggle i');
        if (t === 'dark') {
            i.removeClass('fa-moon').addClass('fa-sun');
        } else {
            i.removeClass('fa-sun').addClass('fa-moon');
        }
    }

    // Initial State Check
    updateThemeIcon();

    $(document).on('click', '#themeToggle', function (e) {
        e.preventDefault(); // Prevent any default behavior

        var currentTheme = document.documentElement.getAttribute('data-theme');
        var newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        updateThemeIcon();
    });
});

// var currentUserRole = 'user'; // Set in index.php via PHP
// If not set for some reason, default to user
if (typeof currentUserRole === 'undefined') var currentUserRole = 'user';

function loadInventory() {
    $.post('actions.php', { action: 'list' }, function (resp) {
        if (resp.status === 'success') {
            currentUserRole = resp.role || 'user';

            // RBAC UI Layer
            // If strict Read Only, maybe hide Sales too? 
            // User requirement: "si es usuario normal es de solo lectura".
            // However, "Vender" is usually a basic function. 
            // I will HIDE "Vender" button if I strictly follow "Read Only".
            // But usually "Read Only" refers to Admin/CRUD configuration.
            // Let's SHOW Vender for now since it's a specific requested button, 
            // but hide CRUD.
            // Actually, let's keep it safe. If user says "Read Only", maybe they mean "Cannot Edit Products".
            // Selling modifies stock. 

            if (currentUserRole !== 'admin') {
                $('#btnAddProduct').hide();
            } else {
                $('#btnAddProduct').show();
            }

            // Update Table
            var table = $('#inventoryTable').DataTable();
            table.clear();
            table.rows.add(resp.data);
            table.draw();

            // Update Cards
            $('#stat-total-stock').text(resp.stats.total_stock);
            $('#stat-total-cost').text(resp.stats.total_cost);
            $('#stat-total-value').text(resp.stats.total_value);
        } else {
            Swal.fire('Error', resp.message, 'error');
        }
    }, 'json');
}

// Global scope for onclick handlers
window.openProductModal = function () {
    if (currentUserRole !== 'admin') {
        Swal.fire('Acceso Denegado', 'Solo administradores pueden crear productos.', 'warning');
        return;
    }
    $('#productForm')[0].reset();
    $('#productId').val('');
    $('#modalTitle').text('Nuevo Producto');
    $('.mp-input').addClass('d-none');
    $('#productModal').modal('show');
};

window.saveProduct = function () {
    var mps = [];
    if ($('#check_ml').is(':checked')) mps.push({ platform: 'Mercado Libre', active: 1, link: $('#input_ml').val() });
    if ($('#check_tn').is(':checked')) mps.push({ platform: 'Tiendanube', active: 1, link: $('#input_tn').val() });
    if ($('#check_fb').is(':checked')) mps.push({ platform: 'Facebook', active: 1, link: $('#input_fb').val() });
    if ($('#check_gt').is(':checked')) mps.push({ platform: 'Go Trendier', active: 1, link: $('#input_gt').val() });
    if ($('#check_sh').is(':checked')) mps.push({ platform: 'Shein', active: 1, link: $('#input_sh').val() });
    if ($('#check_tt').is(':checked')) mps.push({ platform: 'Tik Tok', active: 1, link: $('#input_tt').val() });
    if ($('#check_web').is(':checked')) mps.push({ platform: 'Web', active: 1, link: $('#input_web').val() });

    var formData = $('#productForm').serializeArray();
    var dataObj = {};
    $(formData).each(function (index, obj) {
        dataObj[obj.name] = obj.value;
    });
    dataObj.marketplaces = mps;

    $.post('actions.php', dataObj, function (resp) {
        if (resp.status === 'success') {
            $('#productModal').modal('hide');
            Swal.fire('Guardado', resp.message, 'success');
            loadInventory();
        } else {
            Swal.fire('Error', resp.message, 'error');
        }
    }, 'json');
};

window.editProduct = function (id) {
    if (currentUserRole !== 'admin') return;

    $.get('actions.php', { action: 'get_product', id: id }, function (resp) {
        if (resp.status === 'success') {
            var p = resp.data;
            $('#productId').val(p.id);
            $('#sku').val(p.sku);
            $('#barcode').val(p.barcode);
            $('#name').val(p.name);
            $('#brand').val(p.brand);
            $('#size').val(p.size);
            $('#warehouse_location').val(p.warehouse_location);
            $('#cost_price').val(p.cost_price);
            $('#sale_price').val(p.sale_price);
            $('#stock').val(p.stock);

            // Restore Marketplaces
            $('.mp-check').prop('checked', false);
            $('.mp-input').addClass('d-none').val('');

            if (p.marketplaces && p.marketplaces.length > 0) {
                p.marketplaces.forEach(function (mp) {
                    var idMap = {
                        'Mercado Libre': 'ml',
                        'Tiendanube': 'tn',
                        'Facebook': 'fb',
                        'Go Trendier': 'gt',
                        'Shein': 'sh',
                        'Tik Tok': 'tt',
                        'Web': 'web'
                    };

                    var suffix = idMap[mp.platform];
                    if (suffix) {
                        $('#check_' + suffix).prop('checked', true);
                        $('#input_' + suffix).removeClass('d-none').val(mp.link);
                    }
                });
            }

            $('#modalTitle').text('Editar Producto');
            $('#productModal').modal('show');
        }
    }, 'json');
};

window.deleteProduct = function (id) {
    if (currentUserRole !== 'admin') return;

    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esto",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('actions.php', { action: 'delete', id: id }, function (resp) {
                if (resp.status === 'success') {
                    Swal.fire('Eliminado!', resp.message, 'success');
                    loadInventory();
                } else {
                    Swal.fire('Error', resp.message, 'error');
                }
            }, 'json');
        }
    })
};

// Profile Logic
window.openProfileModal = function () {
    console.log('Opening Profile Modal...');
    $.post('actions.php', { action: 'get_profile' }, function (resp) {
        if (resp.status === 'success') {
            var u = resp.data;
            if (!u) {
                Swal.fire('Error', 'No se pudieron cargar los datos del perfil.', 'error');
                return;
            }
            $('#p_username').val(u.username);
            $('#p_email').val(u.email);
            $('#p_phone').val(u.phone);
            $('#profileRole').text(u.role);
            if (u.photo) {
                $('#profilePreview').attr('src', u.photo);
            } else {
                $('#profilePreview').attr('src', 'https://via.placeholder.com/100');
            }
            $('#profileModal').modal('show');
        } else {
            console.error('Profile Error:', resp.message);
            Swal.fire('Error', 'No se pudo cargar el perfil: ' + resp.message, 'error');
        }
    }, 'json').fail(function (xhr, status, error) {
        console.error('AJAX Error:', error);
        console.log(xhr.responseText);
        Swal.fire('Error', 'Falló la conexión al servidor', 'error');
    });
};

window.saveProfile = function () {
    var formData = new FormData($('#profileForm')[0]);
    $.ajax({
        url: 'actions.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (resp) {
            if (resp.status === 'success') {
                Swal.fire('Perfil Actualizado', resp.message, 'success');
                $('#profileModal').modal('hide');
                // Optional: reload to see header image change
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire('Error', resp.message, 'error');
            }
        },
        dataType: 'json'
    });
};

// Sales Logic
window.openSaleModal = function () {
    $('#saleSearchTerm').val('');
    $('#saleProductInfo').addClass('d-none');
    $('#btnConfirmSale').prop('disabled', true);
    $('#saleModal').modal('show');
    setTimeout(() => $('#saleSearchTerm').focus(), 500);
};

window.searchSaleProduct = function () {
    var term = $('#saleSearchTerm').val();
    if (!term) return;

    $.post('actions.php', { action: 'search_product_for_sale', term: term }, function (resp) {
        if (resp.status === 'success') {
            var p = resp.data;
            $('#saleProductId').val(p.id);
            $('#saleProductName').text(p.name + ' (' + p.size + ')');
            $('#saleProductPrice').text('$' + parseFloat(p.sale_price).toFixed(2));
            $('#saleProductStock').text(p.stock);

            $('#saleProductInfo').removeClass('d-none');
            $('#btnConfirmSale').prop('disabled', false);
            $('#saleQuantity').val(1).focus().select();
        } else {
            $('#saleProductInfo').addClass('d-none');
            $('#btnConfirmSale').prop('disabled', true);
            Swal.fire('Error', resp.message, 'error');
            $('#saleSearchTerm').select();
        }
    }, 'json');
};

window.confirmSale = function () {
    var id = $('#saleProductId').val();
    var qty = $('#saleQuantity').val();

    $.post('actions.php', { action: 'register_sale', product_id: id, quantity: qty }, function (resp) {
        if (resp.status === 'success') {
            $('#saleModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: '¡Venta Registrada!',
                text: resp.message,
                timer: 2000,
                showConfirmButton: false
            });
            loadInventory();
        } else {
            Swal.fire('Error', resp.message, 'error');
        }
    }, 'json');
};


function saveProduct() {
    // Usamos FormData para poder enviar archivos (imágenes)
    var form = document.getElementById('productForm');
    var formData = new FormData(form);

    // Agregamos la acción manualmente porque FormData solo toma los inputs
    formData.append('action', 'save_product');

    $.ajax({
        url: 'actions.php', // Ahora todo va a este archivo único
        type: 'POST',
        data: formData,
        contentType: false, // Importante para subir archivos
        processData: false, // Importante para subir archivos
        success: function (response) {
            if (response.status == 'success') {
                Swal.fire('¡Éxito!', response.message, 'success');
                $('#productModal').modal('hide');
                $('#productsTable').DataTable().ajax.reload(); // Recargar tabla
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Error de conexión con el servidor', 'error');
        }
    });
}

// User Management Logic
window.openUsersModal = function () {
    $('#usersModal').modal('show');
    loadUsers();
};

window.loadUsers = function () {
    $.post('actions.php', { action: 'get_users' }, function (resp) {
        if (resp.status === 'success') {
            var tbody = $('#usersTableBody');
            tbody.empty();
            resp.data.forEach(function (u) {
                var btn = `<button class="btn btn-sm btn-danger py-0" onclick="deleteUser(${u.id})"><i class="fas fa-trash"></i></button>`;
                tbody.append(`
                    <tr>
                        <td>${u.id}</td>
                        <td>${u.username}</td>
                        <td><span class="badge bg-${u.role === 'admin' ? 'primary' : 'secondary'}">${u.role}</span></td>
                        <td class="text-end">${btn}</td>
                    </tr>
                `);
            });
        }
    }, 'json');
};

window.createUser = function () {
    var u = $('#nu_username').val();
    var p = $('#nu_password').val();
    var r = $('#nu_role').val();

    if (!u || !p) return Swal.fire('Error', 'Completa los campos', 'warning');

    $.post('actions.php', { action: 'create_user', username: u, password: p, role: r }, function (resp) {
        if (resp.status === 'success') {
            $('#nu_username').val('');
            $('#nu_password').val('');
            loadUsers();
            Swal.fire('Creado', resp.message, 'success');
        } else {
            Swal.fire('Error', resp.message, 'error');
        }
    }, 'json');
};

window.deleteUser = function (id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    }).then((r) => {
        if (r.isConfirmed) {
            $.post('actions.php', { action: 'delete_user', id: id }, function (resp) {
                if (resp.status === 'success') {
                    loadUsers();
                    Swal.fire('Eliminado', resp.message, 'success');
                } else {
                    Swal.fire('Error', resp.message, 'error');
                }
            }, 'json');
        }
    });
};