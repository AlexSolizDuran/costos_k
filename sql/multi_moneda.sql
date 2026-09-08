-- ============================================================
-- sql/multi_moneda.sql
-- Migración idempotente para soporte multi-moneda (USDT, Bs, US$).
--   * Tabla de configuración global de tipos de cambio
--   * Moneda y tasa histórica en cada gasto
--   * Conversión de datos legacy existentes (eran en Bs)
-- Asume el esquema de proy_kevin (tabla gastos). Es idempotente:
-- se puede ejecutar varias veces sin romper nada.
-- ============================================================

-- ------------------------------------------------------------
-- 1) CONFIGURACION: tipos de cambio globales (clave-valor)
--    bs_por_usd  = cuántos Bs vale 1 US$
--    bs_por_usdt = cuántos Bs vale 1 USDT
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor NUMERIC(12,6) NOT NULL,
    actualizado TIMESTAMP NOT NULL DEFAULT NOW()
);

INSERT INTO configuracion (clave, valor) VALUES ('bs_por_usd', 6.96), ('bs_por_usdt', 7.30)
ON CONFLICT (clave) DO NOTHING;

-- ------------------------------------------------------------
-- 2) GASTOS: moneda original y tasa de conversión a US$
--    (tasa_usd = cuántos US$ vale 1 unidad de la moneda del gasto)
-- ------------------------------------------------------------
ALTER TABLE gastos ADD COLUMN IF NOT EXISTS moneda VARCHAR(10) NOT NULL DEFAULT 'BS';
ALTER TABLE gastos ADD COLUMN IF NOT EXISTS tasa_usd NUMERIC(12,6) NOT NULL DEFAULT 1;
ALTER TABLE gasto_participantes ADD COLUMN IF NOT EXISTS monto_correspondiente_usd NUMERIC(12,2) NOT NULL DEFAULT 0;
ALTER TABLE gasto_participantes ADD COLUMN IF NOT EXISTS monto_pagado_usd NUMERIC(12,2) NOT NULL DEFAULT 0;

-- ------------------------------------------------------------
-- 3) DATOS LEGACY: los gastos existentes eran en Bs.
--    Se les asigna la tasa vigente (1 US$ en Bs) a los que aún
--    tengan la tasa por defecto (marcador de "no migrado",
--    porque una tasa real de Bs nunca será 1.0).
-- ------------------------------------------------------------
UPDATE gastos SET tasa_usd = 1.0 / GREATEST(
        COALESCE((SELECT valor FROM configuracion WHERE clave = 'bs_por_usd'), 1.0),
        0.000001
    )
WHERE moneda = 'BS' AND tasa_usd = 1;

UPDATE gasto_participantes gp
SET monto_correspondiente_usd = ROUND(gp.monto_correspondiente * g.tasa_usd, 2),
        monto_pagado_usd = ROUND(gp.monto_pagado * g.tasa_usd, 2)
FROM gastos g
WHERE g.id = gp.gasto_id
    AND gp.monto_correspondiente_usd = 0;