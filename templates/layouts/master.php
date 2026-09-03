<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(TITULO_APP) ?> - <?= htmlspecialchars($tituloPagina) ?></title>
    <style>
        /* ============ Base (estética del original) ============ */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #222; }

        /* ============ Navbar (estructura conservada) ============ */
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #222; padding: 1rem 2rem; }
        .navbar .logo a { color: #fff; text-decoration: none; font-size: 1.3rem; font-weight: bold; }
        .nav-links { list-style: none; display: flex; gap: 1rem; flex-wrap: wrap; }
        .nav-links li { display: flex; align-items: center; }
        .nav-links a { color: #ddd; text-decoration: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 14px; }
        .nav-links a:hover { background: #444; color: #fff; }

        /* ============ Contenedor ============ */
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
        .cabecera h1 { margin: 0; font-size: 32px; }
        .cabecera .usuario { color: #666; margin-top: 5px; }
        .acciones { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .volver { text-decoration: none; color: #555; font-weight: 600; }
        .volver:hover { color: #000; }
        .titulo { margin: 0 0 8px; font-size: 32px; }
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.8rem; margin-bottom: 0.3rem; }

        /* ============ Botones del original ============ */
        .btn { display: inline-block; padding: 10px 15px; border-radius: 7px; text-decoration: none; font-weight: 600; font-size: 14px; border: none; cursor: pointer; text-align: center; }
        .btn-negro { background: #222; color: white; }
        .btn-negro:hover { background: #444; }
        .btn-azul { background: #0066cc; color: white; }
        .btn-azul:hover { background: #0052a3; }
        .btn-rojo { background: #ffe5e5; color: #c00; }
        .btn-rojo:hover { background: #ffd2d2; }
        .btn-gris { background: #eee; color: #333; }
        .btn-gris:hover { background: #ddd; }
        .btn-primary { background: #222; color: white; display: inline-block; padding: 13px 20px; border-radius: 7px; text-decoration: none; font-weight: 600; font-size: 14px; border: none; cursor: pointer; }
        .btn-primary:hover { background: #444; }
        .btn-small { padding: 0.3rem 0.8rem; font-size: 0.85rem; border-radius: 6px; text-decoration: none; color: #0066cc; border: 1px solid #0066cc; font-weight: 600; display: inline-block; }
        .btn-small:hover { background: rgba(0,102,204,0.08); }
        .crear-grupo { display: inline-block; background: #222; color: white; text-decoration: none; padding: 12px 20px; border: none; border-radius: 7px; font-size: 14px; cursor: pointer; font-family: inherit; }
        .crear-grupo:hover { background: #444; }
        .cerrar { color: #c00; text-decoration: none; font-weight: 600; }
        .cerrar:hover { text-decoration: underline; }

        /* ============ Secciones / tarjetas ============ */
        .grupo-info, .administracion, .seccion {
            background: white; border-radius: 12px; padding: 25px; margin-bottom: 25px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }
        .seccion-cabecera { display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .seccion-cabecera h2 { margin: 0; }
        .descripcion { color: #666; margin-top: 8px; line-height: 1.5; }
        .datos-grupo { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 20px; }
        .dato { background: #f7f7f7; border-radius: 8px; padding: 15px; }
        .dato strong { display: block; font-size: 13px; color: #777; margin-bottom: 5px; }

        /* ============ Tablas del original ============ */
        .tabla-contenedor { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th { background: #f1f2f4; color: #444; font-size: 14px; text-align: left; }
        th, td { padding: 13px 12px; border-bottom: 1px solid #e5e5e5; text-align: left; }
        tr:hover td { background: #fafafa; }
        .accion { text-decoration: none; color: #0066cc; font-weight: 600; cursor: pointer; background: none; border: none; font-family: inherit; font-size: 14px; }
        .accion:hover { text-decoration: underline; }
        .accion-roja { color: #c00; }
        .accion-roja:hover { text-decoration: underline; }

        /* ============ Pills de estado ============ */
        .estado { display: inline-block; padding: 5px 9px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .estado-activo, .estado-pagado { background: #e4f7e8; color: #16702d; }
        .estado-cerrado, .estado-pendiente, .estado-desactivado { background: #eee; color: #666; }
        .estado-parcial { background: #fff3cd; color: #856404; }
        .estado-expirada, .estado-usada { background: #ffe5e5; color: #b00000; }

        /* ============ Sin datos ============ */
        .sin-grupos { background: white; padding: 40px; text-align: center; border-radius: 10px; box-shadow: 0 3px 12px rgba(0,0,0,0.08); }
        .sin-datos { text-align: center; padding: 35px 20px; color: #777; background: #fafafa; border-radius: 8px; }

        /* ============ Mensajes ============ */
        .flash-info { background: #e5f1fb; color: #004e91; padding: 12px 15px; border-radius: 7px; margin-bottom: 1rem; border: 1px solid #bcdcfa; }
        .error { background: #ffdede; color: #b00000; padding: 10px 12px; margin-bottom: 15px; border-radius: 5px; font-size: 14px; }
        .error-gasto { background: #ffe5e5; color: #b00000; border: 1px solid #ffb3b3; border-radius: 7px; padding: 12px; margin-bottom: 15px; font-size: 14px; }

        /* ============ Grilla de grupos (index) ============ */
        .grupos { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .grupo { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .grupo h2 { margin-top: 0; margin-bottom: 10px; font-size: 18px; }
        .grupo p { color: #666; min-height: 40px; }
        .grupo small { color: #777; }
        .grupo .grupo-links a { display: inline-block; margin-top: 10px; margin-right: 12px; text-decoration: none; color: #0066cc; font-weight: 600; }

        /* ============ Autenticación ============ */
        .auth-container { display: flex; justify-content: center; align-items: center; min-height: 70vh; }
        .auth-box { background: white; width: 100%; max-width: 400px; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .auth-box h1 { text-align: center; margin-bottom: 25px; }
        .auth-box label { display: block; margin-bottom: 5px; }
        .auth-box input, .auth-box select, .auth-box textarea { width: 100%; box-sizing: border-box; padding: 11px; border: 1px solid #ccc; border-radius: 7px; font-family: inherit; font-size: 14px; margin-bottom: 15px; }
        .auth-box input:focus, .auth-box select:focus, .auth-box textarea:focus { outline: none; border-color: #0066cc; box-shadow: 0 0 0 2px rgba(0,102,204,0.1); }
        .auth-box button[type="submit"], .auth-box .btn-full { width: 100%; background: #222; color: white; padding: 12px; border: none; border-radius: 7px; cursor: pointer; font-size: 15px; font-family: inherit; }
        .auth-box button[type="submit"]:hover { background: #444; }
        .registro-link { text-align: center; margin-top: 15px; font-size: 14px; color: #666; }
        .registro-link a { color: #0066cc; text-decoration: none; }

        /* ============ Formularios en secciones ============ */
        form.campo-form label { display: block; font-weight: 600; margin: 12px 0 5px; }
        form.campo-form .btn { margin-top: 18px; }
        .campo-enlace { display: flex; gap: 8px; margin-top: 15px; }
        .campo-enlace input { flex: 1; min-width: 0; padding: 11px; border: 1px solid #ccc; border-radius: 7px; font-family: inherit; font-size: 14px; }
        .campo-enlace button { padding: 10px 15px; border: none; border-radius: 7px; background: #222; color: white; cursor: pointer; }

        /* ============ Modales (copiados del original) ============ */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: none; justify-content: center; align-items: center; z-index: 9999; padding: 20px; }
        .modal { width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; background: white; border-radius: 12px; padding: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.25); animation: aparecerModal 0.15s ease-out; }
        @keyframes aparecerModal { from { opacity: 0; transform: translateY(-10px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .modal-cabecera { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-cabecera h2 { margin: 0; }
        .cerrar-modal { border: none; background: transparent; font-size: 30px; cursor: pointer; color: #666; line-height: 1; }
        .cerrar-modal:hover { color: #000; }
        .modal form { display: flex; flex-direction: column; gap: 4px; }
        .modal label { font-weight: 600; margin-top: 8px; }
        .modal input, .modal textarea, .modal select { width: 100%; padding: 11px; border: 1px solid #ccc; border-radius: 7px; font-family: inherit; font-size: 14px; box-sizing: border-box; margin-top: 4px; }
        .modal textarea { resize: vertical; }
        .modal input:focus, .modal textarea:focus, .modal select:focus { outline: none; border-color: #0066cc; box-shadow: 0 0 0 2px rgba(0,102,204,0.1); }
        .modal-acciones { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .modal-acciones button { padding: 10px 18px; border-radius: 7px; cursor: pointer; border: none; font-weight: 600; }
        .btn-cancelar { background: #eee; color: #333; }
        .btn-cancelar:hover { background: #ddd; }
        .btn-guardar { background: #222; color: white; }
        .btn-guardar:hover { background: #444; }
        .btn-whatsapp { display: inline-block; padding: 11px 16px; background: #25D366; color: white; text-decoration: none; border-radius: 7px; font-weight: 600; }
        .btn-whatsapp:hover { opacity: 0.9; }
        .mensaje-copiado { display: none; margin-top: 10px; color: #16803c; font-size: 14px; }
        .acciones-invitacion { margin-top: 20px; display: flex; flex-direction: column; gap: 10px; }

        /* ============ Botones de invitación (Link / QR) ============ */
        .btn-inv { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px 16px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; font-family: inherit; text-decoration: none; box-sizing: border-box; transition: all .15s ease; }
        .btn-inv-ico { font-size: 16px; }
        .btn-inv:disabled { cursor: not-allowed; }
        .btn-inv-copiar { background: #eef2f7; color: #1a2733; border: 1px solid #cfd8e3; }
        .btn-inv-copiar:hover:not(:disabled) { background: #dfe7f0; border-color: #b8c5d4; }
        .btn-inv-copiar:disabled { background: #f4f6f9; color: #aab4bf; }
        .btn-inv-whatsapp { background: #25D366; color: white; }
        .btn-inv-whatsapp:hover { opacity: 0.9; }
        .btn-inv-regenerar { background: #ffffff; color: #0066cc; border: 1.5px solid #0066cc; }
        .btn-inv-regenerar:hover { background: #eaf3fb; }
        .btn-inv-descargar { background: #0066cc; color: white; }
        .btn-inv-descargar:hover:not(:disabled) { background: #0052a3; }
        .btn-inv-descargar:disabled { background: #c3d6ea; color: #eef4fa; }

        /* ============ Tabs Link / QR (invitaciones) ============ */
        .seguro-tabs { display: flex; gap: 8px; margin: 16px 0 14px; background: #f1f3f5; padding: 4px; border-radius: 9px; }
        .seguro-tab { flex: 1; padding: 10px; border: none; background: transparent; border-radius: 7px; cursor: pointer; font-size: 14px; font-weight: 600; color: #555; font-family: inherit; }
        .seguro-tab:hover { color: #000; }
        .seguro-tab.activa { background: white; color: #0066cc; box-shadow: 0 1px 4px rgba(0,0,0,0.12); }
        .qr-contenedor { display: flex; justify-content: center; padding: 14px; background: #fff; border: 1px solid #e3e6ea; border-radius: 10px; }
        .qr-contenedor canvas, .qr-contenedor img { max-width: 100%; height: auto; border-radius: 6px; }

        /* ============ Responsive ============ */
        @media (max-width: 700px) {
            .container { padding: 0 12px; }
            .cabecera { align-items: flex-start; flex-direction: column; }
            .titulo { font-size: 26px; }
            .grupo-info, .administracion, .seccion { padding: 18px; }
            .seccion-cabecera { align-items: flex-start; flex-direction: column; }
            .acciones { flex-direction: column; align-items: stretch; }
            .btn { text-align: center; }
            .navbar { padding: 1rem; flex-direction: column; gap: 10px; }
            .nav-links { justify-content: center; }
            .modal-acciones { flex-direction: column-reverse; }
            .modal-acciones button { width: 100%; }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="?"><?= TITULO_APP ?></a>
            </div>
            <ul class="nav-links">
                <?php if(Auth::estaAutenticada()): ?>
                    <li><a href="?action=dashboard">Panel</a></li>
                    <li><a href="?action=grupos">Mis Grupos</a></li>
                    <?php if(Auth::esAdmin()): ?>
                        <li><a href="?action=admin_usuarios">Usuarios</a></li>
                    <?php endif; ?>
                    <li><a href="?action=logout">Cerrar sesión</a></li>
                <?php else: ?>
                    <li><a href="?action=login">Iniciar sesión</a></li>
                    <li><a href="?action=register">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">
        <?php if(!empty($flashMessages)): ?>
            <?php foreach($flashMessages as $msg): ?>
                <div class="flash-info"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?= $contenidoPagina ?>
    </main>

    <footer>
        <p>&copy; 2026 <?= TITULO_APP ?></p>
    </footer>
</body>
</html>