<?php
$rolActual = $rol ?? ($grupo['mi_rol'] ?? '');
$esAdminGrupo = ($rolActual === 'admin');
?>

<!-- Modal: Modificar grupo -->
<div class="modal-overlay" id="modalModificarGrupo">
    <div class="modal">
        <div class="modal-cabecera">
            <h2>Modificar grupo</h2>
            <button type="button" class="cerrar-modal" onclick="cerrarModal('modalModificarGrupo')"> &times; </button>
        </div>
        <div class="error" id="modificarGrupoError" style="display:none;"></div>
        <form id="formModificarGrupo" method="POST" action="?action=modify_group">
            <input type="hidden" name="id" value="<?= (int) $grupoId ?>">
            <label> Nombre del grupo </label>
            <input type="text" name="nombre" maxlength="150" required>
            <label> Descripción </label>
            <textarea name="descripcion" rows="5"></textarea>
            <div class="modal-acciones">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalModificarGrupo')"> Cancelar </button>
                <button type="submit" class="btn-guardar"> Guardar cambios </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Invitar personas -->
<div class="modal-overlay" id="modalInvitacion">
    <div class="modal">
        <div class="modal-cabecera">
            <h2>Invitar personas al grupo</h2>
            <button type="button" class="cerrar-modal" onclick="cerrarModal('modalInvitacion')"> &times; </button>
        </div>
        <p> Comparte este enlace (o escanea el código QR) para que nuevos usuarios se unan al grupo. </p>

        <div class="seguro-tabs" id="seguroTabs">
            <button type="button" class="seguro-tab activa" data-tab="link" onclick="cambiarTabInvitacion('link')"> 🔗 Link </button>
            <button type="button" class="seguro-tab" data-tab="qr" onclick="cambiarTabInvitacion('qr')"> 📱 QR </button>
        </div>

        <!-- Vista Link -->
        <div id="pestana-link">
            <div class="campo-enlace">
                <input type="text" id="enlaceInvitacion" readonly placeholder="Generando enlace...">
            </div>
            <div class="mensaje-copiado" id="mensajeCopiado"> Enlace copiado al portapapeles </div>
            <div class="acciones-invitacion">
                <button type="button" class="btn-inv btn-inv-copiar" id="btnCopiarEnlace" onclick="copiarEnlace()" disabled>
                    <span class="btn-inv-ico">📋</span> Copiar enlace
                </button>
                <a class="btn-inv btn-inv-whatsapp" id="btnWhatsapp" href="#" target="_blank" style="pointer-events:none; opacity:0.5;">
                    <span class="btn-inv-ico">💬</span> Compartir por WhatsApp
                </a>
                <button type="button" class="btn-inv btn-inv-regenerar" onclick="generarInvitacion()">
                    <span class="btn-inv-ico">🔄</span> Generar enlace nuevo
                </button>
            </div>
        </div>

        <!-- Vista QR -->
        <div id="pestana-qr" style="display:none;">
            <div class="qr-contenedor">
                <div id="qrInvitacion"></div>
            </div>
            <div class="acciones-invitacion">
                <button type="button" class="btn-inv btn-inv-descargar" id="btnDescargarQR" onclick="descargarQR()" disabled>
                    <span class="btn-inv-ico">⬇️</span> Descargar QR
                </button>
                <button type="button" class="btn-inv btn-inv-regenerar" onclick="generarInvitacion()">
                    <span class="btn-inv-ico">🔄</span> Generar enlace nuevo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Agregar integrante -->
