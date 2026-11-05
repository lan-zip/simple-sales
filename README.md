# Sales Management System

A web based sales management system developed using Laravel for PT Halus Ciptanadi.

This project was created to digitalize the field sales workflow. Sales staff can record customer orders directly through the system, while administrators can validate transactions and management can monitor sales reports and stock information.

## About the Project

Previously, sales transactions were recorded manually and later entered again by the administrator. This process could cause delays, duplicate data entry, and make sales information less up to date.

This system provides a centralized workflow where sales activities can be recorded directly through the website.

## User Roles

The system has three user roles.

**Admin**

Manages products, customers, sales accounts, incoming orders, transaction validation, and sales data.

**Sales**

Manages customers, creates sales orders, checks order information, updates transaction status, and uploads proof of product delivery.

**Director**

Monitors sales performance, transaction summaries, stock information, and sales reports.

## Main Features

### Authentication

The system provides login authentication with role based access for Admin, Sales, and Director.

### Dashboard

Each user role has a different dashboard based on their responsibilities.

### Product Management

Administrators can manage product information including product name, category, price, stock, and product status.

### Customer Management

Customer information can be created and managed through the system.

### Sales Order

Sales staff can create orders directly while visiting customers. Order information includes customer data, selected products, quantity, and transaction details.

### Transaction Validation

Administrators can review and validate transactions submitted by sales staff.

### Delivery Confirmation

Sales staff can update the transaction status and upload proof of product delivery.

### Sales Reports

The system provides sales and stock information that can be monitored by management.

## Technology

| Technology   | Usage                         |
| ------------ | ----------------------------- |
| Laravel      | Backend Framework             |
| PHP          | Backend Programming           |
| MySQL        | Database                      |
| Blade        | Template Engine               |
| Tailwind CSS | User Interface                |
| JavaScript   | Frontend Interaction          |
| Laragon      | Local Development Environment |

## Installation

Clone this repository.

```bash
git clone https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
```

Open the project directory.

```bash
cd YOUR-REPOSITORY
```

Install PHP dependencies.

```bash
composer install
```

Create the environment file.

```bash
cp .env.example .env
```

Generate the application key.

```bash
php artisan key:generate
```

Configure your database inside `.env`.

```env
DB_DATABASE=simple_sales
DB_USERNAME=root
DB_PASSWORD=
```

Run database migrations.

```bash
php artisan migrate
```

Start the Laravel development server.

```bash
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000
```

## Project Status

This project is currently developed as a web based sales management system and may continue to receive improvements and additional features.

## Author

Developed as an Informatics project using Laravel.
