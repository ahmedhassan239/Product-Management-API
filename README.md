# Product Management API

This is a Product Management API built with Laravel, providing user authentication, product management, and role-based authorization. This project is designed to be secure, robust, and easy to extend.

## Table of Contents
- [Project Setup](#project-setup)
- [Database Schema](#database-schema)
- [Role-Based Authorization](#role-based-authorization)
- [Design Diagram](#design-diagram)
- [Postman Collection](#postman-collection)
- [Usage](#usage)
- [Unit Testing](#unit-testing)
- [Endpoints](#endpoints)

## Project Setup

To get started with the project, follow the steps below:

### Prerequisites
- PHP 8.x
- Composer
- MySQL or any compatible database
- Node.js and npm (optional, for frontend assets)

### Installation

1. **Clone the Repository:**
    ```bash
    git clone https://github.com/ahmedhassan239/Product-Management-API.git
    cd product-management-api
    ```

2. **Install Dependencies:**
    ```bash
    composer install
    npm install (if using frontend assets)
    ```

3. **Environment Setup:**
    - Copy the `.env.example` to `.env`:
    ```bash
    cp .env.example .env
    ```
    - Update the `.env` file with your database and other environment configurations:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
    ```

4. **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```

5. **Generate JWT Key:**
    ```bash
    php artisan jwt:secret
    ```

6. **Run Migrations:**
    ```bash
    php artisan migrate
    ```

7. **Start the Application:**
    ```bash
    php artisan serve
    ```

## Database Schema

The database schema consists of the following tables:

- **Users:** Stores user data including name, email, password, and role.
- **Addresses:** Stores addresses associated with users.
- **Products:** Stores product information including name, description, prices, and stock quantity.

### Tables:

- **`users`**
  - `id`
  - `name`
  - `email`
  - `password`
  - `role` (values: `User`, `Super Admin`)
  - `created_at`
  - `updated_at`

- **`addresses`**
  - `id`
  - `user_id` (foreign key to `users`)
  - `address_line`
  - `city`
  - `state`
  - `zip_code`
  - `checkpoint`
  - `created_at`
  - `updated_at`

- **`products`**
  - `id`
  - `name`
  - `description`
  - `prices` (stored as JSON)
  - `stock_quantity`
  - `created_at`
  - `updated_at`

## Role-Based Authorization

### Super Admin Role

To be authorized to perform actions such as creating, updating, or deleting users and products, you must register as a "Super Admin". 

Regular users with the role "User" are only authorized to view data but cannot make any modifications.

### Important Note

- Ensure that you register the first user as a **"Super Admin"** to gain full control over the application.
  
## Design Diagram

The project contains a design diagram that illustrates the system's architecture and database schema. You can find the diagram file in the project root:

- **File Name:** `Diagram.drawio`

To view or edit the diagram, you can use the [draw.io](https://www.diagrams.net/) online tool or any compatible application that supports `.drawio` files.

## Postman Collection

A Postman collection is provided to help you test the API endpoints easily. The collection includes all necessary requests for user and product management.

- **File Name:** `postman.json`

To use the Postman collection:

1. Open Postman.
2. Click on the `Import` button.
3. Select the `postman.json` file from the project directory.
4. You can now execute the API requests directly from Postman.

## Usage

### Registering as Super Admin

1. Use the `/users/register` endpoint to create a new user.
2. Make sure the `role` field is set to `"Super Admin"`.

### Example Registration Request (using Postman):

```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "Super Admin",
  "addresses": [
    {
      "address_line": "123 Main St",
      "city": "New York",
      "state": "NY",
      "zip_code": "10001",
      "checkpoint": true
    }
  ]
}
