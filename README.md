# POS System - Apex Commerce

A full stack web-based Point of Sale (POS) and inventory management system designed to manage products, warehouse stock, cashier sales, customers, payments, refunds, reports, and stock activities.


![Blade](https://img.shields.io/badge/Blade-FF2D20?style=for-the-badge&logo=laravel&logoColor=white) ![Tailwind
CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-FFD600?style=for-the-badge&logo=alpinedotjs&logoColor=black) 
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Cloudinary](https://img.shields.io/badge/Cloudinary-3448C5?style=for-the-badge&logo=cloudinary&logoColor=white)
![Figma](https://img.shields.io/badge/Figma-F24E1E?style=for-the-badge&logo=figma&logoColor=white)

## Features

### Admin
- Dashboard & sales charts
- Product lists/UOMS & category management
- Inventory & stock movements
- Stock request & approval system
- Stock transfer to cashiers & stock adjustment
- User management & customer sale 
- Reports & sales analytics, Order, top categories/products
- Notifications & activity logs
- Store, tax & receipt settings

### Cashier
- POS sales & checkout/Hold cart
- Product lists/UOMS allocated
- Order History
- Customer management
- Cash & KHQR payments (DEMO)
- Stock request: Restock, New Products
- Loss/damage reporting
- Refund & partial refund
- Receipt & invoice

### System
- Authentication & role management
- UOM (Unit of Measurement)
- VIP 5% & manual discounts
- Tax, gross & net amount
- Stock tracking & movement history
- Real-time search & filtering/Date range
- SummaryCard analytics per Modules & Chart Graphs
- Modern UX & dark mode
- Reusable Blade components
- Laravel ORM optimization
- Cloudinary image upload

## Project Structure
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

### Main Tables

| Table | Purpose |
|---|---|
| `users` | Admin and cashier accounts |
| `categories` | Product categories |
| `products` | Product master data |
| `product_catalog` | Product data name and price eixst |
| `product_uoms` | Product units, prices, stock, and UOM details |
| `customers` | Customer information |
| `orders` | Sales orders |
| `order_items` | Products included in each order |
| `payments` | Payment records |
| `cashier_stocks` | Stock allocated to each cashier |
| `stock_movements` | Stock increase, decrease, transfer, refund, and loss records |
| `stock_activities` | Stock request and activity workflow |
| `notifications` | Admin and cashier notifications |
| `activity_logs` | System action history |
| `settings` | Configuration Shop |
