<?php
// actions.php
// session_start(); // Handled in config.php via db_connect
ob_start(); // Start buffering to catch any stray whitespace/notices
error_reporting(0); // Suppress warnings/notices that break JSON
require_once 'db_connect.php';
ob_end_clean(); // Discard any prior output (whitespace, BOM, notices)

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
file_put_contents('debug_log.txt', "Action: $action | Session ID: " . session_id() . " | User: " . ($_SESSION['user_id'] ?? 'None') . "\n", FILE_APPEND);

// Public Actions (No Auth Required)
if ($action === 'login') {
    $username = trim($_POST['username'] ?? ''); // User changed to 'email' in their edit, but let's stick to username as perDB or check both
// Actually, user edit used 'email' in login case: "$email = $_POST['email'];".
// And query "SELECT * FROM users WHERE email = ?".
// But original DB had 'username'. Let's support 'username' consistent with `login.php`.
// login.php sends `username`. So I MUST use `username`.

    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['status' => 'success', 'message' => 'Login exitoso']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Credenciales incorrectas']);
    }
    exit;
}

// Auth Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$isAdmin = ($_SESSION['role'] === 'admin');

switch ($action) {
    case 'list': // JS calls 'list', not 'list_products'
        try {
            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
            $products = $stmt->fetchAll();
            file_put_contents('debug_log.txt', "Found Products: " . count($products) . "\n", FILE_APPEND);

            $totalCost = 0;
            $totalValue = 0;
            $totalStock = 0;
            foreach ($products as &$p) {
                $totalCost += ($p['cost_price'] * $p['stock']);
                $totalValue += ($p['sale_price'] * $p['stock']);
                $totalStock += $p['stock'];
                $p['formatted_price'] = formatCurrency($p['sale_price']);
                $p['formatted_cost'] = formatCurrency($p['cost_price']);
                $p['marketplaces'] = json_decode($p['marketplaces_data'] ?? '[]', true);
            }

            echo json_encode([
                'status' => 'success',
                'data' => $products,
                'role' => $_SESSION['role'], // Critical for JS RBAC
                'stats' => [ // Critical for JS Dashboard
                    'total_products' => count($products),
                    'total_cost' => formatCurrency($totalCost),
                    'total_value' => formatCurrency($totalValue),
                    'total_stock' => $totalStock
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'save_product':
        if (!$isAdmin)
            exit(json_encode(['status' => 'error', 'message' => 'Solo administradores']));

        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $brand = $_POST['brand'] ?? 'XX';
        $size = $_POST['size'] ?? 'UNI';
        $sku = $_POST['sku'] ?? '';
        $barcode = $_POST['barcode'] ?? null;
        $details = $_POST['details'] ?? '';
        $cost = $_POST['cost_price'] ?? 0;
        $price = $_POST['sale_price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $location = $_POST['warehouse_location'] ?? '';

        // Handle Marketplaces being sent as array by FormData
        $mps = [];
        if (isset($_POST['marketplaces']) && is_array($_POST['marketplaces'])) {
            $mps = $_POST['marketplaces'];
        }
        $marketplaces_data = json_encode($mps);

        // Auto SKU
        if (empty($sku))
            $sku = 'R-' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Dupe Check
        if ($barcode) {
            $check = $pdo->prepare("SELECT id FROM products WHERE barcode = ? AND id != ?");
            $check->execute([$barcode, $id ?: 0]);
            if ($check->fetch())
                exit(json_encode(['status' => 'error', 'message' => 'Código de barras duplicado']));
        }

        // Image Handling
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $uploadDir = 'uploads/products/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0755, true);
                $newFilename = 'img_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFilename)) {
                    $imagePath = $uploadDir . $newFilename;
                    // Delete old
                    if (!empty($id)) {
                        $old = $pdo->prepare("SELECT image FROM products WHERE id=?");
                        $old->execute([$id]);
                        $oldImg = $old->fetchColumn();
                        if ($oldImg && file_exists($oldImg))
                            @unlink($oldImg);
                    }
                }
            }
        }

        try {
            if (empty($id)) {
                $sql = "INSERT INTO products (name, brand, size, details, cost_price, sale_price, stock, warehouse_location, sku,
barcode, marketplaces_data, image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
                $pdo->prepare($sql)->execute([
                    $name,
                    $brand,
                    $size,
                    $details,
                    $cost,
                    $price,
                    $stock,
                    $location,
                    $sku,
                    $barcode,
                    $marketplaces_data,
                    $imagePath
                ]);
                echo json_encode(['status' => 'success', 'message' => 'Producto creado']);
            } else {
                if ($imagePath) {
                    $sql = "UPDATE products SET name=?, brand=?, size=?, details=?, cost_price=?, sale_price=?, stock=?,
warehouse_location=?, sku=?, barcode=?, marketplaces_data=?, image=? WHERE id=?";
                    $pdo->prepare($sql)->execute([
                        $name,
                        $brand,
                        $size,
                        $details,
                        $cost,
                        $price,
                        $stock,
                        $location,
                        $sku,
                        $barcode,
                        $marketplaces_data,
                        $imagePath,
                        $id
                    ]);
                } else {
                    $sql = "UPDATE products SET name=?, brand=?, size=?, details=?, cost_price=?, sale_price=?, stock=?,
warehouse_location=?, sku=?, barcode=?, marketplaces_data=? WHERE id=?";
                    $pdo->prepare($sql)->execute([
                        $name,
                        $brand,
                        $size,
                        $details,
                        $cost,
                        $price,
                        $stock,
                        $location,
                        $sku,
                        $barcode,
                        $marketplaces_data,
                        $id
                    ]);
                }
                echo json_encode(['status' => 'success', 'message' => 'Producto actualizado']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'delete':
        if (!$isAdmin)
            exit(json_encode(['status' => 'error', 'message' => 'Solo administradores']));
        $id = $_POST['id'] ?? 0;
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Producto eliminado']);
        break;

    case 'get_product':
        $id = $_GET['id'] ?? 0;
        $p = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $p->execute([$id]);
        $prod = $p->fetch();
        if ($prod) {
            $prod['marketplaces'] = json_decode($prod['marketplaces_data'] ?? '[]', true);
            echo json_encode(['status' => 'success', 'data' => $prod]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No encontrado']);
        }
        break;

    // --- USER PROFILE & CONFIG ---
    case 'get_profile':
        try {
            if (!isset($_SESSION['user_id']))
                throw new Exception('Sesión no válida');
            $s = $pdo->prepare("SELECT username, email, phone, role, photo FROM users WHERE id = ?");
            $s->execute([$_SESSION['user_id']]);
            $data = $s->fetch();
            if (!$data)
                throw new Exception('Usuario no encontrado');
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'update_profile':
        // NOTE: Username is NOT updated here to prevent identity confusion
        $e = $_POST['email'] ?? '';
        $ph = $_POST['phone'] ?? '';
        $np = trim($_POST['new_password'] ?? '');
        $id = $_SESSION['user_id'];

        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $dest = "uploads/profile_" . $id . "_" . time() . "." . $ext;
                if (!is_dir('uploads'))
                    mkdir('uploads', 0755, true);
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest))
                    $photoPath = $dest;
            }
        }

        try {
            $sql = "UPDATE users SET email=?, phone=?";
            $params = [$e, $ph];

            if ($photoPath) {
                $sql .= ", photo=?";
                $params[] = $photoPath;
            }

            if (!empty($np)) {
                $sql .= ", password=?";
                $params[] = password_hash($np, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id=?";
            $params[] = $id;

            $pdo->prepare($sql)->execute($params);

            echo json_encode(['status' => 'success', 'message' => 'Perfil actualizado']);
        } catch (Exception $ex) {
            echo json_encode(['status' => 'error', 'message' => $ex->getMessage()]);
        }
        break;

    // --- USER MANAGEMENT (ADMIN) ---
    case 'get_users':
        if (!$isAdmin)
            exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));
        $stmt = $pdo->query("SELECT id, username, role FROM users ORDER BY id ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        break;

    case 'create_user':
        if (!$isAdmin)
            exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));
        $u = trim($_POST['username'] ?? '');
        $p = $_POST['password'] ?? '';
        $r = $_POST['role'] ?? 'user';

        if (empty($u) || empty($p))
            exit(json_encode(['status' => 'error', 'message' => 'Faltan datos']));

        // Check Dupe
        $check = $pdo->prepare("SELECT id FROM users WHERE username=?");
        $check->execute([$u]);
        if ($check->fetch())
            exit(json_encode(['status' => 'error', 'message' => 'El usuario ya existe']));

        $hash = password_hash($p, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?,?,?)")->execute([$u, $hash, $r]);
        echo json_encode(['status' => 'success', 'message' => 'Usuario creado']);
        break;

    case 'delete_user':
        if (!$isAdmin)
            exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));
        $id = $_POST['id'];
        if ($id == $_SESSION['user_id'])
            exit(json_encode(['status' => 'error', 'message' => 'No puedes eliminarte a ti mismo']));

        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado']);
        break;

    // --- SALES ---
    case 'search_product_for_sale':
        $t = $_POST['term'];
        $s = $pdo->prepare("SELECT * FROM products WHERE sku = ? OR barcode = ? LIMIT 1");
        $s->execute([$t, $t]);
        $prod = $s->fetch();
        if ($prod && $prod['stock'] > 0)
            echo json_encode(['status' => 'success', 'data' => $prod]);
        else
            echo json_encode(['status' => 'error', 'message' => 'No encontrado o sin stock']);
        break;

    case 'register_sale':
        $pid = $_POST['product_id'];
        $qty = (int) $_POST['quantity'];
        $mp = $_POST['marketplace'] ?? 'Local';

        $s = $pdo->prepare("SELECT stock, sale_price FROM products WHERE id = ?");
        $s->execute([$pid]);
        $p = $s->fetch();
        if (!$p || $p['stock'] < $qty)
            exit(json_encode(['status' => 'error', 'message' => 'Stock insuficiente']));

        $total = $p['sale_price'] * $qty;
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE products SET stock = stock - ?, last_sold_at = NOW() WHERE id = ?")->execute([$qty, $pid]);
            $pdo->prepare("INSERT INTO sales (product_id, quantity, total, marketplace) VALUES (?, ?, ?, ?)")->execute([
                $pid,
                $qty,
                $total,
                $mp
            ]);
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Venta registrada']);
        } catch (Exception $ex) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $ex->getMessage()]);
        }
        break;

    default:
        // Fallback for list action mismatch if any
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida: ' . $action]);
        break;
}


?>