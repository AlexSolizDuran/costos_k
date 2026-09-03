<?php
// index.php - Punto de entrada / Router
// BACKEND: lógica en src/, FRONTEND: plantillas en templates/
// Arquitectura escalable: cada template genera $contenidoPagina y el layout lo envuelve.

session_start();

// Cargar configuración
require __DIR__ . '/config/constants.php';

// Cargar clases backend
require __DIR__ . '/src/core/Auth.php';
require __DIR__ . '/src/models/Database.php';
require __DIR__ . '/src/models/User.php';
require __DIR__ . '/src/models/Group.php';
require __DIR__ . '/src/models/Expense.php';
require __DIR__ . '/src/models/Payment.php';

// Helpers de presentación
require __DIR__ . '/templates/helpers.php';

// Variables globales para templates
$tituloPagina = '';
$contenidoPagina = '';
$flashMessages = [];
$erroresLogin = [];
$erroresRegistro = [];
$errores = [];
$grupo = [];
$grupos = [];
$integrantes = [];
$gastos = [];
$usuariosCandidatos = [];

// Router
$action = $_GET['action'] ?? '';

/* ============================================================
   AUTENTICACIÓN
============================================================ */

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

        if (
            $usuario &&
            (bool) $usuario['activo'] &&
            password_verify($_POST['password'], $usuario['password_hash'])
        ) {
            Auth::autenticar($usuario['id'], $usuario['nombre'], $usuario['rol']);

            $destino = $_POST['redirect'] ?? '';
            if ($destino !== '' && str_starts_with($destino, '?')) {
                header('Location: ' . $destino);
                exit;
            }
            header('Location: ?');
            exit;
        }
    }
    $erroresLogin[] = 'Credenciales incorrectas'; // incluye cuentas desactivadas
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
                'rol' => ROL_MEMBRO
            ]);
            if ($usuarioId) {
                Auth::autenticar($usuarioId, $_POST['nombre'], ROL_MEMBRO);

                $destino = $_POST['redirect'] ?? '';
                if ($destino !== '' && str_starts_with($destino, '?')) {
                    header('Location: ' . $destino);
                    exit;
                }
                header('Location: ?');
                exit;
            }
        } else {
            $erroresRegistro[] = 'El email ya está registrado';
        }
    } else {
        $erroresRegistro[] = 'Debes completar todos los campos';
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

// Ruta raíz "/": página pública de bienvenida si no hay sesión activa
if ($action === '' && !Auth::estaAutenticada()) {
    $tituloPagina = 'Bienvenido';
    require __DIR__ . '/templates/landing.php';
    require __DIR__ . '/templates/layouts/master.php';
    exit;
}

// Todas las demás rutas requieren autenticación
if (!Auth::estaAutenticada()) {
    header('Location: ?action=login');
    exit;
}

$usuarioId = Auth::getUsuarioId();
$esAdminSistema = Auth::esAdmin();
$userModel = new User(Database::getInstance());
$grupoModel = new Group(Database::getInstance());

// Mensajes flash sobreviven al redirect por sesión
$flashMessages = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

/* ============================================================
   CUENTA (usuario autenticado)
============================================================ */

if ($action === 'cambiar_password') {
    $tituloPagina = 'Cambiar contraseña';
    $exito = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $actual = $_POST['password_actual'] ?? '';
        $nueva = $_POST['password_nueva'] ?? '';
        $repetida = $_POST['password_repetida'] ?? '';

        $usuario = $userModel->findById($usuarioId);
        if (!password_verify($actual, $usuario['password_hash'])) {
            $errores[] = 'La contraseña actual no es correcta';
        } elseif (strlen($nueva) < 6) {
            $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres';
        } elseif ($nueva !== $repetida) {
            $errores[] = 'La nueva contraseña y su confirmación no coinciden';
        } else {
            $userModel->actualizar($usuarioId, ['nombre' => $usuario['nombre'], 'email' => $usuario['email'], 'password' => $nueva]);
            $exito = true;
        }
    }

    require __DIR__ . '/templates/cuenta/cambiar_password.php';
    require __DIR__ . '/templates/layouts/master.php';
    exit;
}

