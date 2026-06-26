<?php
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/admin-functions.php';

admin_require_login();

$products = admin_get_all_products($db);
$productId = (int)($_GET['edit'] ?? 0);
$currentProduct = $productId > 0 ? admin_get_product($db, $productId) : null;

$success = '';
$error = '';

// Crear o actualizar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'price' => (float)($_POST['price'] ?? 0),
        'weight' => trim($_POST['weight'] ?? ''),
        'badge' => trim($_POST['badge'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'image' => trim($_POST['image'] ?? ''),
    ];

    if (empty($data['name']) || $data['price'] <= 0) {
        $error = 'Por favor completa todos los campos correctamente';
    } else {
        if ($productId > 0) {
            if (admin_update_product($db, $productId, $data)) {
                $success = 'Producto actualizado correctamente';
                $currentProduct = null;
                $products = admin_get_all_products($db);
            } else {
                $error = 'Error al actualizar el producto';
            }
        } else {
            if (admin_create_product($db, $data)) {
                $success = 'Producto creado correctamente';
                $products = admin_get_all_products($db);
            } else {
                $error = 'Error al crear el producto';
            }
        }
    }
}

// Eliminar producto
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if (admin_delete_product($db, $deleteId)) {
        $success = 'Producto eliminado correctamente';
        $products = admin_get_all_products($db);
    } else {
        $error = 'Error al eliminar el producto';
    }
}

$pageTitle = 'Productos - Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #e1e4e8;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .navbar h1 {
            font-size: 18px;
            color: #333;
        }

        .navbar a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .navbar a:hover {
            background: #f0f2f5;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            width: 200px;
            height: calc(100vh - 60px);
            background: white;
            border-right: 1px solid #e1e4e8;
            padding: 20px 0;
            overflow-y: auto;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .sidebar a.active {
            background: #f0f2f5;
            color: #333;
            border-left-color: #667eea;
        }

        .main-content {
            margin-left: 200px;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header h2 {
            font-size: 24px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-grid.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table thead {
            background: #f5f7fa;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e1e4e8;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e1e4e8;
        }

        table tbody tr:hover {
            background: #f9f9f9;
        }

        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            object-fit: cover;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-edit {
            background: #667eea;
            color: white;
        }

        .btn-edit:hover {
            background: #5568d3;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .back-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar a {
                padding: 12px 10px;
                text-align: center;
            }

            .main-content {
                margin-left: 60px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🥩 <?php echo htmlspecialchars($store['name']); ?></h1>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="pedidos.php">Pedidos</a>
            <a href="pagos.php">Pagos</a>
            <a href="logout.php">Salir</a>
        </div>
    </div>

    <div class="sidebar">
        <a href="index.php">📊 Dashboard</a>
        <a href="pedidos.php">📦 Pedidos</a>
        <a href="productos.php" class="active">🛒 Productos</a>
        <a href="pagos.php">💳 Pagos</a>
        <a href="facturas.php">📄 Facturas</a>
    </div>

    <div class="main-content">
        <?php if ($currentProduct): ?>
            <!-- EDITAR PRODUCTO -->
            <a href="productos.php" class="back-link">← Volver a productos</a>
            <div class="page-header">
                <h2>Editar Producto</h2>
            </div>

            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="section">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nombre del Producto</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($currentProduct['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Categoría</label>
                            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($currentProduct['category']); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="price">Precio (€)</label>
                            <input type="number" id="price" name="price" step="0.01" value="<?php echo $currentProduct['price']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="weight">Peso</label>
                            <input type="text" id="weight" name="weight" value="<?php echo htmlspecialchars($currentProduct['weight']); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="badge">Badge (Etiqueta)</label>
                            <input type="text" id="badge" name="badge" value="<?php echo htmlspecialchars($currentProduct['badge']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="image">URL de Imagen</label>
                            <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($currentProduct['image']); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid full">
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea id="description" name="description" required><?php echo htmlspecialchars($currentProduct['description']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Guardar Cambios</button>
                        <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- CREAR O LISTAR PRODUCTOS -->
            <div class="page-header">
                <h2>Productos</h2>
                <button class="btn-primary" onclick="document.getElementById('formContainer').style.display = document.getElementById('formContainer').style.display === 'none' ? 'block' : 'none'">+ Nuevo Producto</button>
            </div>

            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div id="formContainer" class="section" style="display: none; margin-bottom: 20px;">
                <h3 style="margin-bottom: 20px;">Crear Nuevo Producto</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nombre del Producto</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Categoría</label>
                            <input type="text" id="category" name="category" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="price">Precio (€)</label>
                            <input type="number" id="price" name="price" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="weight">Peso</label>
                            <input type="text" id="weight" name="weight" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="badge">Badge (Etiqueta)</label>
                            <input type="text" id="badge" name="badge" required>
                        </div>

                        <div class="form-group">
                            <label for="image">URL de Imagen</label>
                            <input type="text" id="image" name="image" required>
                        </div>
                    </div>

                    <div class="form-grid full">
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Crear Producto</button>
                    </div>
                </form>
            </div>

            <div class="section">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Peso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image"></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td><?php echo number_format($product['price'], 2, ',', '.'); ?>€</td>
                            <td><?php echo htmlspecialchars($product['weight']); ?></td>
                            <td>
                                <a href="?edit=<?php echo $product['id']; ?>" class="btn-edit">Editar</a>
                                <a href="?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