<div class="modal-overlay" id="modalAgregarIntegrante">
    <div class="modal">
        <div class="modal-cabecera">
            <h2>Agregar integrante</h2>
            <button type="button" class="cerrar-modal" onclick="cerrarModal('modalAgregarIntegrante')"> &times; </button>
        </div>
        <form method="POST" action="?action=add_member">
            <input type="hidden" name="grupo_id" value="<?= (int) $grupoId ?>">
            <label> Usuario </label>
            <select name="usuario_id" required>
                <option value=""> Selecciona un usuario... </option>
                <?php if (!empty($usuariosCandidatos)): ?>
                    <?php foreach ($usuariosCandidatos as $candidato): ?>
                        <option value="<?= (int) $candidato['id'] ?>">
                            <?= htmlspecialchars($candidato['nombre']) ?> (<?= htmlspecialchars($candidato['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if (empty($usuariosCandidatos)): ?>
                <p style="color:#777; font-size:13px; margin-top:8px;"> No hay usuarios disponibles para agregar. </p>
            <?php endif; ?>
            <div class="modal-acciones">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalAgregarIntegrante')"> Cancelar </button>
                <button type="submit" class="btn-guardar"> Agregar </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Ver gasto -->
<div class="modal-overlay" id="modalGasto">
    <div class="modal">
        <div class="modal-cabecera">
            <h2 id="tituloModalGasto"> Gasto </h2>
            <button type="button" class="cerrar-modal" onclick="cerrarModal('modalGasto')"> &times; </button>
        </div>
        <div id="contenidoGasto"></div>
    </div>
</div>

<!-- Modal: Registrar pago -->
<div class="modal-overlay" id="modalRegistrarPago">
    <div class="modal">
        <div class="modal-cabecera">
            <h2>Registrar pago</h2>
            <button type="button" class="cerrar-modal" onclick="cerrarModal('modalRegistrarPago')"> &times; </button>
        </div>
        <div class="error" id="errorRegistrarPago" style="display:none;"></div>
        <form method="POST" action="?action=register_payment">
            <input type="hidden" name="gasto_id" id="registrarGastoId">
            <input type="hidden" name="usuario_id" id="registrarUsuarioId">
            <label> Monto a pagar (Bs) </label>
            <input type="number" step="0.01" min="0.01" id="registrarMonto" name="monto" required>
            <label> Información adicional (opcional) </label>
            <input type="text" id="registrarInformacion" name="informacion" placeholder="Ej: pagado con pago móvil">
            <p id="ayudaRegistrarPago" style="color:#666; font-size:13px;"></p>
            <div class="modal-acciones">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalRegistrarPago')"> Cancelar </button>
                <button type="submit" class="btn-guardar"> Registrar pago </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Modificar gasto -->
<div class="modal-overlay" id="modalModificarGasto">
    <div class="modal">
        <div class="modal-cabecera">
            <h2>Modificar gasto</h2>
            <button type="button" class="cerrar-modal" onclick="cerrarModal('modalModificarGasto')"> &times; </button>
        </div>
        <div class="error" id="errorModificarGasto" style="display:none;"></div>
        <form id="formModificarGasto">
            <input type="hidden" name="gasto_id" id="modificarGastoId">
            <input type="hidden" name="grupo_id" id="modificarGrupoId">
            <label> Título </label>
            <input type="text" name="concepto" maxlength="150" required>
            <label> Información adicional </label>
            <textarea name="informacion" rows="3"></textarea>
            <label> Monto total (Bs) </label>
            <input type="number" step="0.01" min="0.01" name="monto" id="modificarGastoMonto" required>
            <label> Fecha </label>
            <input type="date" name="fecha" required>
            <label> Pagado por </label>
            <select name="pagado_por" id="modificarPagadoPor"></select>
            <label>Tipo de división</label>
            <div style="display:flex; gap:15px; margin-top:4px;">
                <label style="font-weight:normal; display:flex; align-items:center; gap:6px;">
                    <input type="radio" name="tipo_division" value="igual" style="width:auto;" checked onchange="mostrarDivisionModificar(this.value)"> Igual
                </label>
                <label style="font-weight:normal; display:flex; align-items:center; gap:6px;">
                    <input type="radio" name="tipo_division" value="personalizado" style="width:auto;" onchange="mostrarDivisionModificar(this.value)"> Personalizada
                </label>
            </div>
            <div id="contenedorParticipantesModificar"></div>
            <div class="modal-acciones">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalModificarGasto')"> Cancelar </button>
                <button type="submit" class="btn-guardar"> Guardar cambios </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/qrcode.min.js"></script>
<script>
    const BS_FORMAT = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    function bsFmt(n) { return 'Bs ' + BS_FORMAT.format(n); }
    function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }
    function abrirModal(id) { document.getElementById(id).style.display = 'flex'; }

    /* ---------- Modificar grupo ---------- */
    function abrirModificarGrupo() {
        document.getElementById('formModificarGrupo').reset();
        document.getElementById('modificarGrupoError').style.display = 'none';
        fetch('?action=modify_group&id=<?= (int) $grupoId ?>').then(r => r.json()).then(data => {
            if (data.ok && data.grupo) {
                const g = data.grupo;
                document.querySelector('#formModificarGrupo input[name="id"]').value = g.id;
                document.querySelector('#formModificarGrupo input[name="nombre"]').value = g.nombre;
                document.querySelector('#formModificarGrupo textarea[name="descripcion"]').value = g.descripcion || '';
                abrirModal('modalModificarGrupo');
            } else {
                alert(data.error || 'No se pudo cargar el grupo.');
            }
        });
    }

    /* ---------- Invitación ---------- */
    let qrInvitacion = null;
    let enlaceAbsolutoActual = '';

    function urlAbsoluta(relativa) {
        return window.location.origin + window.location.pathname + relativa;
    }

    function generarInvitacion() {
        const fd = new FormData();
        fd.append('grupo_id', '<?= (int) $grupoId ?>');
        fetch('?action=generate_invitation', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
            if (data.ok) {
                const enlace = urlAbsoluta(data.enlace);
                enlaceAbsolutoActual = enlace;
                document.getElementById('enlaceInvitacion').value = enlace;
                const msj = 'Únete al grupo de gastos: ' + enlace;
                document.getElementById('btnCopiarEnlace').disabled = false;
                const wa = document.getElementById('btnWhatsapp');
                wa.href = 'https://wa.me/?text=' + encodeURIComponent(msj);
                wa.style.pointerEvents = 'auto';
                wa.style.opacity = 1;
                dibujarQR(enlace);
            } else {
                document.getElementById('enlaceInvitacion').value = '';
                alert(data.error || 'No se pudo generar el enlace.');
            }
        }).catch(() => {
            alert('Error de conexión.');
        });
    }
    function dibujarQR(texto) {
        const contenedor = document.getElementById('qrInvitacion');
        contenedor.innerHTML = '';
        if (typeof QRCode === 'undefined') {
            contenedor.innerHTML = '<p style="color:#999; font-size:13px;">Librería QR no disponible.</p>';
            return;
        }
        qrInvitacion = new QRCode(contenedor, {
            text: texto,
            width: 190,
            height: 190,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
        document.getElementById('btnDescargarQR').disabled = false;
    }
    function descargarQR() {
        if (!qrInvitacion || !qrInvitacion._oDrawing) return;
        const canvas = qrInvitacion._oDrawing._elCanvas || qrInvitacion._oDrawing._elImage;
        const enlaceDescarga = document.createElement('a');
        if (canvas && canvas.toDataURL) {
            enlaceDescarga.href = canvas.toDataURL('image/png');
            enlaceDescarga.download = 'invitacion_grupo.png';
        } else {
            return;
        }
        document.body.appendChild(enlaceDescarga);
        enlaceDescarga.click();
        document.body.removeChild(enlaceDescarga);
    }
    function cambiarTabInvitacion(tab) {
        document.querySelectorAll('#seguroTabs .seguro-tab').forEach(function (b) {
            b.classList.toggle('activa', b.getAttribute('data-tab') === tab);
        });
        document.getElementById('pestana-link').style.display = tab === 'link' ? '' : 'none';
        document.getElementById('pestana-qr').style.display = tab === 'qr' ? '' : 'none';
    }
    function abrirInvitacion() {
        document.getElementById('enlaceInvitacion').value = '';
        document.getElementById('mensajeCopiado').style.display = 'none';
        document.getElementById('btnCopiarEnlace').disabled = true;
        const wa = document.getElementById('btnWhatsapp');
        wa.href = '#';
        wa.style.pointerEvents = 'none';
        wa.style.opacity = 0.5;
        document.getElementById('btnDescargarQR').disabled = true;
        document.getElementById('qrInvitacion').innerHTML = '';
        cambiarTabInvitacion('link');
        abrirModal('modalInvitacion');
        generarInvitacion();
    }
    function copiarEnlace() {
        const campo = document.getElementById('enlaceInvitacion');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(campo.value).then(function () {
                document.getElementById('mensajeCopiado').style.display = 'block';
            });
        } else {
            campo.select();
            campo.setSelectionRange(0, 99999);
            document.execCommand('copy');
            document.getElementById('mensajeCopiado').style.display = 'block';
        }
    }
    function abrirAgregarIntegrante() {
        document.getElementById('modalAgregarIntegrante').style.display = 'flex';
    }

    /* ---------- Ver gasto ---------- */
    function mostrarEstadoPago(estado) {
        const m = { pendiente: 'Pendiente', parcial: 'Pendiente', pagado: 'Pagado' };
        const c = { pendiente: 'estado-pendiente', parcial: 'estado-parcial', pagado: 'estado-pagado' };
        return '<span class="estado ' + (c[estado] || 'estado-pendiente') + '">' + (m[estado] || estado) + '</span>';
    }
    function abrirGasto(id) {
        const cont = document.getElementById('contenidoGasto');
        cont.innerHTML = '<p>Cargando...</p>';
        abrirModal('modalGasto');
        fetch('?action=expense_detail&id=' + id).then(r => r.json()).then(data => {
            if (!data.ok) { cont.innerHTML = '<p>' + data.error + '</p>'; return; }
            const g = data.gasto;
            const participantes = data.participantes;
            const resumen = data.resumen;
            document.getElementById('tituloModalGasto').textContent = g.titulo;
            let filas = '';
            const pagador = Number(g.pagado_por);
            participantes.forEach(function (p) {
                const idP = Number(p.usuario_id);
                const pendiente = Math.max(0, (Number(p.monto_correspondiente) - Number(p.monto_pagado)));
                const puedePagar = (idP !== pagador) && (Number(p.monto_correspondiente) - Number(p.monto_pagado) > 0.001) && g.estado === 'activo';
                const accion = puedePagar
                    ? '<button type="button" class="accion" onclick="abrirRegistrarPago(' + g.id + ', ' + idP + ', \'' + p.nombre.replace(/'/g, "\\'") + '\', ' + pendiente.toFixed(2) + ')"> Registrar pago </button>'
                    : '';
                filas += '<tr>' +
                    '<td>' + (idP === pagador ? '<strong>' + p.nombre + ' (pagó)</strong>' : p.nombre) + '</td>' +
                    '<td>' + bsFmt(Number(p.monto_correspondiente)) + '</td>' +
                    '<td>' + bsFmt(Number(p.monto_pagado)) + '</td>' +
                    (idP === pagador ? '<td>—</td>' : '<td>' + bsFmt(Math.max(0, Number(p.monto_correspondiente) - Number(p.monto_pagado))) + '</td>') +
                    '<td>' + mostrarEstadoPago(p.estado_pago) + '</td>' +
                    '<td>' + accion + '</td>' +
                    '</tr>';
            });
            const imagenHtml = g.imagen_url
                ? '<div style="margin:12px 0; text-align:center;"><img src="' + g.imagen_url + '" alt="Imagen del gasto" style="max-width:100%; max-height:280px; border-radius:10px; border:1px solid #ddd; background:#fff;"></div>'
                : '';
            document.getElementById('contenidoGasto').innerHTML =
                '<p style="color:#666;">' + (g.informacion || '') + ' &nbsp;|&nbsp; Fecha: ' + g.fecha + '</p>' +
                imagenHtml +
                '<div class="tabla-contenedor"><table>' +
                '<thead><tr><th>Integrante</th><th>Monto</th><th>Pagado</th><th>Pendiente</th><th>Estado</th><th>Acción</th></tr></thead>' +
                '<tbody>' + filas + '</tbody></table></div>' +
                '<p style="margin-top:15px;"><strong>Total pendiente:</strong> ' + bsFmt(Number(resumen.total_pendiente)) + '</p>';
        }).catch(function () { cont.innerHTML = '<p>Error de conexión.</p>'; });
    }

    /* ---------- Registrar pago ---------- */
    function abrirRegistrarPago(gastoId, usuarioId, nombre, pendiente) {
        document.getElementById('errorRegistrarPago').style.display = 'none';
        document.getElementById('registrarGastoId').value = gastoId;
        document.getElementById('registrarUsuarioId').value = usuarioId;
        document.getElementById('registrarMonto').value = pendiente.toFixed(2);
        document.getElementById('registrarInformacion').value = '';
        document.getElementById('registrarMonto').max = pendiente.toFixed(2);
        document.getElementById('ayudaRegistrarPago').textContent = 'Pendiente de ' + nombre + ': ' + bsFmt(pendiente);
        abrirModal('modalRegistrarPago');
    }

    /* ---------- Modificar gasto ---------- */
    let modificarGastos = { integrantes: [], participantes: [] };
    function mostrarDivisionModificar(tipo) {
        const divM = document.getElementById('contenedorParticipantesModificar');
        const radios = document.querySelectorAll('#formModificarGasto input[name="tipo_division"]');
        let html = '<label> Participantes </label>';
        modificarGastos.integrantes.forEach(function (int) {
            const esta = modificarGastos.participantes.some(function (p) { return Number(p.usuario_id) === Number(int.usuario_id); });
            html += '<label style="display:flex; align-items:center; gap:8px; font-weight:normal; cursor:pointer;">' +
                '<input type="checkbox" name="participantes[]" value="' + int.usuario_id + '" style="width:auto;" ' + (esta ? 'checked' : '') + ' onchange="actualizarMontosModificar()"> ' +
                int.nombre +
                (tipo === 'personalizado' ? ' <input type="number" step="0.01" min="0" name="monto_personalizado[' + int.usuario_id + ']" style="width:120px; margin-left:auto;" placeholder="Bs">' : '') +
                '</label>';
        });
        html += '<p id="infoModificarGasto" style="font-size:13px; color:#666;"></p>';
        divM.innerHTML = html;
        if (tipo === 'personalizado') {
            modificarGastos.participantes.forEach(function (p) {
                const campo = divM.querySelector('input[name="monto_personalizado[' + p.usuario_id + ']"]');
                if (campo) campo.value = Number(p.monto_correspondiente).toFixed(2);
            });
        }
        actualizarMontosModificar();
    }
    function actualizarMontosModificar() {
        const tipo = document.querySelector('#formModificarGasto input[name="tipo_division"]:checked').value;
        const info = document.getElementById('infoModificarGasto');
        if (tipo === 'igual') {
            const sel = document.querySelectorAll('#formModificarGasto input[name="participantes[]"]:checked');
            const monto = parseFloat(document.getElementById('modificarGastoMonto').value) || 0;
            if (sel.length > 0) info.textContent = 'Cada participante pagará ' + bsFmt(Math.round(monto * 100 / sel.length) / 100);
            else info.textContent = '';
        } else {
            let total = 0;
            document.querySelectorAll('#formModificarGasto input[name^="monto_personalizado["]').forEach(function (c) { total += parseFloat(c.value) || 0; });
            info.textContent = 'Suma: ' + bsFmt(total);
        }
    }
    function abrirModificarGasto(id) {
        document.getElementById('errorModificarGasto').style.display = 'none';
        fetch('?action=modify_expense&id=' + id).then(r => r.json()).then(data => {
            if (!data.ok) {
                const err = document.getElementById('errorModificarGasto');
                err.textContent = data.error;
                err.style.display = 'block';
                return;
            }
            const g = data.gasto;
            modificarGastos = { integrantes: data.integrantes, participantes: data.participantes };
            document.getElementById('modificarGastoId').value = g.id;
            document.getElementById('modificarGrupoId').value = g.grupo_id;
            document.getElementById('formModificarGasto').reset();
            document.querySelector('#formModificarGasto input[name="concepto"]').value = g.titulo;
            document.querySelector('#formModificarGasto textarea[name="informacion"]').value = g.informacion || '';
            document.getElementById('modificarGastoMonto').value = Number(g.monto).toFixed(2);
            document.querySelector('#formModificarGasto input[name="fecha"]').value = g.fecha;
            const selPagado = document.getElementById('modificarPagadoPor');
            selPagado.innerHTML = '';
            data.integrantes.forEach(function (int) {
                const op = document.createElement('option');
                op.value = int.usuario_id;
                op.textContent = int.nombre;
                selPagado.appendChild(op);
            });
            selPagado.value = String(g.pagado_por);
            if (g.tipo_division === 'personalizado') {
                document.querySelector('#formModificarGasto input[name="tipo_division"][value="personalizado"]').checked = true;
            } else {
                document.querySelector('#formModificarGasto input[name="tipo_division"][value="igual"]').checked = true;
            }
            mostrarDivisionModificar(g.tipo_division);
            abrirModal('modalModificarGasto');
        }).catch(function () {
            const err = document.getElementById('errorModificarGasto');
            err.textContent = 'Error de conexión.';
            err.style.display = 'block';
        });
    }
    document.getElementById('formModificarGasto').addEventListener('submit', function (ev) {
        ev.preventDefault();
        const fd = new FormData(this);
        fetch('?action=modify_expense', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
            if (data.ok) {
                cerrarModal('modalModificarGasto');
                location.reload();
            } else {
                const err = document.getElementById('errorModificarGasto');
                err.textContent = data.error || 'No se pudo guardar el gasto.';
                err.style.display = 'block';
            }
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (ev) {
            if (ev.target === overlay) overlay.style.display = 'none';
        });
    });

    /* ---------- Deudores: expandir detalle ---------- */
    function alternarDeudas(usuarioId) {
        const fila = document.getElementById('deudor-detalle-' + usuarioId);
        if (fila) fila.style.display = fila.style.display === 'none' ? '' : 'none';
    }

    /* ---------- Deudores: abrir ventana flotante de registrar pago ---------- */
    function pagarDeuda(gastoId, usuarioId, nombre, pendiente) {
        abrirRegistrarPago(gastoId, usuarioId, nombre, pendiente);
    }
</script>