if ($action === 'eliminar_cuenta') {
    $tituloPagina = 'Eliminar cuenta';

    if (isset($_GET['confirmar']) && $_GET['confirmar'] === '1') {
        $userModel->desactivar($usuarioId);
        session_destroy();
        header('Location: ?action=login');
        exit;
    }

    require __DIR__ . '/templates/cuenta/eliminar_cuenta.php';
    require __DIR__ . '/templates/layouts/master.php';
    exit;
}

/* ============================================================
   ADMINISTRACIÓN DE USUARIOS (solo admin del sistema)
============================================================ */

if (in_array($action, ['admin_usuarios', 'admin_usuario_form'], true)) {
    if (!$esAdminSistema) {
        header('Location: ?');
        exit;
    }

    if ($action === 'admin_usuarios') {
        $tituloPagina = 'Administrar usuarios';
        $usuarios = $userModel->getAll([], false);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? ROL_MEMBRO;

            if ($nombre === '' || $email === '' || $password === '') {
                $errores[] = 'Nombre, email y contraseña son obligatorios';
            } elseif ($userModel->existsWithEmail($email)) {
                $errores[] = 'El email ya está registrado';
            } else {
                $userModel->crear(['nombre' => $nombre, 'email' => $email, 'password' => $password, 'rol' => $rol]);
                header('Location: ?action=admin_usuarios');
                exit;
            }
        }
        require __DIR__ . '/templates/admin/usuarios.php';
        require __DIR__ . '/templates/layouts/master.php';
        exit;
    }

    if ($action === 'admin_usuario_form') {
        $tituloPagina = 'Modificar usuario';
        $usuarioEditar = null;
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $usuarioEditar = $userModel->findById((int) $_GET['id']);
        }
        if (!$usuarioEditar) {
            header('Location: ?action=admin_usuarios');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $rol = $_POST['rol'] ?? $usuarioEditar['rol'];
            $password = $_POST['password'] ?? '';

            if ($nombre === '' || $email === '') {
                $errores[] = 'Nombre y email son obligatorios';
            } elseif ($email !== $usuarioEditar['email'] && $userModel->existsWithEmail($email, $usuarioEditar['id'])) {
                $errores[] = 'El email ya está registrado por otro usuario';
            } else {
                $datos = ['nombre' => $nombre, 'email' => $email, 'rol' => $rol];
                if (!empty($password)) {
                    $datos['password'] = $password;
                }
                $userModel->actualizar($usuarioEditar['id'], $datos);
                header('Location: ?action=admin_usuarios');
                exit;
            }
        }
        require __DIR__ . '/templates/admin/usuario_form.php';
        require __DIR__ . '/templates/layouts/master.php';
        exit;
    }
}

if ($action === 'admin_usuario_toggle') {
    if (!$esAdminSistema) {
        header('Location: ?');
        exit;
    }
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $target = $userModel->findById((int) $_GET['id']);
        if ($target) {
            if ((bool) $target['activo']) {
                $userModel->desactivar($target['id']);
            } else {
                $userModel->activar($target['id']);
            }
        }
    }
    header('Location: ?action=admin_usuarios');
    exit;
}

/* ============================================================
   GRUPOS Y GASTOS
============================================================ */

