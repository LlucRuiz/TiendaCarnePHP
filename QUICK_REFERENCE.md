# ⚡ Quick Reference - URLs y Comandos

## 🌐 URLs de la Tienda

### Tienda (Cliente)
```
http://localhost:8000/                  - Página de inicio
http://localhost:8000/productos.php     - Catálogo
http://localhost:8000/carrito.php       - Carrito
http://localhost:8000/contacto.php      - Contacto
```

### Panel Admin
```
http://localhost:8000/admin/login.php       - Login
http://localhost:8000/admin/index.php       - Dashboard
http://localhost:8000/admin/pedidos.php     - Pedidos
http://localhost:8000/admin/productos.php   - Productos
http://localhost:8000/admin/pagos.php       - Pagos
http://localhost:8000/admin/facturas.php    - Facturas
http://localhost:8000/admin/logout.php      - Salir
```

---

## 🖥️ Comandos Terminal (PowerShell)

### Instalación
```powershell
# Instalar dependencias
cd d:\Tortura\TiendaCarnePHP
composer install

# Actualizar dependencias
composer update
```

### Crear Usuario Admin
```powershell
php admin/create-user.php
```

### Iniciar Servidor
```powershell
# Opción 1: Simple
php -S localhost:8000

# Opción 2: Con script composer
composer run serve

# Opción 3: En directorio específico
php -S localhost:8000 -t "d:\Tortura\TiendaCarnePHP"
```

### Variables de Entorno (en PowerShell)
```powershell
# Establecer variables
$env:STRIPE_PUBLIC_KEY = "pk_test_xxxxx"
$env:STRIPE_SECRET_KEY = "sk_test_xxxxx"
$env:DB_HOST = "127.0.0.1"
$env:DB_USER = "root"

# Verificar variable
$env:STRIPE_PUBLIC_KEY
```

---

## 📋 Flujos Comunes

### 1️⃣ Setup Inicial
```bash
1. composer install
2. php admin/create-user.php
3. php -S localhost:8000
4. Abrir http://localhost:8000
```

### 2️⃣ Crear Nuevo Producto
```
1. Ir a /admin/productos.php
2. Clic en "+ Nuevo Producto"
3. Llenar formulario
4. Clic en "Crear Producto"
```

### 3️⃣ Ver Pedido en Detalle
```
1. Ir a /admin/pedidos.php
2. Clic en "Ver" del pedido
3. Ver todos los detalles y artículos
4. Cambiar estado si es necesario
```

### 4️⃣ Generar Factura PDF
```
1. Ir a /admin/facturas.php
2. Buscar la factura
3. Clic en "PDF" para descargar
4. O "Ver" para abrir en navegador
```

### 5️⃣ Verificar Pagos
```
1. Ir a /admin/pagos.php
2. Ver historial de pagos
3. Estadísticas: Pagados vs Pendientes
4. Verificar configuración de Stripe
```

---

## 🗄️ Operaciones Base de Datos

### Backup
```bash
# MySQL backup (desde cmd/powershell)
mysqldump -u root -p carne_manolo > backup.sql

# Con XAMPP
cd "C:\xampp\mysql\bin"
mysqldump -u root carne_manolo > "d:\backup.sql"
```

### Restore
```bash
mysql -u root -p carne_manolo < backup.sql
```

### Ver Tablas
```bash
mysql -u root -p -e "USE carne_manolo; SHOW TABLES;"
```

---

## 🔑 Configuración Stripe

### Obtener Claves
1. Ir a https://dashboard.stripe.com
2. Click en "Developers" (esquina inferior)
3. Click en "API keys"
4. Copiar "Publishable key" (pk_test_xxx)
5. Copiar "Secret key" (sk_test_xxx)

### Configurar en .env
```env
STRIPE_PUBLIC_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxx
```

### Verificar Configuración
```php
<?php
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
echo "✓ Stripe configurado correctamente";
?>
```

---

## 📱 Dispositivos Prueba Stripe

### Tarjetas de Prueba
| Número | Resultado |
|--------|-----------|
| 4242 4242 4242 4242 | Éxito |
| 4000 0000 0000 0002 | Rechazada |
| 4000 0000 0000 0069 | Expirada |
| 4000 0000 0000 9995 | Declinada (llamada) |

- **Fecha**: Cualquier fecha futura (ej: 12/25)
- **CVC**: Cualquier 3 números (ej: 123)

---

## 🐛 Troubleshooting Rápido

