<?php
ob_start();
?>
<div class="cabecera">
    <div>
        <a class="volver" href="?action=dashboard"> ← Panel </a>
        <h1 class="titulo"> Eliminar mi cuenta </h1>
    </div>
</div>

<div class="seccion" style="max-width:560px;">
    <h2>Atención</h2>
    <p style="color:#666; line-height:1.6;">
        Si eliminas tu cuenta no podrás volver a iniciar sesión. Tus grupos dejarán de aparecer y tu rol en ellos quedará desactivado.
        Esta acción es <strong>permanente</strong>: podrás registrarte de nuevo, pero no se restaurarán tus datos.
    </p>

    <div style="margin-top:20px; padding:15px; background:#ffe5e5; border-radius:8px; color:#b00000;">
        <strong>Importante:</strong> si eres administrador de un grupo, otro administrador debe asumir el control antes de eliminar tu cuenta.
    </div>

    <div class="acciones" style="margin-top:22px;">
        <a class="btn btn-gris" href="?action=dashboard"> Cancelar </a>
        <a class="btn btn-rojo" href="?action=eliminar_cuenta&confirmar=1"
           onclick="return confirm('¿Seguro que quieres eliminar tu cuenta para siempre?');"> Eliminar mi cuenta </a>
    </div>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>