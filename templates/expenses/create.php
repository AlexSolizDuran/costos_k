<?php
$erroresHtml = '';
if (!empty($errores)) {
    foreach ($errores as $e) {
        $erroresHtml .= '<span class="error">' . htmlspecialchars($e) . '</span><br>';
    }
}

$contenidoPagina = '
<div class="page-header"><h1>Agregar gasto</h1></div>
<div class="auth-container">
    <div class="auth-box">
        ' . $erroresHtml . '
        <form action="?action=create_expense&grupo_id=' . ($grupoId ?? '') . '" method="POST">
            <div class="form-group">
                <label for="concepto">Concepto</label>
                <input type="text" id="concepto" name="concepto" required>
            </div>
            <div class="form-group">
                <label for="monto">Monto</label>
                <input type="number" step="0.01" id="monto" name="monto" required>
            </div>
            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo">
                    <option value="personalizado">Personalizado</option>
                    <option value="igualitario">Igualitario</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Guardar gasto</button>
        </form>
    </div>
</div>';
?>