# 🥩 Carne Manolo - Tienda Online Premium

Sistema completo de e-commerce para venta de carnes artesanales con **panel administrativo profesional**, **sistema de pagos Stripe** y **generación de facturas PDF**.

## ✨ Características Implementadas

### 🎯 Frente de Tienda (Cliente)
- ✅ Catálogo de productos elegante
- ✅ Carrito de compras persistente  
- ✅ Checkout con datos del cliente
- ✅ Sistema de pedidos
- ✅ Página de contacto
- ✅ Responsive design

### 👨‍💼 Panel Administrativo Profesional
- ✅ Dashboard con estadísticas en tiempo real
- ✅ Gestión completa de productos (CRUD)
- ✅ Gestión de pedidos con estados
- ✅ Historial de pagos y transacciones
- ✅ Generación de facturas (HTML + PDF)
- ✅ Reportes y análisis de ventas

### 💳 Sistema de Pagos
- ✅ Integración con Stripe (tarjetas de crédito)
- ✅ Sesiones de pago seguras
- ✅ Verificación automática de pagos
- ✅ Historial de transacciones
- ✅ Estadísticas de ingresos

### 📄 Facturas Profesionales
- ✅ Generación automática por pedido
- ✅ Visualización en HTML
- ✅ Descarga en PDF
- ✅ Impresión directa
- ✅ Datos completos del pedido y cliente

---

## 🚀 Quick Start

### 1. Instalación de Dependencias
```bash
cd d:\Tortura\TiendaCarnePHP
composer install
```

### 2. Crear Usuario Admin
```bash
php admin/create-user.php
```

### 3. Iniciar Servidor
```bash
php -S localhost:8000
```

### 4. Acceder
- **Tienda**: http://localhost:8000
- **Admin**: http://localhost:8000/admin/login.php

---

## 📊 Dashboard Admin

Acceso a:
- **📊 Dashboard**: Estadísticas generales, últimos pedidos, productos más vendidos
- **📦 Pedidos**: Listar, ver detalles, actualizar estado
- **🛒 Productos**: CRUD completo de productos
- **💳 Pagos**: Historial y estadísticas de transacciones Stripe
- **📄 Facturas**: Ver y descargar facturas en PDF

---

## 🗄️ Base de Datos

### Tablas Principales
- `products` - Catálogo de productos
- `customers` - Clientes registrados
- `orders` - Pedidos realizados
- `order_items` - Artículos por pedido
- `admins` - Usuarios administradores
- `payments` - Pagos y transacciones Stripe
- `contact_messages` - Mensajes de contacto

---

## 📁 Estructura de Carpetas

```
TiendaCarnePHP/
├── admin/
│   ├── login.php          # Login admin
│   ├── index.php          # Dashboard
│   ├── pedidos.php        # Gestión pedidos
│   ├── productos.php      # Gestión productos
│   ├── pagos.php          # Gestión pagos
│   ├── facturas.php       # Gestión facturas
│   └── create-user.php    # Crear admin
├── includes/
│   ├── admin-functions.php      # Funciones admin
│   ├── payment-functions.php    # Funciones Stripe
│   ├── invoice-functions.php    # Generación facturas
│   ├── functions.php            # Funciones generales
│   ├── config.php               # Configuración
│   ├── db.php                   # Base de datos
│   ├── app.php                  # Inicialización
│   ├── header.php               # Header común
│   └── footer.php               # Footer común
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
├── index.php              # Página inicio
├── productos.php          # Catálogo
├── carrito.php            # Carrito
├── contacto.php           # Contacto
├── ADMIN_SETUP.md         # Guía instalación admin
├── composer.json          # Dependencias PHP
├── .env.example           # Variables de entorno
└── sql/schema.sql         # Schema BD
```

---

## 🔐 Autenticación Admin

- **Sistema de login seguro** con bcrypt
- **Protección de páginas** con sesiones
- **Logout limpio**
- **Crear múltiples usuarios admin**

Ejemplo:
```php
// Crear usuario
admin_create_user($db, 'admin', 'password123', 'admin@carnemanolo.test');

// Login
admin_login($db, 'admin', 'password123');

// Proteger página
admin_require_login();

// Logout
admin_logout();
```

---

## 💳 Integración Stripe

### Configurar Claves Stripe

