# 🥩 Sistema de Administración y Pagos - Tienda Carne Manolo

Este documento cubre la instalación y uso del nuevo panel de administración, sistema de pagos con Stripe y generación de facturas.

## 📋 Contenido

- Panel Administrativo completo
- Sistema de Pagos (Stripe)
- Generación de Facturas (HTML + PDF)
- Gestión de Productos y Pedidos
- Dashboard con Estadísticas

---

## 🚀 Instalación Rápida

### 1. Instalar dependencias con Composer

```bash
cd d:\Tortura\TiendaCarnePHP
composer require stripe/stripe-php
composer require tecnickcom/tcpdf
```

Si no tienes Composer instalado, descárgalo desde https://getcomposer.org/

### 2. Crear archivo `.env` (opcional pero recomendado)

Crea un archivo `.env` en la raíz del proyecto:

```env
# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=carne_manolo
DB_USER=root
DB_PASS=

# App
APP_URL=http://localhost:8000

# Stripe (obtén tus claves en https://dashboard.stripe.com)
STRIPE_PUBLIC_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxx
```

### 3. Crear usuario administrador

```bash
php admin/create-user.php
```

Sigue las instrucciones y crea tu usuario admin.

### 4. Inicia el servidor

```bash
php -S localhost:8000
```

---

## 🔐 Acceso al Panel Admin

**URL:** `http://localhost:8000/admin/login.php`

**Usuario de prueba (si lo creaste):** 
- Usuario: admin
- Contraseña: admin123 (o la que hayas definido)

---

## 📊 Funcionalidades del Panel Admin

### Dashboard
- **Estadísticas en tiempo real**
  - Total de pedidos
  - Productos activos
  - Clientes registrados
  - Ingresos totales
- **Últimos 5 pedidos**
- **Top 5 productos más vendidos**

### 📦 Gestión de Pedidos
- Listar todos los pedidos con paginación
- Ver detalles de cada pedido
- Actualizar estado del pedido (Nuevo, Pagado, Preparando, Entregado)
- Generar facturas desde aquí

### 🛒 Gestión de Productos
- Ver todos los productos
- Crear nuevos productos
- Editar productos existentes
- Eliminar productos (se desactivan, no se borran)
- Gestionar: nombre, categoría, precio, peso, badge, descripción, imagen

### 💳 Gestión de Pagos
- Ver historial de pagos
- Estadísticas de pagos
- Transacciones pagadas vs pendientes
- Configuración de Stripe

### 📄 Gestión de Facturas
- Listar todas las facturas
- Ver facturas en HTML (en navegador)
- Descargar facturas en PDF
- Imprimir directamente desde navegador

---

## 💳 Sistema de Pagos (Stripe)

### Obtener Claves de Stripe

1. Crea una cuenta en https://stripe.com
2. Accede al dashboard: https://dashboard.stripe.com
3. Copia tus claves de prueba:
   - **Public Key** (comenzará con `pk_test_`)
   - **Secret Key** (comenzará con `sk_test_`)

### Configurar Stripe en la Tienda

Las claves de Stripe se configuran en el archivo `.env` o como variables de entorno del sistema.

**En Windows (PowerShell):**
```powershell
$env:STRIPE_PUBLIC_KEY = "pk_test_xxxxx"
$env:STRIPE_SECRET_KEY = "sk_test_xxxxx"
```

**En Linux/Mac (.bashrc, .bash_profile, etc):**
```bash
export STRIPE_PUBLIC_KEY=pk_test_xxxxx
export STRIPE_SECRET_KEY=sk_test_xxxxx
```

### Flujo de Pago en la Tienda

1. Cliente agrega productos al carrito
2. En el checkout, se puede integrar el botón de pago con Stripe
3. Una vez pagado, el pedido se marca como "pagado"
4. Se genera automáticamente un registro en la tabla `payments`

### Integración en Carrito (Código de Ejemplo)

Para integrar Stripe en tu página `carrito.php`:

```php
<?php
require_once __DIR__ . '/includes/payment-functions.php';

// Después de crear el pedido
$paymentSession = create_stripe_payment_session(
    $db,
    $orderId,
    $checkoutEmail,
    $summary['items'],
    $summary['total']
);

if ($paymentSession) {
    // Redirigir a Stripe Checkout
    header('Location: ' . $paymentSession['payment_url']);
    exit;
}
?>
```

---

## 📄 Sistema de Facturas

### Generar Facturas

Las facturas se generan automáticamente para cada pedido.

**Formatos soportados:**
- **HTML**: Ver en navegador, imprimir
- **PDF**: Descargar archivo