### Error: "Class not found: Stripe"
```bash
composer require stripe/stripe-php
composer dump-autoload
```

### Error: "TCPDF not found"
```bash
composer require tecnickcom/tcpdf
```

### Error: "Tabla 'admins' no existe"
```bash
# Ejecutar en PHP
php admin/create-user.php
# Esto crea las tablas automáticamente
```

### Error: "Admin login no funciona"
```bash
# Verificar tabla existe
mysql -u root -p carne_manolo
> SELECT * FROM admins;

# Si no existe, recrear desde app.php
```

### Error: "Stripe keys no configuradas"
```bash
# Verificar en Windows
echo %STRIPE_PUBLIC_KEY%

# Verificar en PHP
<?php var_dump(getenv('STRIPE_PUBLIC_KEY')); ?>
```

---

## 📊 Estadísticas SQL

### Total Ingresos
```sql
SELECT SUM(total) FROM orders;
```

### Pedidos por Estado
```sql
SELECT status, COUNT(*) FROM orders GROUP BY status;
```

### Productos Más Vendidos
```sql
SELECT p.name, SUM(oi.quantity) as vendidos 
FROM order_items oi 
JOIN products p ON p.id = oi.product_id 
GROUP BY p.id 
ORDER BY vendidos DESC;
```

### Clientes por Pedidos
```sql
SELECT c.name, COUNT(*) as pedidos 
FROM orders o 
JOIN customers c ON c.id = o.customer_id 
GROUP BY c.id 
ORDER BY pedidos DESC;
```

---

## 🛒 Estados Pedido

| Estado | Significado | Color |
|--------|-------------|-------|
| nuevo | Creado, esperando pago | Azul |
| pagado | Pago confirmado | Verde |
| preparando | En preparación | Naranja |
| entregado | Entregado | Gris |

---

## 📁 Rutas Importantes

```
Proyecto: d:\Tortura\TiendaCarnePHP\
Admin: d:\Tortura\TiendaCarnePHP\admin\
DB Config: d:\Tortura\TiendaCarnePHP\includes\db.php
Funciones: d:\Tortura\TiendaCarnePHP\includes\
Assets: d:\Tortura\TiendaCarnePHP\assets\
```

---

## 🎯 Atajos Útiles

### PHP Server
```bash
# Windows CMD
cd /d d:\Tortura\TiendaCarnePHP && php -S localhost:8000

# PowerShell
cd d:\Tortura\TiendaCarnePHP; php -S localhost:8000

# Con otro puerto
php -S localhost:9000

# Con hostname
php -S 192.168.1.100:8000
```

### Composer
```bash
# Ver versiones instaladas
composer show

# Autoload dump
composer dump-autoload

# Ver info paquete
composer info stripe/stripe-php
```

---

## 🔍 Buscar en Código

### Localizar Funciones
```bash
# En PowerShell con grep
Get-ChildItem includes\*.php | Select-String "function admin"

# En Linux/Mac
grep -r "function admin" includes/
```

---

## 📱 Acceso Remoto

Para acceder desde otro dispositivo:

```bash
# Ver IP local
ipconfig

# Usar en otra máquina
http://192.168.X.X:8000
```

---

## 🌐 Producción

### Cambiar a Claves Live
```env
# En .env cambiar a:
STRIPE_PUBLIC_KEY=pk_live_xxxxxxxxxxxxx
STRIPE_SECRET_KEY=sk_live_xxxxxxxxxxxxx
```

### URLs en Producción
```
https://tudominio.com/
https://tudominio.com/admin/login.php
```

---

## 📞 Ayuda Rápida

| Problema | Solución |
|----------|----------|
| No carga la página | ¿Está el servidor? `php -S localhost:8000` |
| Error de BD | ¿MySQL está corriendo? ¿Datos correctos en config? |
| Login no funciona | ¿Usuario admin creado? `php admin/create-user.php` |
| Stripe error | ¿Claves configuradas? `echo %STRIPE_PUBLIC_KEY%` |
| PDF no genera | ¿TCPDF instalado? `composer require tecnickcom/tcpdf` |

---

## 📚 Recursos Útiles

- **Stripe Docs**: https://stripe.com/docs
- **TCPDF**: https://tcpdf.org
- **PHP Manual**: https://www.php.net/manual
- **MySQL**: https://dev.mysql.com/doc

---

**Hoja de Trucos - Imprímela y tenla a mano! 📌**
