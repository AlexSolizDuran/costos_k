<?php
ob_start();
?>
<div class="modal-overlay" style="display: flex;">
    <div class="modal">
        <div class="modal-cabecera">
            <h2> Crear grupo </h2>
            <a href="?action=dashboard" class="cerrar-modal" style="text-decoration: none;"> × </a>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="error">
                <?php foreach ($errores as $err): ?>
                    <?= htmlspecialchars($err) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="?action=create_group">
            <label> Nombre del grupo </label>
            <input type="text" name="nombre" maxlength="150" value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES) ?>" required autofocus>
            <label> Descripción </label>
            <textarea name="descripcion" rows="5" placeholder="Descripción del grupo (opcional)"><?= htmlspecialchars($_POST['descripcion'] ?? '', ENT_QUOTES) ?></textarea>

            <div class="modal-acciones">
                <a class="btn btn-cancelar" href="?action=dashboard"> Cancelar </a>
                <button type="submit" class="btn-guardar"> Crear grupo </button>
            </div>
        </form>
    </div>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>