1. Crea cuenta en https://stripe.com
2. Obtén claves de prueba en dashboard
3. Configura en `.env`:

```env
STRIPE_PUBLIC_KEY=pk_test_xxxxx
STRIPE_SECRET_KEY=sk_test_xxxxx
```

### Flujo de Pago
```
Cliente → Carrito → Checkout → Stripe Session → Pago → Confirmación → Factura
```

---

## 📄 Facturas

### Acceso
- **Admin**: Panel → Facturas → Ver/PDF
- **URL directa**: `/admin/facturas.php?id=123`

### Características
- Datos del cliente y tienda
- Detalles de cada artículo
- Totales y cálculos
- Estado del pago
- Impresión optimizada
- PDF descargable

---

## 🎯 Estadísticas del Dashboard

El dashboard muestra en tiempo real:
- Total de pedidos
- Total de ingresos
- Productos activos
- Clientes registrados
- Pedidos últimos 7 días
- Top 5 productos vendidos

---

## 🛠️ Funciones Principales

### Gestión Productos
```php
admin_get_all_products($db)                  // Todos
admin_create_product($db, $data)             // Crear
admin_update_product($db, $id, $data)        // Editar
admin_delete_product($db, $id)               // Eliminar
admin_get_product($db, $id)                  // Obtener
```

### Gestión Pedidos
```php
admin_get_all_orders($db, $limit, $offset)   // Listar
admin_get_order($db, $id)                    // Detalle
admin_get_order_items($db, $order_id)        // Items
admin_update_order_status($db, $id, $status) // Cambiar estado
admin_get_total_orders_count($db)            // Contar
```

### Pagos Stripe
```php
create_stripe_payment_session(...)  // Crear sesión
verify_payment_session($db, $id)    // Verificar
get_payment_history($db)            // Historial
get_payment_stats($db)              // Estadísticas
```

### Facturas
```php
generate_invoice_html($db, $id, $store)      // HTML
generate_invoice_pdf($db, $id, $store)       // PDF
download_invoice_pdf($db, $id, $store)       // Descargar
```

---

## 🚨 Requisitos

- PHP 7.4+
- MySQL/MariaDB 5.7+
- Composer
- Conexión HTTPS (recomendado para producción)

---

## 📦 Dependencias

```bash
composer require stripe/stripe-php
composer require tecnickcom/tcpdf
```

---

## 📚 Documentación Completa

Para detalles completos de instalación, configuración y troubleshooting:

👉 **Ver [ADMIN_SETUP.md](ADMIN_SETUP.md)**

---

## 🎓 Ejemplos de Uso

### Crear un Producto Programáticamente
```php
$product = admin_create_product($db, [
    'name' => 'Entrecot Madurado',
    'category' => 'Premium',
    'price' => 18.90,
    'weight' => '500g',
    'badge' => 'Top Venta',
    'description' => 'Descripción...',
    'image' => 'assets/images/entrecot.png',
]);
```

### Actualizar Estado de Pedido
```php
admin_update_order_status($db, 123, 'entregado');
```

### Generar Factura
```php
$invoice = generate_invoice_html($db, 123, $store);
echo $invoice; // Mostrar en navegador
```

---

## 🔒 Seguridad en Producción

- ✅ Usar HTTPS
- ✅ Cambiar credenciales por defecto
- ✅ Implementar 2FA
- ✅ Hacer backups regulares
- ✅ Usar claves Stripe en vivo
- ✅ Validar entrada en servidor
- ✅ Implementar CSRF tokens

---

## 💬 Estados de Pedido

- **nuevo** - Pedido creado, esperando pago
- **pagado** - Pago confirmado
- **preparando** - Preparando en tienda
- **entregado** - Entregado al cliente

---

## 📞 Contacto y Soporte

- **Email**: pedidos@carnemanolo.test
- **Documentación**: Ver archivos markdown
- **Admin**: http://localhost:8000/admin/login.php

---

## 📝 Licencia

MIT License - Libre para usar y modificar

---

## 🎉 ¡Listo para Vender!

Tu tienda online está completamente equipada con un sistema profesional de administración, pagos y facturas.

**Próximos pasos:**
1. ✅ Instalar dependencias (Composer)
2. ✅ Crear usuario admin
3. ✅ Configurar Stripe
4. ✅ Comenzar a vender

---

**Hecho con ❤️ para Carne Manolo**