### Acceder a Facturas

**Desde el panel admin:**
1. Ir a "Facturas"
2. Seleccionar la factura deseada
3. Opción "Ver" para HTML o "PDF" para descargar

**Desde código PHP:**
```php
// HTML
$html = generate_invoice_html($db, $orderId, $store);
echo $html;

// PDF
download_invoice_pdf($db, $orderId, $store);
```

---

## 🗄️ Base de Datos

### Nuevas Tablas Creadas

#### `admins`
Usuarios administradores del sistema
```sql
- id (INT)
- username (VARCHAR)
- password_hash (VARCHAR)
- email (VARCHAR)
- active (TINYINT)
- created_at (TIMESTAMP)
```

#### `payments`
Registros de pagos con Stripe
```sql
- id (INT)
- order_id (INT)
- stripe_session_id (VARCHAR)
- status (VARCHAR: pending, paid)
- amount_cents (INT)
- payment_method (VARCHAR)
- created_at (TIMESTAMP)
- paid_at (TIMESTAMP)
```

---

## 📁 Estructura de Archivos

```
admin/
├── login.php           # Página de login
├── logout.php          # Cerrar sesión
├── index.php           # Dashboard
├── pedidos.php         # Gestión de pedidos
├── productos.php       # Gestión de productos
├── pagos.php           # Gestión de pagos
├── facturas.php        # Gestión de facturas
└── create-user.php     # Script CLI para crear admin

includes/
├── admin-functions.php      # Funciones de admin (CRUD)
├── payment-functions.php    # Funciones de Stripe
├── invoice-functions.php    # Generación de facturas
├── functions.php            # (actualizado con nuevas tablas)
├── app.php                  # Inicialización
├── config.php               # Configuración
├── db.php                   # Conexión BD
├── header.php               # Header HTML
└── footer.php               # Footer HTML
```

---

## 🔑 Funciones Principales

### Autenticación
```php
admin_login($db, $username, $password)      // Login
admin_logout()                               // Logout
admin_require_login()                        // Proteger páginas
admin_create_user($db, $user, $pass, $mail) // Crear usuario
```

### Productos
```php
admin_get_all_products($db)
admin_get_product($db, $id)
admin_create_product($db, $data)
admin_update_product($db, $id, $data)
admin_delete_product($db, $id)
```

### Pedidos
```php
admin_get_all_orders($db, $limit, $offset)
admin_get_order($db, $id)
admin_get_order_items($db, $order_id)
admin_update_order_status($db, $id, $status)
```

### Estadísticas
```php
admin_get_dashboard_stats($db)      // Stats generales
admin_get_revenue_by_date($db, $days)
admin_get_top_products($db, $limit)
```

### Pagos (Stripe)
```php
create_stripe_payment_session($db, $orderId, $email, $items, $total)
verify_payment_session($db, $sessionId)
get_payment_history($db, $limit)
get_payment_stats($db)
```

### Facturas
```php
generate_invoice_html($db, $orderId, $store)
generate_invoice_pdf($db, $orderId, $store)
download_invoice_pdf($db, $orderId, $store)
```

---

## 🐛 Solución de Problemas

### Error: "Composer no encontrado"
Instala Composer: https://getcomposer.org/download/

### Error: "Stripe Keys no configuradas"
Asegúrate de establecer las variables de entorno `STRIPE_PUBLIC_KEY` y `STRIPE_SECRET_KEY`

### Error: "Usuario de admin no funciona"
Verifica que la tabla `admins` fue creada correctamente en la BD. Ejecuta de nuevo `create-user.php`

### Error: "No se puede generar PDF"
Instala TCPDF: `composer require tecnickcom/tcpdf`

---

## 🔒 Seguridad

- ✅ Contraseñas hasheadas con bcrypt
- ✅ Sesiones seguras
- ✅ CSRF protection recomendado (implementar en producción)
- ✅ Validación de entrada en todos los formularios
- ✅ SQL injection prevenido con prepared statements

---

## 📝 Notas

- Este es un sistema de prueba/desarrollo
- Para producción, configura HTTPS
- Implementa autenticación de 2 factores
- Haz backups regulares de la BD
- Usa las claves de producción de Stripe, no las de prueba

---

## 📞 Soporte

Para problemas o preguntas, consulta:
- Documentación de Stripe: https://stripe.com/docs
- Documentación de TCPDF: https://tcpdf.org
- Código fuente en los archivos incluidos

---

**¡Tu tienda está lista para manejar pagos y administración profesional!** 🎉
