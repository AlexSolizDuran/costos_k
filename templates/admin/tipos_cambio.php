<?php
// templates/admin/tipos_cambio.php - Tipos de cambio globales
ob_start();
$esAdminSistema = Auth::esAdmin();
?>
<div class="cabecera">
    <div>
        <a class="volver" href="?action=dashboard"> ← Panel </a>
        <h1 class="titulo"> Tipos de cambio </h1>
    </div>
</div>

<div class="seccion">
    <h2>Configuración de monedas</h2>
    <p class="descripcion">
        Los gastos se pueden registrar en <strong>USDT</strong>, <strong>Bolivianos (Bs)</strong> o
        <strong>Dólares americanos físicos (US$)</strong>. La conciliación final se hace en US$.
        Estas tasas se usan como valor por defecto al registrar gastos y pagos, y para mostrar todos
        los montos en una moneda común. 1 US$ físico <strong>no</strong> se asume igual a 1 USDT:
        su relación se deriva de estas dos tasas.
    </p>
    <br>

    <?php if (!$esAdminSistema): ?>
        <div class="flash-info">
            Solo un administrador del sistema puede modificar estas tasas. Se muestran a todos los
            integrantes para que conozcan los valores vigentes.
        </div>
    <?php endif; ?>

    <form class="campo-form" method="POST" action="?action=config_moneda">
        <label for="bs_por_usd">Cambio: Bs por 1 US$ (dólar físico)</label>
        <input type="number"
               id="bs_por_usd"
               name="bs_por_usd"
               step="0.01"
               min="0.000001"
               value="<?= e(number_format((float) $tasas['bs_por_usd'], 6, '.', '')) ?>"
               <?= $esAdminSistema ? '' : 'disabled' ?>
               placeholder="Ej: 6.96">
        <small>Cuántos Bolivianos cuesta 1 dólar americano físico.</small>

        <label for="bs_por_usdt">Cambio: Bs por 1 USDT</label>
        <input type="number"
               id="bs_por_usdt"
               name="bs_por_usdt"
               step="0.01"
               min="0.000001"
               value="<?= e(number_format((float) $tasas['bs_por_usdt'], 6, '.', '')) ?>"
               <?= $esAdminSistema ? '' : 'disabled' ?>
               placeholder="Ej: 7.30">
        <small>Cuántos Bolivianos cuesta 1 USDT.</small>

        <?php if ($esAdminSistema): ?>
            <button type="submit" class="btn btn-negro">Guardar tipos de cambio</button>
        <?php endif; ?>
    </form>
</div>

<div class="seccion">
    <h2>Valores derivados (referencia)</h2>
    <div class="datos-grupo">
        <div class="dato">
            <strong>1 US$ en Bs</strong>
            <?= e(number_format((float) $tasas['bs_por_usd'], 2, '.', ',')) ?>
        </div>
        <div class="dato">
            <strong>1 USDT en Bs</strong>
            <?= e(number_format((float) $tasas['bs_por_usdt'], 2, '.', ',')) ?>
        </div>
        <div class="dato">
            <strong>1 USDT en US$</strong>
            <?= e(number_format((float) $tasas['bs_por_usdt'] / $tasas['bs_por_usd'], 4, '.', ',')) ?>
        </div>
        <div class="dato">
            <strong>1 Bs en US$</strong>
            <?= e(number_format(1.0 / (float) $tasas['bs_por_usd'], 4, '.', ',')) ?>
        </div>
    </div>
</div>
<?php
$contenidoPagina = ob_get_clean();
?>