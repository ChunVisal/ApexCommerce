# 🛒 ApexCommerce — POS & Inventory Management System

A full-stack, web-based Point of Sale (POS) and inventory management platform built for retail operations — managing products, warehouse stock, cashier sales, customers, payments, refunds, reports, and stock activities, It covers the full retail workflow.

<p align="left">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Blade-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" />
  <img src="https://img.shields.io/badge/Alpine.js-FFD600?style=for-the-badge&logo=alpinedotjs&logoColor=black" />
  <img src="https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Cloudinary-3448C5?style=for-the-badge&logo=cloudinary&logoColor=white" />
  <img src="https://img.shields.io/badge/Figma-F24E1E?style=for-the-badge&logo=figma&logoColor=white" />
</p>

---

## ✨ Features

### 🔑 Admin
- Dashboard with sales charts & analytics
- Product list, UOM (unit of measurement), and category management
- Inventory tracking & stock movement history
- Stock request & approval workflow
- Stock transfer to cashiers & stock adjustment
- User management & customer sales overview
- Reports: daily sales analytics, orders tables, top cashier, payment overview
- Notifications & activity logs
- Store, discount, tax & receipt configuration

### 🧾 Cashier
- POS sales & checkout with hold-cart support
- Allocated product list & UOM view
- Order history
- Customer management
- Cash & KHQR payments *(demo)*
- Stock requests: restock & new product
- Loss/damage reporting
- Full & partial refunds
- Receipt & invoice generation

### ⚙️ System
- Authentication & role-based access control
- UOM (Unit of Measurement) support
- Config VIP % & manual discounts
- Tax, discount, gross & net amount calculation
- Stock tracking & movement history
- Real-time search, pagination, filtering & date-range queries
- Summary card analytics per module + chart graphs
- Dark mode & modern UX
- Reusable Blade components
- Optimized Laravel ORM queries
- Cloudinary image upload

---

## 🏗️ Project MVC Structure

```text
app/
├── Http/
│   └── Controllers/
│       ├── Admin/
│       └── Cashier/
├── Models/
├── Services/
│   ├── Admin/
│   └── Cashier/
└── Traits/

resources/
└── views/
    ├── admin/
    ├── cashier/
    └── components/
```

---

## 🗄️ Database Schema

| Table | Purpose |
|---|---|
| `users` | Admin and cashier accounts |
| `categories` | Product categories |
| `products` | Product master data |
| `product_catalog` | Important Product name & price reference data |
| `product_uoms` | Product units, prices, stock, and UOM details |
| `customers` | Customer information |
| `orders` | Sales orders |
| `order_items` | Products included in each order |
| `payments` | Payment records |
| `cashier_stocks` | Stock allocated to each cashier |
| `stock_movements` | Stock increase, decrease, transfer, refund & loss records |
| `stock_activities` | Stock request & activity workflow |
| `notifications` | Admin and cashier notifications |
| `activity_logs` | System action history |
| `settings` | Shop configuration |

---

## 🚀 Getting Started

```bash
# Clone the repository
git clone https://github.com/ChunVisal/ApexCommerce.git
cd ApexCommerce

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env, then run migrations
php artisan migrate --seed

# Build assets & serve
npm run dev
php artisan serve
```