switch ($action) {
    /* ---------- Dashboard ---------- */
    case 'dashboard':
        $grupos = $grupoModel->findByUser($usuarioId);
        require __DIR__ . '/templates/dashboard.php';
        break;

    /* ---------- Lista de grupos ---------- */
    case 'grupos':
        $tituloPagina = 'Mis Grupos';
        $grupos = $grupoModel->findByUser($usuarioId);
        require __DIR__ . '/templates/groups/list.php';
        break;

    /* ---------- Detalle de grupo ---------- */
    case 'group_detail':
        $grupoId = $_GET['id'] ?? null;
        if ($grupoId === null || !is_numeric($grupoId)) {
            header('Location: ?');
            exit;
        }
        $grupoId = (int) $grupoId;
        $tituloPagina = 'Detalle del grupo';
        $grupo = $grupoModel->findById($grupoId, $usuarioId);
        if (!$grupo) {
            header('Location: ?');
            exit;
        }
        $rol = $grupo['mi_rol'];
        $integrantes = $grupoModel->getIntegrantes($grupoId);
        $gastos = (new Expense(Database::getInstance()))->getByGroup($grupoId);
        $deudores = (new Expense(Database::getInstance()))->getDeudores($grupoId);
        $usuariosCandidatos = $grupoModel->getUsuariosCandidatos($grupoId);
        require __DIR__ . '/templates/groups/detail.php';
        break;

    /* ---------- Crear grupo ---------- */
    case 'create_group':
        $tituloPagina = 'Crear grupo';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty(trim($_POST['nombre'] ?? ''))) {
                $errores[] = 'El nombre del grupo es obligatorio.';
            } else {
                $nuevoId = $grupoModel->crear($_POST, $usuarioId);
                if ($nuevoId) {
                    header('Location: ?action=group_detail&id=' . $nuevoId);
                    exit;
                }
                $errores[] = 'No se pudo crear el grupo.';
            }
        }
        require __DIR__ . '/templates/groups/create.php';
        break;

    /* ---------- Modificar grupo (GET: JSON para modal | POST: guardar) ---------- */
    case 'modify_group':
        header('Content-Type: application/json; charset=utf-8');
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
        if ($id <= 0 || !$grupoModel->esAdmin($id, $usuarioId)) {
            echo json_encode(['ok' => false, 'error' => 'No tienes permisos para modificar este grupo.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            if ($nombre === '') {
                $flashMessages[] = 'El nombre es obligatorio.';
            } else {
                $grupoModel->actualizar($id, ['nombre' => $nombre, 'descripcion' => $_POST['descripcion'] ?? '']);
                $flashMessages[] = 'Grupo modificado correctamente.';
            }
            if (!empty($flashMessages)) {
                $_SESSION['flash'] = $flashMessages;
            }
            header('Location: ?action=group_detail&id=' . $id);
            exit;
        }

        echo json_encode(['ok' => true, 'grupo' => $grupoModel->findByIdSolo($id)]);
        exit;
        break;

    /* ---------- Cerrar grupo ---------- */
    case 'close_group':
        $grupoId = (int) ($_GET['id'] ?? 0);
        if ($grupoId > 0 && $grupoModel->esAdmin($grupoId, $usuarioId)) {
            $grupoModel->cerrarGrupo($grupoId);
        }
        header('Location: ?action=group_detail&id=' . $grupoId);
        exit;
        break;

    /* ---------- Eliminar grupo ---------- */
    case 'delete_group':
        $grupoId = (int) ($_GET['id'] ?? 0);
        if ($grupoId > 0 && $grupoModel->esAdmin($grupoId, $usuarioId)) {
            $grupoModel->eliminar($grupoId);
        }
        header('Location: ?');
        exit;
        break;

    /* ---------- Agregar integrante (manual) ---------- */
    case 'add_member':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?');
            exit;
        }
        $grupoId = (int) ($_POST['grupo_id'] ?? 0);
        $nuevoUsuarioId = (int) ($_POST['usuario_id'] ?? 0);
        if ($grupoId > 0 && $nuevoUsuarioId > 0 && $grupoModel->esAdmin($grupoId, $usuarioId)) {
            if ($nuevoUsuarioId === $usuarioId) {
                $flashMessages[] = 'No puedes agregarte a ti mismo.';
            } else {
                $ok = $grupoModel->agregarIntegrante($grupoId, $nuevoUsuarioId, 'miembro');
                $flashMessages[] = $ok
                    ? 'Integrante agregado correctamente.'
                    : 'Ese usuario ya pertenece al grupo.';
            }
        }
        if (!empty($flashMessages)) {
            $_SESSION['flash'] = $flashMessages;
        }
        header('Location: ?action=group_detail&id=' . $grupoId);
        exit;
        break;

    /* ---------- Cambiar rol ---------- */
    case 'change_role':
        $grupoId = (int) ($_GET['grupo_id'] ?? 0);
        $objetivo = (int) ($_GET['usuario_id'] ?? 0);
        $nuevoRol = $_GET['rol'] ?? '';
        if (!in_array($nuevoRol, ['admin', 'miembro'], true)) {
            $nuevoRol = '';
        }

        if (
            $grupoId > 0 && $objetivo > 0 && $nuevoRol !== '' &&
            $objetivo !== $usuarioId && $grupoModel->esAdmin($grupoId, $usuarioId)
        ) {
            if ($nuevoRol === 'miembro' && $grupoModel->contarAdmins($grupoId) <= 1) {
                $flashMessages[] = 'No puedes quitar el último administrador del grupo.';
            } elseif ($grupoModel->esIntegrante($grupoId, $objetivo)) {
                $grupoModel->actualizarRol($grupoId, $objetivo, $nuevoRol);
            }
        }
        if (!empty($flashMessages)) {
            $_SESSION['flash'] = $flashMessages;
        }
        header('Location: ?action=group_detail&id=' . $grupoId);
        exit;
        break;

    /* ---------- Eliminar integrante ---------- */
    case 'remove_member':
        $grupoId = (int) ($_GET['grupo_id'] ?? 0);
        $objetivo = (int) ($_GET['usuario_id'] ?? 0);
        if ($grupoId > 0 && $objetivo > 0 && $grupoModel->esAdmin($grupoId, $usuarioId)) {
            $info = $grupoModel->findByIdSolo($grupoId);
            if ($info && (int) $info['creado_por'] === $objetivo) {
                $flashMessages[] = 'No se puede eliminar al creador del grupo.';
            } elseif ($grupoModel->esIntegrante($grupoId, $objetivo)) {
                $grupoModel->quitarIntegrante($grupoId, $objetivo);
            }
        }
        if (!empty($flashMessages)) {
            $_SESSION['flash'] = $flashMessages;
        }
        header('Location: ?action=group_detail&id=' . $grupoId);
        exit;
        break;

    /* ---------- Salir del grupo ---------- */
    case 'leave_group':
        $grupoId = (int) ($_GET['id'] ?? 0);
        if ($grupoId > 0 && $grupoModel->esIntegrante($grupoId, $usuarioId)) {
            if ($grupoModel->esAdmin($grupoId, $usuarioId) && $grupoModel->contarAdmins($grupoId) <= 1) {
                $flashMessages[] = 'No puedes salir porque eres el único administrador del grupo.';
            } else {
                $grupoModel->salirGrupo($grupoId, $usuarioId);
            }
        }
        if (!empty($flashMessages)) {
            $_SESSION['flash'] = $flashMessages;
        }
        header('Location: ?action=dashboard');
        exit;
        break;

    /* ---------- Generar invitación (JSON) ---------- */
    case 'generate_invitation':
        header('Content-Type: application/json; charset=utf-8');
        $grupoId = (int) ($_POST['grupo_id'] ?? 0);
        if ($grupoId <= 0 || !$grupoModel->esAdmin($grupoId, $usuarioId)) {
            echo json_encode(['ok' => false, 'error' => 'No tienes permisos para generar invitaciones.']);
            exit;
        }
        echo json_encode($grupoModel->generarInvitacion($grupoId, $usuarioId));
        exit;
        break;

    /* ---------- Aceptar invitación ---------- */
    case 'accept_invitation':
        $codigo = $_GET['codigo'] ?? '';

        if (Auth::estaAutenticada() === false) {
            header('Location: ?action=login&redirect=' . urlencode('?action=accept_invitation&codigo=' . urlencode($codigo)));
            exit;
        }

        $invitacion = $grupoModel->obtenerInvitacion($codigo);
        if (!$invitacion) {
            $tituloPagina = 'Invitación';
            $contenidoPagina = '<div class="seccion"><h2>Invitación no encontrada</h2><p>El código proporcionado no existe.</p><a class="btn btn-negro" href="?">Ir a Mis grupos</a></div>';
            require __DIR__ . '/templates/layouts/master.php';
            exit;
        }

        if ($invitacion['estado'] === 'expirada' || ($invitacion['fecha_expiracion'] !== null && strtotime($invitacion['fecha_expiracion']) < time())) {
            $tituloPagina = 'Invitación';
            $contenidoPagina = '<div class="seccion"><h2>Invitación expirada</h2><p>Esta invitación ya no está activa.</p><a class="btn btn-negro" href="?">Ir a Mis grupos</a></div>';
            require __DIR__ . '/templates/layouts/master.php';
            exit;
        }

        if ($invitacion['estado'] === 'usada' && !$grupoModel->esIntegrante($invitacion['grupo_id'], $usuarioId)) {
            $tituloPagina = 'Invitación';
            $contenidoPagina = '<div class="seccion"><h2>Invitación ya usada</h2><p>Este enlace ya fue utilizado.</p><a class="btn btn-negro" href="?">Ir a Mis grupos</a></div>';
            require __DIR__ . '/templates/layouts/master.php';
            exit;
        }

        $resultado = $grupoModel->aceptarInvitacion($invitacion, $usuarioId);
        if (!$resultado['ok']) {
            $tituloPagina = 'Invitación';
            $contenidoPagina = '<div class="seccion"><h2>No se pudo unir</h2><p>' . e($resultado['error']) . '</p><a class="btn btn-negro" href="?">Ir a Mis grupos</a></div>';
            require __DIR__ . '/templates/layouts/master.php';
            exit;
        }

        header('Location: ?action=group_detail&id=' . $resultado['grupo_id']);
        exit;
        break;

    /* ---------- Lista de gastos del grupo ---------- */
    case 'group_expenses':
        $grupoId = (int) ($_GET['id'] ?? 0);
        if ($grupoId <= 0 || !$grupoModel->esIntegrante($grupoId, $usuarioId)) {
            header('Location: ?');
            exit;
        }
        $tituloPagina = 'Gastos del grupo';
        $grupo = $grupoModel->findById($grupoId, $usuarioId);
        $expenseModel = new Expense(Database::getInstance());
        $gastos = $expenseModel->getByGroup($grupoId);
        $totalPendienteUsuario = $expenseModel->totalPendienteDe($usuarioId, $grupoId);
        $usuariosCandidatos = $grupoModel->getUsuariosCandidatos($grupoId);
        require __DIR__ . '/templates/expenses/list.php';
        break;

    /* ---------- Crear gasto ---------- */
    case 'create_expense':
        $grupoId = (int) ($_GET['grupo_id'] ?? $_POST['grupo_id'] ?? 0);
        if ($grupoId <= 0 || !$grupoModel->esIntegrante($grupoId, $usuarioId)) {
            header('Location: ?');
            exit;
        }
        $tituloPagina = 'Agregar gasto';
        $grupo = $grupoModel->findById($grupoId, $usuarioId);
        $integrantes = $grupoModel->getIntegrantes($grupoId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($grupo['estado'] !== 'activo') {
                $errores[] = 'Este grupo está cerrado y no permite nuevos gastos.';
            }
            $imagenUrl = null;
            if (empty($errores)) {
                if (isset($_FILES['imagen_gasto']) && $_FILES['imagen_gasto']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['imagen_gasto']['error'] !== UPLOAD_ERR_OK) {
                        $errores[] = 'La imagen no pudo subirse. Intenta de nuevo.';
                    } else {
                        $carpetaDestino = __DIR__ . '/uploads/gastos';
                        if (!is_dir($carpetaDestino) && !mkdir($carpetaDestino, 0777, true) && !is_dir($carpetaDestino)) {
                            $errores[] = 'No se pudo crear la carpeta de im�genes.';
                        } else {
                            $extension = strtolower(pathinfo($_FILES['imagen_gasto']['name'], PATHINFO_EXTENSION));
                            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                            if (!in_array($extension, $permitidas, true)) {
                                $errores[] = 'La imagen debe tener formato JPG, PNG, WEBP o GIF.';
                            } else {
                                $nombreArchivo = 'gasto_' . $grupoId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                                $destino = $carpetaDestino . DIRECTORY_SEPARATOR . $nombreArchivo;
                                if (!move_uploaded_file($_FILES['imagen_gasto']['tmp_name'], $destino)) {
                                    $errores[] = 'No se pudo guardar la imagen del gasto.';
                                } else {
                                    $imagenUrl = 'uploads/gastos/' . $nombreArchivo;
                                }
                            }
                        }
                    }
                }
            }
            if (empty($errores)) {
                $expenseModel = new Expense(Database::getInstance());
                $resultado = $expenseModel->create([
                    'grupo_id' => $grupoId,
                    'user_id' => $usuarioId,
                    'concepto' => $_POST['concepto'] ?? '',
                    'informacion' => $_POST['informacion'] ?? '',
                    'imagen_url' => $imagenUrl,
                    'monto' => $_POST['monto'] ?? 0,
                    'fecha' => $_POST['fecha'] ?? '',
                    'pagado_por' => $_POST['pagado_por'] ?? 0,
                    'tipo_division' => $_POST['tipo_division'] ?? 'igual',
                    'participantes' => $_POST['participantes'] ?? [],
                    'monto_personalizado' => $_POST['monto_personalizado'] ?? []
                ]);
                if ($resultado['ok']) {
                    header('Location: ?action=group_detail&id=' . $grupoId);
                    exit;
                }
                $errores[] = $resultado['error'];
            }
        }

        require __DIR__ . '/templates/expenses/create.php';
        break;

    /* ---------- Ver gasto (JSON) ---------- */
    case 'expense_detail':
        header('Content-Type: application/json; charset=utf-8');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Gasto inválido.']);
            exit;
        }
        $expenseModel = new Expense(Database::getInstance());
        $datos = $expenseModel->getById($id, $usuarioId);
        if (!$datos) {
            echo json_encode(['ok' => false, 'error' => 'Gasto no encontrado o sin acceso.']);
            exit;
        }
        echo json_encode(['ok' => true, 'gasto_id' => $id, 'gasto' => $datos['gasto'], 'participantes' => $datos['participantes'], 'resumen' => $datos['resumen']], JSON_UNESCAPED_UNICODE);
        exit;
        break;

    /* ---------- Registrar pago ---------- */
    case 'register_payment':
        $grupoId = 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['gasto_id'] ?? 0);
            $deUsuario = (int) ($_POST['usuario_id'] ?? 0);
            $monto = (float) ($_POST['monto'] ?? 0);
            $informacion = $_POST['informacion'] ?? '';

            $paymentModel = new Payment(Database::getInstance());
            $resultado = $paymentModel->registrar($id, $deUsuario, $monto, $informacion);
            $grupoId = $resultado['ok'] ? $resultado['grupo_id'] : 0;
            $flashMessages[] = $resultado['ok']
                ? 'Pago registrado correctamente.'
                : $resultado['error'];
        }
        if (!empty($flashMessages)) {
            $_SESSION['flash'] = $flashMessages;
        }
        $destino = $grupoId > 0 ? '?action=group_detail&id=' . $grupoId : '?';
        header('Location: ' . $destino);
        exit;
        break;

    /* ---------- Modificar gasto (GET: JSON | POST: guardar) ---------- */
    case 'modify_expense':
        $expenseModel = new Expense(Database::getInstance());

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json; charset=utf-8');
            $id = (int) ($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['ok' => false, 'error' => 'Gasto inválido.']);
                exit;
            }
            echo json_encode($expenseModel->getDatosModificar($id, $usuarioId), JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        $id = (int) ($_POST['gasto_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Gasto inválido.']);
            exit;
        }
        $resultado = $expenseModel->update($id, [
            'concepto' => $_POST['concepto'] ?? '',
            'informacion' => $_POST['informacion'] ?? '',
            'monto' => $_POST['monto'] ?? 0,
            'fecha' => $_POST['fecha'] ?? '',
            'pagado_por' => $_POST['pagado_por'] ?? 0,
            'tipo_division' => $_POST['tipo_division'] ?? 'igual',
            'participantes' => $_POST['participantes'] ?? [],
            'monto_personalizado' => $_POST['monto_personalizado'] ?? []
        ]);
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit;
        break;

    /* ---------- Por defecto ---------- */
    default:
        $tituloPagina = 'Panel de control';
        $grupos = $grupoModel->findByUser($usuarioId);
        require __DIR__ . '/templates/dashboard.php';
        break;
}

require __DIR__ . '/templates/layouts/master.php';