<?php
$postParticipantes = $_POST['participantes'] ?? null;
function estaMarcado($usuarioId, $integrantes, $postParticipantes) {
    if (isset($postParticipantes)) {
        return in_array((string) $usuarioId, (array) $postParticipantes, true);
    }
    foreach ($integrantes as $i) {
        if ((int) $i['usuario_id'] === (int) $usuarioId) {
            return true;
        }
    }
    return false;
}
$gastoTipoDivision = ($_POST['tipo_division'] ?? 'igual') === 'personalizado' ? 'personalizado' : 'igual';
ob_start();
?>
<div class="modal-overlay" style="display: flex;">
    <div class="modal" style="max-width: 550px;">
        <div class="modal-cabecera">
            <h2>Agregar gasto</h2>
            <a href="?action=group_detail&id=<?= (int) $grupo['id'] ?>" class="cerrar-modal" style="text-decoration: none;"> &times; </a>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="error-gasto">
                <?php foreach ($errores as $err): ?>
                    <?= htmlspecialchars($err) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="?action=create_expense" enctype="multipart/form-data">
            <input type="hidden" name="grupo_id" value="<?= (int) $grupo['id'] ?>">

            <label> Título del gasto </label>
            <input type="text" name="concepto" maxlength="150" value="<?= htmlspecialchars($_POST['concepto'] ?? '', ENT_QUOTES) ?>" required autofocus>

            <label> Información adicional </label>
            <textarea name="informacion" rows="4" placeholder="Detalle del gasto (opcional)"><?= htmlspecialchars($_POST['informacion'] ?? '', ENT_QUOTES) ?></textarea>

            <label> Imagen o foto (opcional) </label>
            <input type="file" name="imagen_gasto" accept="image/*" capture="environment" style="margin-bottom:8px;">
            <small style="display:block; color:#666; margin-top:-4px; margin-bottom:12px;">Puedes tomar una foto desde el celular o subir una imagen.</small>

            <div style="display:flex; gap:15px;">
                <div style="flex:1;">
                    <label> Monto total (Bs) </label>
                    <input type="number" step="0.01" min="0.01" name="monto" id="montoGasto" value="<?= htmlspecialchars($_POST['monto'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div style="flex:1;">
                    <label> Fecha </label>
                    <input type="date" name="fecha" value="<?= htmlspecialchars($_POST['fecha'] ?? date('Y-m-d'), ENT_QUOTES) ?>" required>
                </div>
            </div>

            <label> Pagado por </label>
            <select name="pagado_por" required>
                <option value=""> Selecciona quien pago... </option>
                <?php foreach ($integrantes as $integrante): ?>
                    <option value="<?= (int) $integrante['usuario_id'] ?>"
                        <?= ((int) ($_POST['pagado_por'] ?? 0) === (int) $integrante['usuario_id'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($integrante['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label> Participantes de este gasto </label>
            <div id="listaParticipantes" style="border:1px solid #eee; border-radius:8px; padding:10px;">
                <?php foreach ($integrantes as $integrante): ?>
                    <label style="display:flex; align-items:center; gap:8px; font-weight:normal; cursor:pointer; padding:4px 0;">
                        <input type="checkbox" name="participantes[]" value="<?= (int) $integrante['usuario_id'] ?>"
                               style="width:auto;" <?= estaMarcado($integrante['usuario_id'], $integrantes, $postParticipantes) ? 'checked' : '' ?>
                               onchange="actualizarAyuda()">
                        <?= htmlspecialchars($integrante['nombre']) ?>
                        <input type="number" step="0.01" min="0" name="monto_personalizado[<?= (int) $integrante['usuario_id'] ?>]"
                               class="monto-personalizado" style="width:120px; margin-left:auto; display:none;"
                               placeholder="Bs" value="<?= htmlspecialchars($_POST['monto_personalizado'][$integrante['usuario_id']] ?? '', ENT_QUOTES) ?>"
                               oninput="actualizarAyuda()">
                    </label>
                <?php endforeach; ?>
            </div>

            <label> Tipo de división </label>
            <div style="display:flex; gap:15px; margin-top:4px;">
                <label style="font-weight:normal; display:flex; align-items:center; gap:6px;">
                    <input type="radio" name="tipo_division" value="igual" style="width:auto;" <?= $gastoTipoDivision === 'igual' ? 'checked' : '' ?> onchange="mostrarDivisionGasto('igual')"> Igual
                </label>
                <label style="font-weight:normal; display:flex; align-items:center; gap:6px;">
                    <input type="radio" name="tipo_division" value="personalizado" style="width:auto;" <?= $gastoTipoDivision === 'personalizado' ? 'checked' : '' ?> onchange="mostrarDivisionGasto('personalizado')"> Personalizada
                </label>
            </div>

            <p id="ayudaDivision" style="font-size:13px; color:#666; margin-top:8px;"></p>

            <div class="modal-acciones">
                <a class="btn btn-cancelar" href="?action=group_detail&id=<?= (int) $grupo['id'] ?>"> Cancelar </a>
                <button type="submit" class="btn-guardar"> Guardar gasto </button>
            </div>
        </form>
    </div>
</div>

<script>
    function mostrarDivisionGasto(tipo) {
        document.querySelectorAll('.monto-personalizado').forEach(function (c) { c.style.display = tipo === 'personalizado' ? 'inline-block' : 'none'; });
        actualizarAyuda();
    }
    function actualizarAyuda() {
        const tipo = document.querySelector('input[name="tipo_division"]:checked').value;
        const ayuda = document.getElementById('ayudaDivision');
        const marcados = document.querySelectorAll('#listaParticipantes input[name="participantes[]"]:checked');
        const monto = parseFloat(document.getElementById('montoGasto').value) || 0;
        if (tipo === 'igual') {
            if (marcados.length > 0) {
                ayuda.textContent = 'Cada participante pagará ' + new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Math.round(monto * 100 / marcados.length) / 100) + ' Bs';
            } else {
                ayuda.textContent = 'Selecciona al menos un participante.';
            }
        } else {
            let total = 0;
            document.querySelectorAll('.monto-personalizado').forEach(function (c) { total += parseFloat(c.value) || 0; });
            ayuda.textContent = 'Suma de montos personalizados: ' + new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total) + ' Bs';
        }
    }

    const tipoInicial = '<?= $gastoTipoDivision ?>';
    if (tipoInicial === 'personalizado') {
        mostrarDivisionGasto('personalizado');
    } else {
        mostrarDivisionGasto('igual');
    }
</script>
<?php
$contenidoPagina = ob_get_clean();
?>