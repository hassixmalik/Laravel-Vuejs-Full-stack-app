# 💼 Mini ERP — Customer, Invoice & Inventory Management System

A lightweight **Mini ERP web application** built using **Laravel 12** and **Vue.js 3**.  
This system allows employees and administrators to manage customers, create invoices, handle product inventory, and track orders — all from a single, user-friendly dashboard.

---

## 🧱 Core Features

- **Role-Based Access Control (RBAC):** Separate access levels for Admins, Managers, and Employees.  
- **Customer Management:** Create and maintain customer records with contact and order history.  
- **Invoice Creation:** Generate and manage invoices with automatic tax calculations.  
- **Order Tracking:** Track product orders per customer or employee.  
- **Inventory Control:** Manage available stock, product details, and reorder thresholds.  
- **Modern UI:** Built with Vue 3 + Tailwind for a fast and responsive experience.

---

## ⚙️ Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue.js 3 with Inertia.js
- **Styling:** Tailwind CSS
- **Database:** MySQL / MariaDB
- **Authentication:** Laravel Breeze with role management

---

## 🧩 Setup Guide

### ⚙️ Set up environment
```bash
cp .env.example .env
php artisan key:generate
🛠 Configure database
Edit the .env file and update your database credentials accordingly.
```
### 📦 Run migrations
bash
Copy code
php artisan migrate

### 🚀 Start the development server
bash
Copy code
php artisan serve
npm run dev

🌐 Open your browser at:
👉 http://localhost:8000

### ✨ Author
Developed by hassixmalik
Built with ❤️ using Laravel & Vue.js
