<?php
// ==========================================
// 1. CONEXIÓN A BASE DE DATOS (SQLite)
// ==========================================
$db = new PDO('sqlite:inventario.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS productos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo TEXT NOT NULL,
    nombre TEXT NOT NULL,
    categoria TEXT NOT NULL,
    precio REAL NOT NULL,
    stock INTEGER NOT NULL
)");

// ==========================================
// 2. LÓGICA CRUD
// ==========================================
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['accion']) && $_POST['accion'] === 'crear') {
            $stmt = $db->prepare("INSERT INTO productos (codigo, nombre, categoria, precio, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['codigo'], $_POST['nombre'], $_POST['categoria'], $_POST['precio'], $_POST['stock']]);
            $mensaje = "<div class='alert alert-success'>✅ Producto registrado exitosamente en el inventario.</div>";
        } elseif (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
            $stmt = $db->prepare("UPDATE productos SET codigo=?, nombre=?, categoria=?, precio=?, stock=? WHERE id=?");
            $stmt->execute([$_POST['codigo'], $_POST['nombre'], $_POST['categoria'], $_POST['precio'], $_POST['stock'], $_POST['id']]);
            $mensaje = "<div class='alert alert-success'>✅ Registro actualizado correctamente.</div>";
        } elseif (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
            $stmt = $db->prepare("DELETE FROM productos WHERE id=?");
            $stmt->execute([$_POST['id']]);
            $mensaje = "<div class='alert alert-danger'>🗑️ Producto eliminado de la base de datos.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

$vista = isset($_GET['vista']) ? $_GET['vista'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetStock Pro | Dashboard</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        
        body { 
            background-color: #f1f5f9; 
            margin: 0; 
            color: #334155; 
        }

        /* Navbar */
        .navbar {
            background-color: var(--dark);
            color: white;
            padding: 0 2rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .navbar-brand { font-size: 1.4rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .navbar-menu { display: flex; gap: 15px; }
        
        .nav-link {
            color: #cbd5e1;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }

        .team-badge {
            background: rgba(255,255,255,0.1);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        /* Main Content */
        .main-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 30px;
            border: 1px solid var(--border);
        }

        .card-header {
            border-bottom: 1px solid var(--border);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        h2 { margin: 0; color: var(--dark); font-size: 1.5rem; }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Forms */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full-width { grid-column: span 2; }
        
        label { font-weight: 600; font-size: 0.9rem; color: var(--dark); }
        
        input, select {
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-hover); transform: translateY(-1px); }
        
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; border-radius: 6px; }
        .btn-warning { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .btn-warning:hover { background-color: #fef3c7; }
        .btn-danger { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .btn-danger:hover { background-color: #fee2e2; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background-color: var(--light); color: var(--gray); font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        tr:hover { background-color: #f8fafc; }
        
        .stock-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            background-color: #f1f5f9;
        }
        .stock-ok { background-color: #dcfce7; color: #166534; }
        .stock-low { background-color: #fef3c7; color: #b45309; }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
            background: var(--light);
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">🐾 PetStock Pro</div>
        <div class="navbar-menu">
            <a href="index.php?vista=dashboard" class="nav-link <?= $vista == 'dashboard' ? 'active' : '' ?>">Panel General</a>
            <a href="index.php?vista=crear" class="nav-link <?= $vista == 'crear' ? 'active' : '' ?>">Ingresar Producto</a>
            <a href="index.php?vista=leer" class="nav-link <?= $vista == 'leer' || $vista == 'editar' ? 'active' : '' ?>">Gestionar Inventario</a>
        </div>
        <div class="team-badge">Alex Hernandez & Jullyana Rebolledo</div>
    </nav>

    <div class="main-container">
        <?= $mensaje ?>

        <?php if ($vista === 'dashboard'): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Bienvenido al Sistema Operativo</h2>
                </div>
                <p style="color: var(--gray); font-size: 1.1rem; line-height: 1.6;">
                    Este panel permite administrar el inventario de la tienda de mascotas conectado a la base de datos local SQLite. 
                    Utilice la barra de navegación superior para acceder a las operaciones CRUD.
                </p>
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <a href="index.php?vista=crear" class="btn btn-primary">➕ Registrar Nuevo Producto</a>
                    <a href="index.php?vista=leer" class="btn" style="background: var(--light); color: var(--dark); border: 1px solid var(--border);">📖 Ver Catálogo</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($vista === 'crear'): ?>
            <div class="card" style="max-width: 800px; margin: 0 auto;">
                <div class="card-header">
                    <h2>Registrar Nuevo Producto</h2>
                </div>
                <form action="index.php?vista=leer" method="POST">
                    <input type="hidden" name="accion" value="crear">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Código de Barras</label>
                            <input type="text" name="codigo" placeholder="Ej. 7421098" required>
                        </div>
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria" required>
                                <option value="">Seleccione una opción...</option>
                                <option value="Alimentos">Alimentos</option>
                                <option value="Accesorios">Accesorios</option>
                                <option value="Farmacia">Farmacia</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label>Nombre del Producto</label>
                            <input type="text" name="nombre" placeholder="Ej. Saco Alimento Perro 15kg" required>
                        </div>
                        <div class="form-group">
                            <label>Precio de Venta ($)</label>
                            <input type="number" name="precio" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Stock Inicial (Unidades)</label>
                            <input type="number" name="stock" placeholder="0" required>
                        </div>
                    </div>
                    <div style="margin-top: 25px; text-align: right;">
                        <button type="submit" class="btn btn-primary">💾 Guardar Registro</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($vista === 'leer'): ?>
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Inventario Actual</h2>
                    <a href="index.php?vista=crear" class="btn btn-primary btn-sm">➕ Nuevo</a>
                </div>
                
                <?php
                $productos = $db->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                if (count($productos) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td><code style="background: var(--light); padding: 4px 8px; border-radius: 4px; color: var(--gray);"><?= htmlspecialchars($p['codigo']) ?></code></td>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td><span style="color: var(--gray);"><?= htmlspecialchars($p['categoria']) ?></span></td>
                                    <td style="font-weight: 600;">$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php $stockClass = $p['stock'] > 5 ? 'stock-ok' : 'stock-low'; ?>
                                        <span class="stock-badge <?= $stockClass ?>"><?= htmlspecialchars($p['stock']) ?> un.</span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="index.php?vista=editar&id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                                        <form action="index.php?vista=leer" method="POST" style="display:inline;">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Confirma eliminar este producto definitivamente de la base de datos?');">🗑️ Borrar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 15px;">📦</div>
                        <h3>Base de datos vacía</h3>
                        <p>Aún no hay productos registrados en el inventario.</p>
                        <a href="index.php?vista=crear" class="btn btn-primary" style="margin-top: 15px;">Crear el primer registro</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php 
        if ($vista === 'editar' && isset($_GET['id'])): 
            $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($prod):
        ?>
            <div class="card" style="max-width: 800px; margin: 0 auto; border-top: 4px solid var(--warning);">
                <div class="card-header">
                    <h2>✏️ Modificar Producto (ID: <?= $prod['id'] ?>)</h2>
                </div>
                <form action="index.php?vista=leer" method="POST">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Código de Barras</label>
                            <input type="text" name="codigo" value="<?= htmlspecialchars($prod['codigo']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria" required>
                                <option value="Alimentos" <?= $prod['categoria'] == 'Alimentos' ? 'selected' : '' ?>>Alimentos</option>
                                <option value="Accesorios" <?= $prod['categoria'] == 'Accesorios' ? 'selected' : '' ?>>Accesorios</option>
                                <option value="Farmacia" <?= $prod['categoria'] == 'Farmacia' ? 'selected' : '' ?>>Farmacia</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label>Nombre del Producto</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($prod['nombre']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Precio de Venta ($)</label>
                            <input type="number" name="precio" value="<?= htmlspecialchars($prod['precio']) ?>" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Stock Actual</label>
                            <input type="number" name="stock" value="<?= htmlspecialchars($prod['stock']) ?>" required>
                        </div>
                    </div>
                    <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 15px;">
                        <a href="index.php?vista=leer" class="btn" style="background: var(--light); color: var(--dark); border: 1px solid var(--border);">Cancelar</a>
                        <button type="submit" class="btn btn-warning" style="color: #b45309; border: none;">Actualizar Datos</button>
                    </div>
                </form>
            </div>
        <?php 
            endif; 
        endif; 
        ?>
    </div>

</body>
</html>