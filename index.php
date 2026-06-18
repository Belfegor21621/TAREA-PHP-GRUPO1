<?php
// Iniciamos una sesión para asegurar que la página sea interactiva sin errores de conexión
session_start();

// Simulamos los datos de la base de datos
if (!isset($_SESSION['productos'])) {
    $_SESSION['productos'] = [
        ['id' => 1, 'codigo' => '7421098', 'nombre' => 'Saco Alimento Perro 15kg', 'categoria' => 'Alimentos', 'precio' => 45990],
        ['id' => 2, 'codigo' => '7421099', 'nombre' => 'Collar Antipulgas', 'categoria' => 'Farmacia', 'precio' => 12500]
    ];
}

// LÓGICA DE OPERACIONES CRUD (Interactivas en memoria)

// C: CREATE (Insertar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $nuevo_id = count($_SESSION['productos']) > 0 ? max(array_column($_SESSION['productos'], 'id')) + 1 : 1;
    $_SESSION['productos'][] = [
        'id' => $nuevo_id,
        'codigo' => $_POST['codigo'],
        'nombre' => $_POST['nombre'],
        'categoria' => $_POST['categoria'],
        'precio' => $_POST['precio']
    ];
    header("Location: index.php"); exit();
}

// D: DELETE (Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    foreach ($_SESSION['productos'] as $key => $prod) {
        if ($prod['id'] == $_POST['id']) {
            unset($_SESSION['productos'][$key]);
        }
    }
    header("Location: index.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Interactivo - Mascotas</title>
    <style>
        :root { --primary: #1e293b; --accent: #0ea5e9; --success: #10b981; --danger: #ef4444; --warning: #f59e0b; --bg: #f8fafc; }
        body { font-family: system-ui, sans-serif; background: var(--bg); margin: 0; padding: 30px 20px; color: #334155; display: flex; justify-content: center; }
        .container { max-width: 900px; width: 100%; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0 0 10px 0; color: var(--primary); }
        .integrantes { display: inline-block; background: #e0f2fe; color: #0369a1; padding: 8px 15px; border-radius: 6px; font-weight: 600; margin-bottom: 15px; }
        .modulo { background: #f8fafc; border: 1px solid #e2e8f0; padding: 25px; border-radius: 8px; margin-bottom: 30px; }
        .modulo h2 { margin-top: 0; font-size: 1.25rem; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        .badge { font-size: 0.8rem; padding: 4px 10px; border-radius: 5px; color: white; font-weight: bold; }
        .bg-create { background: var(--success); } .bg-read { background: var(--accent); }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        input, select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .btn-crear { background: var(--success); color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 15px; width: 100%; font-size: 1rem; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 10px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; font-weight: 600; color: #475569; }
        .btn-accion { padding: 8px 12px; border-radius: 5px; border: none; font-size: 0.85rem; color: white; font-weight: bold; cursor: pointer; margin-right: 5px; }
        .btn-update { background: var(--warning); } .btn-delete { background: var(--danger); }
        .form-inline { display: inline; }
        .seccion-mockup { margin-top: 40px; padding-top: 30px; border-top: 2px dashed #cbd5e1; text-align: center; }
        .seccion-mockup img { max-width: 100%; border-radius: 8px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Sistema de Inventarios Interactivo</h1>
            <div class="integrantes">Integrantes: ALEX HERNANDEZ, JULLYANA REBOLLEDO</div>
            <p><strong>Descripción:</strong> Menú de opciones y operaciones CRUD para gestionar el stock de productos de una tienda de mascotas.</p>
        </div>

        <div class="modulo">
            <h2>Ingreso de Nuevo Producto <span class="badge bg-create">Operación CREATE</span></h2>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="grid-form">
                    <input type="text" name="codigo" placeholder="Código de Barras" required>
                    <input type="text" name="nombre" placeholder="Nombre del Producto" required>
                    <select name="categoria" required>
                        <option value="">Seleccionar Categoría...</option>
                        <option value="Alimentos">Alimentos</option>
                        <option value="Accesorios">Accesorios</option>
                        <option value="Farmacia">Farmacia</option>
                    </select>
                    <input type="number" name="precio" placeholder="Precio Venta ($)" step="0.01" required>
                </div>
                <button type="submit" class="btn-crear">💾 Guardar Nuevo Registro</button>
            </form>
        </div>

        <div class="modulo">
            <h2>Inventario Actual <span class="badge bg-read">Operación READ</span></h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Operaciones CRUD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($_SESSION['productos']) > 0): ?>
                        <?php foreach ($_SESSION['productos'] as $prod): ?>
                        <tr>
                            <td><?= htmlspecialchars($prod['codigo']) ?></td>
                            <td><strong><?= htmlspecialchars($prod['nombre']) ?></strong></td>
                            <td><?= htmlspecialchars($prod['categoria']) ?></td>
                            <td>$<?= htmlspecialchars($prod['precio']) ?></td>
                            <td>
                                <button type="button" class="btn-accion btn-update" onclick="alert('Operación UPDATE simulada');">Editar</button>
                                
                                <form action="index.php" method="POST" class="form-inline" onsubmit="return confirm('¿Seguro que deseas aplicar DELETE a este registro?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                    <button type="submit" class="btn-accion btn-delete">Borrar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:20px;">Inventario vacío. Ejecuta CREATE arriba.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="seccion-mockup">
            <h2>Mockup de la Interfaz Principal</h2>
            <img src="mockup.png" alt="Mockup de la aplicación">
        </div>
    </div>

</body>
</html>