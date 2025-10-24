# Customer Management Web App

A lightweight customer management web application built using **Laravel 12** and **Vue.js 3**.  
This project provides a modern and responsive interface for managing customers, invoices, and orders — designed for small to medium-sized businesses.

---

## 🧱 Tech Stack

- **Backend:** Laravel 12 (PHP 8+)
- **Frontend:** Vue.js 3 with Inertia.js
- **Styling:** Tailwind CSS
- **Database:** MySQL
- **Authentication:** Laravel Breeze (with role-based access control - RBAC)

---

## 🚀 Features

- **User Authentication:** Secure registration and login using Laravel Breeze.
- **Role-Based Access Control (RBAC):** Admin, Manager, and Employee roles with permission-based access.
- **Customer Management:** Create, update, and track customer profiles.
- **Invoice Management:** Generate and view invoices with VAT calculation.
- **Order Tracking:** Each employee can manage their own invoices and customer orders.
- **Search & Filters:** Quickly find customers, invoices, or orders.
- **Responsive Design:** Works smoothly on desktop and mobile.

---

## ⚙️ Prerequisites

Before setting up, make sure you have:

- **PHP 8.2+** (Laravel 12 requires PHP 8.2 or newer)
- **Composer** (latest version)
- **Node.js & npm** (recommended Node 18+)
- **MySQL / MariaDB**
- **Git**

> Optional but helpful: VS Code with extensions  
> - Laravel Blade Snippets  
> - PHP Intelephense  
> - Vue Language Features (Volar)

---

## 🧩 Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/<your-username>/<your-repo-name>.git
   cd <your-repo-name>
2. **Install dependencies**
   composer install
   npm install
# 🧩 Setup Guide

### ⚙️ Set up environment
```bash
cp .env.example .env
php artisan key:generate
✨ Author

Developed by hassixmalik

Built with ❤️ using Laravel & Vue.js
