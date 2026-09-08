-- ============================================================
-- sql/pago_multi_moneda.sql
-- Migración idempotente: pagos en cualquier moneda.
--   * Pagos registran su moneda, la tasa USD del momento y el
--     equivalente en US$.
--   * Backfill de pagos legacy (eran en la moneda del gasto).
-- ============================================================

ALTER TABLE pagos ADD COLUMN IF NOT EXISTS moneda VARCHAR(10) NOT NULL DEFAULT 'BS';
ALTER TABLE pagos ADD COLUMN IF NOT EXISTS tasa_usd NUMERIC(12,6) NOT NULL DEFAULT 0;
ALTER TABLE pagos ADD COLUMN IF NOT EXISTS monto_usd NUMERIC(12,2) NOT NULL DEFAULT 0;

-- Datos legacy: los pagos anteriores se registraron en la moneda
-- del gasto. Se les estima su equivalente USD con la tasa del gasto.
UPDATE pagos p
SET moneda = g.moneda,
        tasa_usd = g.tasa_usd,
        monto_usd = ROUND(p.monto * g.tasa_usd, 2)
FROM gastos g
WHERE g.id = p.gasto_id
    AND p.tasa_usd = 0;