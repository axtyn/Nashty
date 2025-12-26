<?php
// index.php
require_once 'config.php';
require_once 'db_connect.php'; // Required for $pdo in header
// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Panel</title>
    <!-- Fonts & Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="assets/style.css" rel="stylesheet">
</head>

<body>
    <script>
        // Apply saved theme immediately to prevent flash
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <header class="dashboard-header sticky-top">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-cube fa-lg text-white-50"></i>
                <h4 class="mb-0 brand-text"><?php echo APP_NAME; ?></h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Theme Toggle -->
                <button type="button" class="btn btn-light rounded-circle shadow-sm" id="themeToggle"
                    style="width: 40px; height: 40px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="dropdown">
                    <button
                        class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3"
                        type="button" data-bs-toggle="dropdown">
                        <!-- User Photo -->
                        <?php
                        // Fetch fresh photo
                        $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $u = $stmt->fetch();
                        $photoUrl = $u['photo'] ? $u['photo'] : 'https://ui-avatars.com/api/?name=' . $_SESSION['username'];
                        ?>
                        <img src="<?php echo $photoUrl; ?>" alt="User" class="rounded-circle"
                            style="width: 30px; height: 30px; object-fit: cover;">

                        <span><?php echo $_SESSION['username']; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li>
                            <h6 class="dropdown-header">Cuenta</h6>
                        </li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="#" onclick="openUsersModal()"><i
                                        class="fas fa-users me-2"></i>Usuarios</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="#" onclick="openProfileModal()"><i
                                    class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="login.php?logout=1"><i
                                    class="fas fa-sign-out-alt me-2"></i>Salir</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4 pb-5">

        <!-- Top Cards (Expert Req) -->
        <div class="row g-4 mb-5">
            <!-- Purple -->
            <div class="col-md-4">
                <div class="stat-card purple p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon bg-purple-soft">
                            <i class="fas fa-tshirt"></i>
                        </div>
                    </div>
                    <h3 class="stat-value" id="stat-total-stock">0</h3>
                    <span class="stat-label">Total Inventario (Unidades)</span>
                </div>
            </div>
            <!-- Green -->
            <div class="col-md-4">
                <div class="stat-card green p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon bg-green-soft">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <h3 class="stat-value text-success" id="stat-total-cost">$0</h3>
                    <span class="stat-label">Costo Inversión</span>
                </div>
            </div>
            <!-- Blue -->
            <div class="col-md-4">
                <div class="stat-card blue p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon bg-blue-soft">
                            <i class="fas fa-tag"></i>
                        </div>
                    </div>
                    <h3 class="stat-value text-primary" id="stat-total-value">$0</h3>
                    <span class="stat-label">Valor Mercado</span>
                </div>
            </div>
        </div>

        <!-- Inventory Section -->
        <div class="custom-table-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Inventario</h4>
                    <p class="text-muted mb-0 small">Gestión completa de productos y existencias</p>
                </div>
                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button class="btn btn-success d-flex align-items-center gap-2 px-4 py-2 shadow-sm rounded-pill"
                        onclick="openSaleModal()">
                        <i class="fas fa-shopping-cart"></i> Vender
                    </button>
                    <button class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 shadow-sm rounded-pill"
                        id="btnAddProduct" onclick="openProductModal()">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="inventoryTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Talla</th>
                            <th>Precio Venta</th>
                            <th>Stock</th>
                            <th>Ubicación</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Product Modal (Expert Req: Dynamic Marketplaces) -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm">
                        <input type="hidden" name="action" value="save_product">
                        <input type="hidden" name="id" id="productId">

                        <!-- Row 1 -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">SKU (Auto)</label>
                                <input type="text" class="form-control bg-light" name="sku" id="sku"
                                    placeholder="Dejar vacío para auto-generar">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Código de Barras</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control" name="barcode" id="barcode">
                                </div>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="mb-3">
                            <label class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>

                        <!-- Row 3 -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Marca</label>
                                <input type="text" class="form-control" name="brand" id="brand">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Talla</label>
                                <select class="form-select" name="size" id="size">
                                    <option value="UNI">UNI</option>
                                    <option value="XS">XS</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ubicación</label>
                                <input type="text" class="form-control" name="warehouse_location"
                                    id="warehouse_location">
                            </div>
                        </div>

                        <!-- Row 4 Pricing -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-success">Costo Compra</label>
                                <input type="number" step="0.01" class="form-control border-success" name="cost_price"
                                    id="cost_price">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-primary">Precio Venta</label>
                                <input type="number" step="0.01" class="form-control border-primary" name="sale_price"
                                    id="sale_price">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Actual</label>
                                <input type="number" class="form-control" name="stock" id="stock">
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3 fw-bold"><i class="fas fa-globe me-2"></i>Marketplaces</h6>
                        <!-- Dynamic Marketplaces -->
                        <div id="marketplacesContainer">
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input mp-check" type="checkbox" id="check_ml"
                                            data-target="input_ml">
                                        <label class="form-check-label" for="check_ml">Mercado Libre</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control d-none mp-input" id="input_ml"
                                        placeholder="Link / ID">
                                </div>
                            </div>
                            <!-- Tiendanube -->
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input mp-check" type="checkbox" id="check_tn"
                                            data-target="input_tn">
                                        <label class="form-check-label" for="check_tn">Tiendanube</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control d-none mp-input" id="input_tn"
                                        placeholder="Link / ID">
                                </div>
                            </div>
                            <!-- Facebook -->
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input mp-check" type="checkbox" id="check_fb"
                                            data-target="input_fb">
                                        <label class="form-check-label" for="check_fb">Facebook</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control d-none mp-input" id="input_fb"
                                        placeholder="Link / ID">
                                </div>
                            </div>
                            <!-- Go Trendier -->
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input mp-check" type="checkbox" id="check_gt"
                                            data-target="input_gt">
                                        <label class="form-check-label" for="check_gt">Go Trendier</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control d-none mp-input" id="input_gt"
                                        placeholder="Link / ID">
                                </div>
                            </div>
                            <!-- Shein -->
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input mp-check" type="checkbox" id="check_sh"
                                            data-target="input_sh">
                                        <label class="form-check-label" for="check_sh">Shein</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control d-none mp-input" id="input_sh"
                                        placeholder="Link / ID">
                                </div>
                            </div>
                            <!-- Tik Tok -->
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input mp-check" type="checkbox" id="check_tt"
                                            data-target="input_tt">
                                        <label class="form-check-label" for="check_tt">Tik Tok</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control d-none mp-input" id="input_tt"
                                        placeholder="Link / ID">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input mp-check" type="checkbox" id="check_web"
                                            data-target="input_web">
                                        <label class="form-check-label" for="check_web">Página Web</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control d-none mp-input" id="input_web"
                                        placeholder="ID Publicación / Link">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary px-4" onclick="saveProduct()">Guardar Producto</button>
                </div>
            </div>
        </div>
    </div>

    </div>

    <!-- Sales Modal -->
    <div class="modal fade" id="saleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Registrar Venta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Buscar Producto (SKU o Código)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="saleSearchTerm"
                                placeholder="Escribe y presiona Enter">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchSaleProduct()"><i
                                    class="fas fa-search"></i></button>
                        </div>
                    </div>

                    <div id="saleProductInfo" class="d-none border rounded p-3 bg-light mb-3">
                        <input type="hidden" id="saleProductId">
                        <h6 class="fw-bold" id="saleProductName">Producto</h6>
                        <div class="d-flex justify-content-between">
                            <span>Precio: <strong class="text-primary" id="saleProductPrice">$0</strong></span>
                            <span>Stock: <strong class="text-danger" id="saleProductStock">0</strong></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="saleQuantity" value="1" min="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Marketplace / Origen</label>
                        <select class="form-select" id="saleMarketplace">
                            <option value="Mercado Libre">Mercado Libre</option>
                            <option value="Tiendanube">Tiendanube</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Go Trendier">Go Trendier</option>
                            <option value="Shein">Shein</option>
                            <option value="Tik Tok">Tik Tok</option>
                            <option value="Local">Local / Otro</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="confirmSale()" id="btnConfirmSale"
                        disabled>Confirmar Venta</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Configuración de Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="profileForm" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="text-center mb-4">
                            <img src="https://via.placeholder.com/100" id="profilePreview"
                                class="rounded-circle border mb-2"
                                style="width: 100px; height: 100px; object-fit: cover;">
                            <br>
                            <span class="badge bg-secondary" id="profileRole">Rol</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario (No editable)</label>
                            <input type="text" class="form-control bg-light" name="username" id="p_username" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="p_email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="phone" id="p_phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto de Perfil</label>
                            <input type="file" class="form-control" name="photo" id="p_photo" accept="image/*">
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Cambiar Contraseña</h6>
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" name="new_password"
                                placeholder="Dejar en blanco para no cambiar">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="saveProfile()">Actualizar Perfil</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Modal (Admin Only) -->
    <div class="modal fade" id="usersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Gestión de Usuarios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Left: List -->
                        <div class="col-md-7">
                            <h6 class="fw-bold">Usuarios Existentes</h6>
                            <div class="table-responsive border rounded" style="max-height: 300px; overflow-y:auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Right: Form -->
                        <div class="col-md-5">
                            <h6 class="fw-bold border-bottom pb-2">Crear Nuevo Usuario</h6>
                            <form id="newUserForm">
                                <div class="mb-2">
                                    <label class="form-label small">Nombre de Usuario</label>
                                    <input type="text" class="form-control form-control-sm" id="nu_username" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Contraseña</label>
                                    <input type="password" class="form-control form-control-sm" id="nu_password"
                                        required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Rol</label>
                                    <select class="form-select form-select-sm" id="nu_role">
                                        <option value="user">Usuario (Solo Lectura/Ventas)</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-success w-100 mt-2" onclick="createUser()">
                                    <i class="fas fa-plus me-1"></i> Crear Usuario
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <p class="mb-0"><strong>Frappe Digital Studio</strong> &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        var currentUserRole = "<?php echo $_SESSION['role'] ?? 'user'; ?>";
    </script>
    <script src="assets/script.js?v=<?php echo time(); ?>_v3"></script>
</body>

</html>