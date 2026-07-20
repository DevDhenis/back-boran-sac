-- =============================================================================
-- inventario_demo.sql
-- Ingreso de inventario (con kardex coherente) para 3 productos y sus movimientos.
-- Independiente de los seeders: se ejecuta directo en la BD MySQL del backend.
--
-- Cómo ejecutarlo:
--   Laragon/HeidiSQL: abrir este archivo y "Ejecutar".
--   CLI:  mysql -u root ferremax_sac < database/sql/inventario_demo.sql
--   (ajusta usuario/nombre de la base según tu entorno)
--
-- Cada producto arranca en 0 y su stock se construye SOLO con movimientos.
-- El stock_after es el saldo corrido; el UPDATE final deja products.stock = último saldo.
-- =============================================================================

-- Referencias existentes (unidad, categoría y un empleado responsable opcional).
SET @unit_id := (SELECT id FROM units LIMIT 1);
SET @cat_id  := (SELECT id FROM product_categories LIMIT 1);
SET @emp_id  := (SELECT id FROM employees LIMIT 1);   -- puede quedar NULL si no hay colaboradores

-- ============================ Producto 1 =====================================
INSERT INTO products
  (internal_code, name, description, stock, minimum_quantity, on_promotion,
   unit_price, wholesale_unit_price, wholesale_min_quantity, discount,
   unit_id, product_category_id, status, final_price, created_at, updated_at)
VALUES
  ('SQL-001', 'Martillo de uña 16oz', 'Martillo de carpintero con mango de fibra.',
   0, 5, 0, 19.90, 17.50, 10, 0, @unit_id, @cat_id, 'A', 19.90, NOW(), NOW());
SET @p1 := LAST_INSERT_ID();

INSERT INTO inventory_management
  (product_id, employee_id, movement_type, quantity, reason,
   stock_before, stock_after, movement_date, status, created_at, updated_at)
VALUES
  (@p1, @emp_id, 'inbound',  80, 'Inventario inicial',   0,  80, NOW() - INTERVAL 40 DAY, 'active', NOW(), NOW()),
  (@p1, @emp_id, 'inbound',  20, 'Compra a proveedor',  80, 100, NOW() - INTERVAL 25 DAY, 'active', NOW(), NOW()),
  (@p1, @emp_id, 'outbound', 15, 'Salida por venta',   100,  85, NOW() - INTERVAL 12 DAY, 'active', NOW(), NOW()),
  (@p1, @emp_id, 'return',    5, 'Devolución de cliente', 85, 90, NOW() - INTERVAL  5 DAY, 'active', NOW(), NOW());
UPDATE products SET stock = 90 WHERE id = @p1;

-- ============================ Producto 2 =====================================
INSERT INTO products
  (internal_code, name, description, stock, minimum_quantity, on_promotion,
   unit_price, wholesale_unit_price, wholesale_min_quantity, discount,
   unit_id, product_category_id, status, final_price, created_at, updated_at)
VALUES
  ('SQL-002', 'Cinta métrica 5m', 'Flexómetro de 5 metros con freno.',
   0, 8, 0, 12.50, 10.90, 12, 0, @unit_id, @cat_id, 'A', 12.50, NOW(), NOW());
SET @p2 := LAST_INSERT_ID();

INSERT INTO inventory_management
  (product_id, employee_id, movement_type, quantity, reason,
   stock_before, stock_after, movement_date, status, created_at, updated_at)
VALUES
  (@p2, @emp_id, 'inbound',    120, 'Inventario inicial',      0, 120, NOW() - INTERVAL 38 DAY, 'active', NOW(), NOW()),
  (@p2, @emp_id, 'outbound',    30, 'Salida por venta',      120,  90, NOW() - INTERVAL 18 DAY, 'active', NOW(), NOW()),
  (@p2, @emp_id, 'adjustment',  10, 'Ajuste por conteo físico', 90, 100, NOW() - INTERVAL 4 DAY, 'active', NOW(), NOW());
UPDATE products SET stock = 100 WHERE id = @p2;

-- ============================ Producto 3 =====================================
INSERT INTO products
  (internal_code, name, description, stock, minimum_quantity, on_promotion,
   unit_price, wholesale_unit_price, wholesale_min_quantity, discount,
   unit_id, product_category_id, status, final_price, created_at, updated_at)
VALUES
  ('SQL-003', 'Llave Stillson 14"', 'Llave de tubo ajustable de 14 pulgadas.',
   0, 4, 0, 45.00, 41.00, 6, 0, @unit_id, @cat_id, 'A', 45.00, NOW(), NOW());
SET @p3 := LAST_INSERT_ID();

INSERT INTO inventory_management
  (product_id, employee_id, movement_type, quantity, reason,
   stock_before, stock_after, movement_date, status, created_at, updated_at)
VALUES
  (@p3, @emp_id, 'inbound',  40, 'Inventario inicial',  0, 40, NOW() - INTERVAL 30 DAY, 'active', NOW(), NOW()),
  (@p3, @emp_id, 'inbound',  15, 'Compra a proveedor', 40, 55, NOW() - INTERVAL 16 DAY, 'active', NOW(), NOW()),
  (@p3, @emp_id, 'outbound', 20, 'Salida por venta',   55, 35, NOW() - INTERVAL  6 DAY, 'active', NOW(), NOW());
UPDATE products SET stock = 35 WHERE id = @p3;

-- Verificación rápida (opcional):
--   SELECT p.internal_code, p.stock, COUNT(m.id) AS movimientos
--   FROM products p JOIN inventory_management m ON m.product_id = p.id
--   WHERE p.internal_code IN ('SQL-001','SQL-002','SQL-003')
--   GROUP BY p.id;
