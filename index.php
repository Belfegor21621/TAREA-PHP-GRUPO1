<?php
session_start();
// HORA EXACTA DE CHILE PARA LA BITÁCORA (Punto 7.1)
date_default_timezone_set('America/Santiago');

// ==========================================
// 1. BASE DE DATOS Y RESTRICCIONES (Punto 6)
// ==========================================
$db = new PDO('sqlite:sistema_pro.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tabla Usuarios (Incluye EMAIL)
$db->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
)");

// Tabla Productos con Restricciones
$db->exec("CREATE TABLE IF NOT EXISTS productos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo TEXT UNIQUE NOT NULL,
    nombre TEXT NOT NULL,
    categoria TEXT NOT NULL,
    precio REAL CHECK(precio >= 0),
    stock INTEGER CHECK(stock >= 0)
)");

// Tabla Bitácora con la estructura EXACTA del Punto 7
$db->exec("CREATE TABLE IF NOT EXISTS bitacora (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha_hora TEXT,
    usuario TEXT,
    tipo_evento TEXT,
    detalle TEXT,
    ip_cliente TEXT
)");

// ==========================================
// 2. FUNCIÓN DE AUDITORÍA / LOG (Punto 7)
// ==========================================
function registrar_auditoria($db, $tipo, $detalle) {
    // Formato exacto: DD/MM/YYYY, HH:MM:SS
    $fecha = date('d/m/Y, H:i:s');
    $usr = $_SESSION['username'] ?? 'No Autenticado';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    $stmt = $db->prepare("INSERT INTO bitacora (fecha_hora, usuario, tipo_evento, detalle, ip_cliente) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$fecha, $usr, $tipo, $detalle, $ip]);
}

// ==========================================
// 3. PROCESAMIENTO CRUD Y AUTH
// ==========================================
$sys_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = $_POST['accion'] ?? '';

        // Registro con Hash y Correo
        if ($accion === 'registro') {
            $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['username'], $_POST['email'], $hash]);
            
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['email'] = $_POST['email'];
            registrar_auditoria($db, 'Creación de usuario', 'Nuevo usuario registrado con correo: ' . $_POST['email']);
            header("Location: index.php?v=dashboard"); exit();
        }
        
        // Login verificado por EMAIL
        elseif ($accion === 'login') {
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($_POST['password'], $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                registrar_auditoria($db, 'inicio de sesión', 'Autenticación exitosa en el sistema');
                header("Location: index.php?v=dashboard"); exit();
            } else {
                $sys_msg = "<div class='alert alert-danger'>❌ Credenciales incorrectas.</div>";
            }
        }

        // CRUD: CREATE
        elseif ($accion === 'crear') {
            $stmt = $db->prepare("INSERT INTO productos (codigo, nombre, categoria, precio, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['codigo'], $_POST['nombre'], $_POST['categoria'], $_POST['precio'], $_POST['stock']]);
            registrar_auditoria($db, 'crear registro', "Insert en tabla: productos | ID insertado");
            $sys_msg = "<div class='alert alert-success'>✅ Producto registrado con éxito.</div>";
        }

        // CRUD: UPDATE
        elseif ($accion === 'modificar') {
            $stmt = $db->prepare("UPDATE productos SET codigo=?, nombre=?, categoria=?, precio=?, stock=? WHERE id=?");
            $stmt->execute([$_POST['codigo'], $_POST['nombre'], $_POST['categoria'], $_POST['precio'], $_POST['stock'], $_POST['id']]);
            registrar_auditoria($db, 'modificar registro', "Update en tabla: productos | ID afectado: {$_POST['id']}");
            $sys_msg = "<div class='alert alert-success'>✅ Producto actualizado con éxito.</div>";
        }

        // CRUD: DELETE
        elseif ($accion === 'eliminar') {
            $stmt = $db->prepare("DELETE FROM productos WHERE id=?");
            $stmt->execute([$_POST['id']]);
            registrar_auditoria($db, 'eliminar registro', "Delete en tabla: productos | ID eliminado: {$_POST['id']}");
            $sys_msg = "<div class='alert alert-success'>🗑️ Producto eliminado de la base de datos.</div>";
        }
    } catch (Exception $e) {
        $sys_msg = "<div class='alert alert-danger'>⚠️ Error de Restricción/SQL: " . $e->getMessage() . "</div>";
    }
}

