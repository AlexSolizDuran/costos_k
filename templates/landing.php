<?php
// templates/landing.php - Página de bienvenida pública (ruta "/")
// Usa el layout master.php y las clases existentes del diseño.
ob_start();
?>
<section class="hero" style="text-align:center; padding:60px 20px 40px;">
    <h1 style="font-size:2.6rem; margin-bottom:15px;">Gestor de Gastos Compartidos</h1>
    <p style="font-size:1.15rem; color:#555; max-width:640px; margin:0 auto 30px; line-height:1.6;">
        Organiza los gastos entre amigos, familia o compañeros. Crea grupos,
        registra gastos y divide cuentas de forma justa, igualitaria o personalizada.
    </p>
    <div class="acciones" style="justify-content:center;">
        <a class="btn btn-negro" href="?action=register" style="font-size:16px; padding:13px 24px;"> Crear cuenta </a>
        <a class="btn btn-azul" href="?action=login" style="font-size:16px; padding:13px 24px;"> Iniciar sesión </a>
    </div>
</section>

<section class="seccion" style="max-width:960px; margin:20px auto;">
    <h2>¿Cómo funciona?</h2>
    <div class="datos-grupo">
        <div class="dato">
            <strong>1 · Crea un grupo</strong>
            Crea un grupo de gastos con tus amigos, familia o equipo de trabajo.
        </div>
        <div class="dato">
            <strong>2 · Registra gastos</strong>
            Añade cada gasto, indica quién pagó y divide la cuenta de forma igualitaria o personalizada.
        </div>
        <div class="dato">
            <strong>3 · Lleva el control</strong>
            Cada participante ve lo que debe y puede registrar sus pagos para saldar las deudas.
        </div>
        <div class="dato">
            <strong>4 · Invita a otros</strong>
            Genera un enlace de invitación y compártelo para que más personas se unan al grupo.
        </div>
    </div>
</section>

<section class="seccion" style="max-width:960px; margin:20px auto; text-align:center;">
    <h2>Comienza ahora</h2>
    <p style="color:#555; margin-bottom:20px;">Regístrate en segundos y crea tu primer grupo de gastos.</p>
    <a class="crear-grupo" href="?action=register"> + Crear mi cuenta </a>
</section>
<?php
$contenidoPagina = ob_get_clean();
?>
