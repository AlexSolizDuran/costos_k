<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(TITULO_APP) ?> - <?= htmlspecialchars($tituloPagina) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #2c3e50; padding: 1rem 2rem; }
        .navbar .logo a { color: #fff; text-decoration: none; font-size: 1.3rem; font-weight: bold; }
        .nav-links { list-style: none; display: flex; gap: 1rem; }
        .nav-links a { color: #ecf0f1; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; }
        .nav-links a:hover { background: rgba(255,255,255,0.1); }
        .container { max-width: 960px; margin: 2rem auto; padding: 0 1rem; }
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.8rem; color: #2c3e50; }
        .auth-container { display: flex; justify-content: center; margin-top: 3rem; }
        .auth-box { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .auth-box h2 { margin-bottom: 1rem; color: #2c3e50; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.3rem; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        .btn-primary { background: #3498db; color: #fff; border: none; padding: 0.7rem 1.5rem; border-radius: 4px; cursor: pointer; font-size: 1rem; text-decoration: none; display: inline-block; }
        .btn-primary:hover { background: #2980b9; }
        .btn-small { padding: 0.3rem 0.8rem; font-size: 0.85rem; border-radius: 4px; text-decoration: none; color: #3498db; border: 1px solid #3498db; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        th, td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2c3e50; color: #fff; }
        .flash-error { background: #e74c3c; color: #fff; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .flash-success { background: #27ae60; color: #fff; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .error { color: #e74c3c; font-size: 0.85rem; }
        footer { text-align: center; padding: 2rem; color: #7f8c8d; font-size: 0.85rem; }
        .link-text { text-align: center; margin-top: 1rem; }
        .link-text a { color: #3498db; }
        .dashboard-stats { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .stat-card { flex: 1; background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; }
        .stat-card h3 { color: #7f8c8d; font-size: 0.9rem; }
        .stat-card p { font-size: 2rem; color: #2c3e50; font-weight: bold; }
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
                    <li><a href="?action=logout">Cerrar sesion</a></li>
                <?php else: ?>
                    <li><a href="?action=login">Iniciar sesion</a></li>
                    <li><a href="?action=register">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">
        <?php if(!empty($flashMessages)): ?>
            <?php foreach($flashMessages as $msg): ?>
                <div class="flash-success"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?= $contenidoPagina ?>
    </main>

    <footer>
        <p>&copy; 2026 <?= TITULO_APP ?></p>
    </footer>
</body>
</html>