// Cierre de sesión y log
if (isset($_GET['v']) && $_GET['v'] === 'logout') {
    registrar_auditoria($db, 'cierre de sesión', 'Terminación voluntaria de sesión');
    session_destroy();
    header("Location: index.php"); exit();
}

$vista = $_GET['v'] ?? 'login';

// Registro de lectura en el Log
if ($vista === 'consultar' && isset($_SESSION['username'])) {
    registrar_auditoria($db, 'consultar registro', 'Visualización de registros en tabla: productos');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetStock Pro | Hito 3</title>
    <style>
        /* Variables de colores vibrantes y modernos */
        :root { 
            --primary: #6366f1; 
            --primary-hover: #4f46e5;
            --secondary: #ec4899;
            --dark: #0f172a; 
            --sidebar: #1e293b;
            --bg-light: #f8fafc;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', system-ui, sans-serif; }
        body { background-color: var(--bg-light); color: #334155; }
        
        /* LOGIN CON COLOR Y ESTILO MODERNOS */
        .login-bg { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px;
        }
        .login-card { 
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);
            padding: 40px; border-radius: 20px; width: 100%; max-width: 450px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); text-align: center;
        }
        .login-card h2 { color: var(--dark); font-size: 2rem; margin-bottom: 5px; }
        .login-card p { color: #64748b; margin-bottom: 30px; font-size: 0.95rem; }
        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; color: #475569;}
        .login-input { 
            width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px;
            font-size: 1rem; transition: 0.3s;
        }
        .login-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .btn-gradient {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white; border: none; padding: 14px; width: 100%; border-radius: 10px;
            font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: transform 0.2s; margin-top: 10px;
        }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }

        /* LAYOUT PRINCIPAL */
        .app-container { display: flex; min-height: 100vh; }
        
        /* SIDEBAR */
        .sidebar { width: 280px; background: var(--sidebar); color: white; display: flex; flex-direction: column; }
        .brand { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .brand h1 { font-size: 1.5rem; background: -webkit-linear-gradient(#fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-list { list-style: none; padding: 20px 15px; flex: 1; }
        .nav-item { margin-bottom: 8px; }
        .nav-link {
            display: flex; align-items: center; padding: 12px 20px; color: #94a3b8;
            text-decoration: none; border-radius: 10px; font-weight: 500; transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active { background: var(--primary); color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        
        /* CONTENIDO */
        .main-content { flex: 1; padding: 40px; background: #f1f5f9; overflow-y: auto; }
        .header-title { font-size: 2rem; color: var(--dark); margin-bottom: 30px; font-weight: 700; }
        
        /* DASHBOARD STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-left: 5px solid var(--primary); }
        .stat-title { color: #64748b; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; }
        .stat-value { font-size: 2.5rem; font-weight: 800; color: var(--dark); margin-top: 10px; }

        /* COMPONENTES */
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .full-width { grid-column: span 2; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 15px; text-align: left; font-size: 0.85rem; color: #64748b; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; background: #e0e7ff; color: #4338ca; }
        
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; color: white; text-decoration: none; display: inline-block;}
        .btn-primary { background: var(--primary); }
        .btn-warning { background: var(--warning); }
        .btn-danger { background: var(--danger); }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }
        
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['username'])): ?>
    <div class="login-bg">
        <div class="login-card">
            <h2>🐾 PetStock Pro</h2>
            <p>Sistema ERP Seguro - Hito 3</p>
            <?= $sys_msg ?>
            
            <?php if ($vista === 'signup'): ?>
                <form action="index.php" method="POST">
                    <input type="hidden" name="accion" value="registro">
                    <div class="input-group">
                        <label>👤 Nombre de Usuario</label>
                        <input type="text" name="username" class="login-input" required autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>✉️ Correo Electrónico</label>
                        <input type="email" name="email" class="login-input" required autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>🔒 Contraseña Segura</label>
                        <input type="password" name="password" class="login-input" required>
                    </div>
                    <button type="submit" class="btn-gradient">Crear Cuenta</button>
                    <a href="index.php" style="display:block; margin-top:20px; color:var(--primary); text-decoration:none; font-weight:600;">Ya tengo cuenta</a>
                </form>
            <?php else: ?>
                <form action="index.php" method="POST">
                    <input type="hidden" name="accion" value="login">
                    <div class="input-group">
                        <label>✉️ Correo Electrónico</label>
                        <input type="email" name="email" class="login-input" required autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>🔒 Contraseña</label>
                        <input type="password" name="password" class="login-input" required>
                    </div>
                    <button type="submit" class="btn-gradient">Ingresar al Sistema</button>
                    <a href="index.php?v=signup" style="display:block; margin-top:20px; color:var(--primary); text-decoration:none; font-weight:600;">Registrar un nuevo usuario</a>
                </form>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <div class="app-container">
        <aside class="sidebar">
            <div class="brand">
                <h1>PetStock Pro</h1>
                <p style="color: #cbd5e1; font-size: 0.9rem; margin-top: 10px; font-weight: 600;">Operador: <?= htmlspecialchars($_SESSION['username']) ?></p>
                <p style="color: #64748b; font-size: 0.75rem; margin-top: 2px;"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></p>
            </div>
            <ul class="nav-list">
                <li class="nav-item"><a href="index.php?v=dashboard" class="nav-link <?= $vista=='dashboard'?'active':'' ?>">📊 Panel Principal</a></li>
                <li class="nav-item"><a href="index.php?v=crear" class="nav-link <?= $vista=='crear'?'active':'' ?>">➕ Crear</a></li>
                <li class="nav-item"><a href="index.php?v=consultar" class="nav-link <?= $vista=='consultar'?'active':'' ?>">📖 Consultar</a></li>
                <li class="nav-item"><a href="index.php?v=modificar" class="nav-link <?= $vista=='modificar'?'active':'' ?>">✏️ Modificar</a></li>
                <li class="nav-item"><a href="index.php?v=eliminar" class="nav-link <?= $vista=='eliminar'?'active':'' ?>">🗑️ Eliminar</a></li>
                <li style="margin: 20px 0;"><hr style="border-color: rgba(255,255,255,0.05);"></li>
                <li class="nav-item"><a href="index.php?v=bitacora" class="nav-link <?= $vista=='bitacora'?'active':'' ?>">📋 Bitácora (Log)</a></li>
                <li class="nav-item"><a href="index.php?v=logout" class="nav-link" style="color: #fca5a5;">🚪 Cerrar sesión</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <?= $sys_msg ?>
            
            <?php if ($vista === 'dashboard'): 
                $total_prod = $db->query("SELECT COUNT(*) FROM productos")->fetchColumn();
                $total_stock = $db->query("SELECT SUM(stock) FROM productos")->fetchColumn() ?: 0;
                $total_logs = $db->query("SELECT COUNT(*) FROM bitacora")->fetchColumn();
            ?>
                <h1 class="header-title">Panel de Control</h1>
                <div class="stats-grid">
                    <div class="stat-card" style="border-color: var(--primary);">
                        <div class="stat-title">Total Productos en BD</div>
                        <div class="stat-value"><?= $total_prod ?></div>
                    </div>
                    <div class="stat-card" style="border-color: var(--success);">
                        <div class="stat-title">Unidades en Stock</div>
                        <div class="stat-value"><?= $total_stock ?></div>
                    </div>
                    <div class="stat-card" style="border-color: var(--warning);">
                        <div class="stat-title">Eventos Registrados</div>
                        <div class="stat-value"><?= $total_logs ?></div>
                    </div>
                </div>
                <div class="card">
                    <h3>Bienvenido al Hito 3</h3>
                    <p style="margin-top: 10px; color: #64748b; line-height: 1.6;">
                        El sistema está operativo con base de datos SQLite. Las contraseñas se almacenan mediante cifrado BCRYPT. Todas las acciones se registran automáticamente en la bitácora cumpliendo los estándares exigidos de auditoría.
                    </p>
                </div>

            <?php elseif ($vista === 'crear'): ?>
                <h1 class="header-title">Crear Nuevo Registro</h1>
                <div class="card">
                    <form action="index.php?v=crear" method="POST">
                        <input type="hidden" name="accion" value="crear">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>SKU / Código Único</label>
                                <input type="text" name="codigo" required>
                            </div>
                            <div class="form-group">
                                <label>Categoría del Producto</label>
                                <select name="categoria" required>
                                    <option value="">Seleccione una categoría...</option>
                                    <option value="Alimentos">Alimentos y Snacks</option>
                                    <option value="Accesorios">Accesorios y Correas</option>
                                    <option value="Farmacia">Farmacia y Medicamentos</option>
                                    <option value="Higiene">Higiene y Cuidado</option>
                                    <option value="Juguetes">Juguetes Interactivos</option>
                                    <option value="Ropa">Ropa para Mascotas</option>
                                    <option value="Camas">Camas y Muebles</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label>Descripción del Producto</label>
                                <input type="text" name="nombre" required>
                            </div>
                            <div class="form-group">
                                <label>Precio Unitario ($)</label>
                                <input type="number" name="precio" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label>Stock Inicial</label>
                                <input type="number" name="stock" min="0" required>
                            </div>
                        </div>
                        <div style="margin-top: 25px; text-align: right;">
                            <button type="submit" class="btn btn-primary">💾 Guardar Producto</button>
                        </div>
                    </form>
                </div>

            <?php elseif ($vista === 'consultar'): ?>
                <h1 class="header-title">Catálogo de Inventario</h1>
                <div class="card" style="padding: 0; overflow: hidden;">
                    <table style="margin: 0;">
                        <tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th></tr>
                        <?php foreach ($db->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll() as $p): ?>
                            <tr>
                                <td><code style="background: #f1f5f9; padding: 4px; border-radius: 4px;"><?= htmlspecialchars($p['codigo']) ?></code></td>
                                <td style="font-weight: 500;"><?= htmlspecialchars($p['nombre']) ?></td>
                                <td><span class="badge"><?= htmlspecialchars($p['categoria']) ?></span></td>
                                <td style="font-weight: bold;">$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($p['stock']) ?> un.</td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

            <?php elseif ($vista === 'modificar'): 
                if (isset($_GET['id'])): 
                    $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                    $prod = $stmt->fetch();
            ?>
                    <h1 class="header-title">Modificar Registro</h1>
                    <div class="card" style="border-top: 4px solid var(--warning);">
                        <form action="index.php?v=modificar" method="POST">
                            <input type="hidden" name="accion" value="modificar">
                            <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Código</label>
                                    <input type="text" name="codigo" value="<?= htmlspecialchars($prod['codigo']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <input type="text" name="categoria" value="<?= htmlspecialchars($prod['categoria']) ?>" required>
                                </div>
                                <div class="form-group full-width">
                                    <label>Descripción</label>
                                    <input type="text" name="nombre" value="<?= htmlspecialchars($prod['nombre']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Precio</label>
                                    <input type="number" name="precio" value="<?= htmlspecialchars($prod['precio']) ?>" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label>Stock</label>
                                    <input type="number" name="stock" value="<?= htmlspecialchars($prod['stock']) ?>" min="0" required>
                                </div>
                            </div>
                            <div style="margin-top: 25px; text-align: right;">
                                <button type="submit" class="btn btn-warning">Actualizar Datos</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <h1 class="header-title">Seleccionar Registro a Modificar</h1>
                    <div class="card" style="padding: 0;">
                        <table>
                            <tr><th>SKU</th><th>Producto</th><th>Acción</th></tr>
                            <?php foreach ($db->query("SELECT * FROM productos")->fetchAll() as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['codigo']) ?></td>
                                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td><a href="index.php?v=modificar&id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Editar</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>

            <?php elseif ($vista === 'eliminar'): ?>
                <h1 class="header-title">Eliminar Registros</h1>
                <div class="card" style="padding: 0; border-top: 4px solid var(--danger);">
                    <table>
                        <tr><th>SKU</th><th>Producto</th><th>Acción Crítica</th></tr>
                        <?php foreach ($db->query("SELECT * FROM productos")->fetchAll() as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['codigo']) ?></td>
                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                <td>
                                    <form action="index.php?v=eliminar" method="POST" onsubmit="return confirm('¿Confirma la eliminación permanente de la base de datos?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Borrar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

            <?php elseif ($vista === 'bitacora'): ?>
                <h1 class="header-title">Bitácora de Auditoría (Log)</h1>
                <div class="card" style="padding: 0;">
                    <table style="font-size: 0.85rem;">
                        <tr>
                            <th>Fecha Hora</th>
                            <th>Usuario</th>
                            <th>Tipo de Evento</th>
                            <th>Detalle / Tablas</th>
                            <th>IP Cliente</th>
                        </tr>
                        <?php foreach ($db->query("SELECT * FROM bitacora ORDER BY id DESC")->fetchAll() as $log): ?>
                            <tr>
                                <td style="color: #64748b;"><?= htmlspecialchars($log['fecha_hora']) ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($log['usuario']) ?></td>
                                <td><span class="badge"><?= htmlspecialchars($log['tipo_evento']) ?></span></td>
                                <td><?= htmlspecialchars($log['detalle']) ?></td>
                                <td style="font-family: monospace;"><?= htmlspecialchars($log['ip_cliente']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
<?php endif; ?>

</body>
</html>