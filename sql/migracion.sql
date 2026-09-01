-- ============================================================
-- sql/migracion.sql
-- Migración idempotente para habilitar las funcionalidades
-- completas del sistema de gastos compartidos:
--   * Invitaciones a grupos
--   * Gastos con participantes y división igual/personalizada
--   * Registro de pagos
--   * Administración de usuarios (activar/desactivar)
-- Asume el esquema usado por proy_kevin (tablas: users, grupos,
-- grupo_integrantes, gastos). Se puede ejecutar varias veces.
-- ============================================================

-- ------------------------------------------------------------
-- 1) USERS: desactivación de cuentas (soft delete)
-- ------------------------------------------------------------
ALTER TABLE users ADD COLUMN IF NOT EXISTS activo BOOLEAN NOT NULL DEFAULT TRUE;

-- ------------------------------------------------------------
-- 2) GRUPOS: creador del grupo
-- ------------------------------------------------------------
ALTER TABLE grupos ADD COLUMN IF NOT EXISTS creado_por INTEGER;
ALTER TABLE grupos ADD COLUMN IF NOT EXISTS fecha_modificacion TIMESTAMP;

-- ------------------------------------------------------------
-- 3) GRUPO_INTEGRANTES: fecha de unión
-- ------------------------------------------------------------
ALTER TABLE grupo_integrantes ADD COLUMN IF NOT EXISTS fecha_union TIMESTAMP NOT NULL DEFAULT NOW();

-- ------------------------------------------------------------
-- 4) GASTOS: información completa del gasto
-- ------------------------------------------------------------
ALTER TABLE gastos ADD COLUMN IF NOT EXISTS informacion TEXT;
ALTER TABLE gastos ADD COLUMN IF NOT EXISTS imagen_url VARCHAR(255) NULL;
ALTER TABLE gastos ADD COLUMN IF NOT EXISTS pagado_por INTEGER;
ALTER TABLE gastos ADD COLUMN IF NOT EXISTS tipo_division VARCHAR(20) NOT NULL DEFAULT 'igualitaria';
ALTER TABLE gastos ADD COLUMN IF NOT EXISTS estado VARCHAR(20) NOT NULL DEFAULT 'activo';

-- ------------------------------------------------------------
-- 5) GASTO_PARTICIPANTES: quiénes participan y cuánto pagan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gasto_participantes (
    id SERIAL PRIMARY KEY,
    gasto_id INTEGER NOT NULL REFERENCES gastos(id) ON DELETE CASCADE,
    usuario_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    monto_correspondiente NUMERIC(10,2) NOT NULL DEFAULT 0,
    monto_pagado NUMERIC(10,2) NOT NULL DEFAULT 0,
    estado_pago VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    fecha_pago TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS idx_gasto_participantes_gasto ON gasto_participantes(gasto_id);

-- ------------------------------------------------------------
-- 6) PAGOS: pagos registrados (de quién a quién)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pagos (
    id SERIAL PRIMARY KEY,
    grupo_id INTEGER NOT NULL REFERENCES grupos(id) ON DELETE CASCADE,
    gasto_id INTEGER NOT NULL REFERENCES gastos(id) ON DELETE CASCADE,
    de_usuario INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    a_usuario INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    monto NUMERIC(10,2) NOT NULL DEFAULT 0,
    informacion TEXT,
    fecha TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_pagos_gasto ON pagos(gasto_id);

-- ------------------------------------------------------------
-- 7) INVITACIONES: enlaces de invitación a grupos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invitaciones (
    id SERIAL PRIMARY KEY,
    grupo_id INTEGER NOT NULL REFERENCES grupos(id) ON DELETE CASCADE,
    codigo VARCHAR(64) NOT NULL UNIQUE,
    creado_por INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    creado TIMESTAMP NOT NULL DEFAULT NOW(),
    fecha_expiracion TIMESTAMP NULL,
    usado_por INTEGER REFERENCES users(id) ON DELETE SET NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'activa'
);

CREATE INDEX IF NOT EXISTS idx_invitaciones_codigo ON invitaciones(codigo);

-- ------------------------------------------------------------
-- 8) DATOS LEGACY: los gastos anteriores no tenían pagador ni
-- participantes. Se asume que los pagó su creador y se registra
-- al creador como participante por el monto completo.
-- Idempotente: solo aplica sobre filas aún no migradas.
-- ------------------------------------------------------------
UPDATE gastos SET pagado_por = user_id
WHERE pagado_por IS NULL;

INSERT INTO gasto_participantes (gasto_id, usuario_id, monto_correspondiente, estado_pago)
SELECT g.id, g.pagado_por, g.monto, 'pagado'
FROM gastos g
WHERE g.pagado_por IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM gasto_participantes gp WHERE gp.gasto_id = g.id
  );