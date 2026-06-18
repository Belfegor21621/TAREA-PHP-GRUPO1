<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Tienda de Mascotas</title>
    <style>
        :root {
            --primary: #2563eb;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #4b5563;
        }

        body { 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0; 
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            background-color: var(--card-bg);
            max-width: 900px;
            width: 100%;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        h1 { 
            color: var(--primary); 
            border-bottom: 2px solid #e5e7eb; 
            padding-bottom: 15px;
            margin-top: 0;
            text-align: center;
        }

        h2 { 
            color: #111827; 
            margin-top: 35px; 
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .integrantes {
            background-color: #eff6ff;
            color: var(--primary);
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        p { line-height: 1.7; color: var(--text-muted); font-size: 1.05rem; }

        /* Diseño del Menú Requerido */
        .menu-opciones { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 15px; 
            padding: 0; 
            list-style: none; 
            justify-content: center;
        }
        
        .menu-opciones li { 
            background-color: var(--primary); 
            color: white; 
            padding: 12px 24px; 
            border-radius: 8px; 
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
            transition: transform 0.2s;
        }

        .menu-opciones li:hover {
            transform: translateY(-2px);
        }

        /* Diseño de Operaciones CRUD */
        .crud-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .crud-card {
            background-color: #f8fafc;
            border-left: 5px solid var(--primary);
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .crud-card h3 { margin-top: 0; color: #1e293b; font-size: 1.1rem; }
        .crud-card p { margin-bottom: 0; font-size: 0.95rem; }
        
        code {
            background-color: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            color: #b91c1c;
            font-weight: bold;
        }

        /* Sección de Imagen */
        .mockup-container {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px dashed #cbd5e1;
        }

        .mockup-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>🐾 Sistema de Gestión de Inventarios</h1>

        <div class="integrantes">
            Integrantes: ALEX HERNANDEZ & JULLYANA REBOLLEDO
        </div>

        <h2>Descripción de la Aplicación</h2>
        <p>La aplicación consiste en un sistema web diseñado específicamente para una tienda de mascotas. El sistema permitirá a los usuarios mantener un registro centralizado y ordenado del stock de productos (como alimentos, accesorios y farmacia). Su objetivo principal es facilitar el control de mercadería mediante un menú de opciones intuitivo.</p>

        <h2>Menú de Opciones</h2>
        <ul class="menu-opciones">
            <li>➕ Crear Producto Nuevo</li>
            <li>📖 Ver Inventario Completo</li>
            <li>✏️ Actualizar Stock / Precio</li>
            <li>🗑️ Eliminar Registro</li>
        </ul>

        <h2>Descripción de las Operaciones CRUD</h2>
        <div class="crud-grid">
            <div class="crud-card" style="border-left-color: #10b981;">
                <h3>CREATE (Crear)</h3>
                <p>Opción para ingresar un nuevo producto. A nivel de base de datos, ejecuta la sentencia <code>INSERT INTO</code> para registrar los datos en la tabla.</p>
            </div>
            <div class="crud-card" style="border-left-color: #3b82f6;">
                <h3>READ (Leer)</h3>
                <p>Opción para visualizar el catálogo. Ejecuta una consulta <code>SELECT</code> en SQL para extraer la información y mostrarla en la interfaz.</p>
            </div>
            <div class="crud-card" style="border-left-color: #f59e0b;">
                <h3>UPDATE (Actualizar)</h3>
                <p>Opción para editar información existente (ej. modificar precios). Utiliza la sentencia <code>UPDATE</code> en la base de datos SQL.</p>
            </div>
            <div class="crud-card" style="border-left-color: #ef4444;">
                <h3>DELETE (Eliminar)</h3>
                <p>Opción para remover definitivamente un producto descontinuado del sistema, ejecutando el comando <code>DELETE FROM</code> en SQL.</p>
            </div>
        </div>

        <div class="mockup-container">
            <h2>Mockup de la Interfaz Principal</h2>
            <img src="mockup.png" alt="Mockup de la interfaz de la aplicación">
        </div>
    </div>

</body>
</html>