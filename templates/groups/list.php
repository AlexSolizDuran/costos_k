<?php
ob_start();
?>
<div class="cabecera">
    <div>
        <h1>Mis Grupos</h1>
        <div class="usuario"> Bienvenido, <?= htmlspecialchars(Auth::getUsuarioNombre()) ?> </div>
    </div>
    <div class="acciones">
        <a class="crear-grupo" href="?action=create_group"> + Crear grupo </a>
    </div>
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
                    <a href="?action=group_expenses&id=<?= (int) $item['id'] ?>"> Ver gastos </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php
$contenidoPagina = ob_get_clean();
?>