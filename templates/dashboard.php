<?php
ob_start();
?>
<div class="cabecera">
    <div>
        <h1>Mis grupos</h1>
        <div class="usuario"> Bienvenido, <?= htmlspecialchars(Auth::getUsuarioNombre()) ?> </div>
    </div>
    <div class="acciones">
        <a class="cerrar" href="?action=logout"> Cerrar sesión </a>
    </div>
</div>

<div class="acciones" style="margin-bottom: 25px;">
    <a class="crear-grupo" href="?action=create_group"> + Crear grupo </a>
</div>

<?php if (count($grupos) === 0): ?>
    <div class="sin-grupos">
        <h2> Todavía no tienes grupos </h2>
        <p> Crea tu primer grupo para comenzar a registrar gastos. </p>
    </div>
<?php else: ?>
    <div class="grupos">
        <?php foreach ($grupos as $item): ?>
            <div class="grupo">
                <h2> <?= htmlspecialchars($item['nombre']) ?> </h2>
                <p> <?= nl2br(htmlspecialchars($item['descripcion'] ?? '')) ?> </p>
                <small> Rol: <?= htmlspecialchars($item['integrante_rol']) ?> </small>
                <div class="grupo-links">
                    <a href="?action=group_detail&id=<?= (int) $item['id'] ?>"> Entrar al grupo → </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<hr style="margin: 40px 0;">

<div class="seccion">
    <h2> Cuenta </h2>
    <p> Si eliminas tu cuenta, dejarás de poder iniciar sesión. Tus gastos y movimientos históricos se conservarán. </p>
    <div class="acciones" style="margin-top: 15px;">
        <a class="btn btn-gris" href="?action=cambiar_password"> Cambiar contraseña </a>
        <a class="btn btn-rojo" href="?action=eliminar_cuenta"> Eliminar mi cuenta </a>
    </div>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>