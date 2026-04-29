# Laravel Wallet API

A RESTful backend system for wallet management and secure money transfers between users.

---

## Features

- User Registration & Login (Token-based auth)
- Wallet balance management
- Add money to wallet
- Transfer money between users
- Transaction history (sent & received)

---

## Tech Stack

- Laravel (PHP)
- MySQL
- REST API
- Laravel Sanctum (Authentication)
- Postman (API testing)

---

## API Endpoints

### Auth
- POST /api/register
- POST /api/login

### Wallet
- GET /api/wallet
- POST /api/wallet/add

### Transactions
- POST /api/transfer
- GET /api/transactions

---

## Setup Instructions

```bash
git clone https://github.com/YOUR_USERNAME/laravel-wallet-api.git
cd laravel-wallet-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
