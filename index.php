<?php
// index.php - Punto de entrada / Router
// BACKEND: lógica en src/, FRONTEND: plantillas en templates/

session_start();

// Cargar configuración
require __DIR__ . '/config/constants.php';

// Cargar clases backend
require __DIR__ . '/src/core/Auth.php';
require __DIR__ . '/src/models/Database.php';
require __DIR__ . '/src/models/User.php';
require __DIR__ . '/src/models/Group.php';
require __DIR__ . '/src/models/Expense.php';

// Variables globales para templates
$tituloPagina = '';
$contenidoPagina = '';
$flashMessages = [];
$erroresLogin = [];
$erroresRegistro = [];
$flashMessages = [];
$erroresLogin = [];
$erroresRegistro = [];
$grupo = [];
$grupos = [];
$integrantes = [];
$gastos = [];

// Router
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $tituloPagina = 'Iniciar sesión';
    require __DIR__ . '/templates/auth/login.php';
    require __DIR__ . '/templates/layouts/master.php';
    exit;
}

if ($action === 'register') {
    $tituloPagina = 'Registrarse';
    require __DIR__ . '/templates/auth/register.php';
    require __DIR__ . '/templates/layouts/master.php';
    exit;
}

if ($action === 'login_process') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email']) && !empty($_POST['password'])) {
        $userModel = new User(Database::getInstance());
        $usuario = $userModel->findByEmail($_POST['email']);

        if ($usuario && password_verify($_POST['password'], $usuario['password_hash'])) {
            Auth::autenticar($usuario['id'], $usuario['nombre']);
            header('Location: ?');
            exit;
        }
    }
    $erroresLogin[] = 'Credenciales incorrectas';
    $tituloPagina = 'Iniciar sesión';
    require __DIR__ . '/templates/auth/login.php';
    require __DIR__ . '/templates/layouts/master.php';
    exit;
}

if ($action === 'register_process') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        $userModel = new User(Database::getInstance());
        if (!$userModel->existsWithEmail($_POST['email'])) {
            $usuarioId = $userModel->crear([
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'rol' => $_POST['rol'] ?? 'miembro'
            ]);
            if ($usuarioId) {
                Auth::autenticar($usuarioId, $_POST['nombre']);
                header('Location: ?');
                exit;
            }
        } else {
            $erroresRegistro[] = 'El email ya está registrado';
        }
    }
    $tituloPagina = 'Registrarse';
    require __DIR__ . '/templates/auth/register.php';
    require __DIR__ . '/templates/layouts/master.php';
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header('Location: ?action=login');
    exit;
}

// Todas las demás rutas requieren autenticación
if (!Auth::estaAutenticada()) {
    header('Location: ?action=login');
    exit;
}

$usuarioId = Auth::getUsuarioId();
$grupoModel = new Group(Database::getInstance());

switch ($action) {
    case 'dashboard':
        $tituloPagina = 'Panel de control';
        $grupos = $grupoModel->findByUser($usuarioId);
        require __DIR__ . '/templates/dashboard.php';
        break;

    case 'grupos':
        $tituloPagina = 'Mis Grupos';
        $grupos = $grupoModel->findByUser($usuarioId);
        require __DIR__ . '/templates/groups/list.php';
        break;

    case 'group_detail':
        $grupoId = $_GET['id'] ?? null;
        $tituloPagina = 'Detalle del grupo';
        $grupo = $grupoModel->findById($grupoId, $usuarioId);
        $integrantes = $grupoModel->getIntegrantes($grupoId);
        require __DIR__ . '/templates/groups/detail.php';
        break;

    case 'create_group':
        $tituloPagina = 'Crear grupo';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre'])) {
            $grupoId = $grupoModel->crear($_POST, $usuarioId);
            if ($grupoId) {
                header('Location: ?action=group_detail&id=' . $grupoId);
                exit;
            }
        }
        require __DIR__ . '/templates/groups/create.php';
        break;

    case 'group_expenses':
        $grupoId = $_GET['id'] ?? null;
        $tituloPagina = 'Gastos del grupo';
        $grupo = $grupoModel->findById($grupoId, $usuarioId);
        $expenseModel = new Expense(Database::getInstance());
        $gastos = $expenseModel->getByGroup($grupoId, $usuarioId);
        require __DIR__ . '/templates/expenses/list.php';
        break;

    case 'create_expense':
        $grupoId = $_GET['grupo_id'] ?? null;
        $tituloPagina = 'Agregar gasto';
        $grupo = $grupoModel->findById($grupoId, $usuarioId);
        $errores = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['concepto'])) $errores['concepto'] = 'El concepto es requerido';
            if (empty($_POST['monto']) || !is_numeric($_POST['monto'])) $errores['monto'] = 'Monto inválido';
            if (empty($errores)) {
                $expenseModel = new Expense(Database::getInstance());
                $result = $expenseModel->create([
                    'grupo_id' => $grupoId,
                    'user_id' => $usuarioId,
                    'concepto' => $_POST['concepto'],
                    'monto' => $_POST['monto'],
                    'tipo' => $_POST['tipo'] ?? 'personalizado'
                ]);
                if ($result) {
                    header('Location: ?action=group_expenses&id=' . $grupoId);
                    exit;
                }
            }
        }
        require __DIR__ . '/templates/expenses/create.php';
        break;

    default:
        $tituloPagina = 'Panel de control';
        $grupos = $grupoModel->findByUser($usuarioId);
        require __DIR__ . '/templates/dashboard.php';
        break;
}

require __DIR__ . '/templates/layouts/master.php';