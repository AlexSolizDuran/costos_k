<?php
$contenidoPagina = '
<div class="page-header"><h1>Crear grupo</h1></div>
<div class="auth-container">
    <div class="auth-box">
        <form action="?action=create_group" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre del grupo</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripcion</label>
                <input type="text" id="descripcion" name="descripcion">
            </div>
            <button type="submit" class="btn-primary">Crear grupo</button>
        </form>
    </div>
</div>';
?>