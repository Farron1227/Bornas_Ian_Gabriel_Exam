# Database Documentation

## Overview
This application uses **SQLite** as the database system for local development. All data is stored in `database/database.sqlite`.

## Database Setup

### Initial Setup
```bash
# Run migrations and seed the database
php artisan migrate:fresh --seed
```

### Database Configuration
Location: `database/database.sqlite`
- Database Type: SQLite
- Connection Name: `sqlite`
- Total Tables: 15

## Database Schema

### Users Table
Stores user account information.

**Columns:**
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `role` - User role (`customer` or `admin`)
- `is_active` - Account active status (boolean)
- `email_verified_at` - Email verification timestamp
- `remember_token` - Remember me token
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

**Default Users:**
- **Admin**: admin@purplebug.com / password
- **Customer**: juan.delacruz@gmail.com / password

### Categories Table
Product categories for organization.

**Columns:**
- `id` - Primary key
- `name` - Category name
- `description` - Category description
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

**Default Categories:**
- Electronics
- Clothing

### Products Table
Product inventory and details.

**Columns:**
- `id` - Primary key
- `name` - Product name
- `description` - Product description
- `price` - Product price (decimal)
- `stock` - Available stock quantity
- `category_id` - Foreign key to categories
- `image` - Product image path (stored in `storage/app/public/products`)
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

**Relationships:**
- Belongs to Category

**Default Products:** 8 sample products (Wireless Headphones, Smart Watch, Bluetooth Speaker, Cotton T-Shirt, Denim Jeans, Summer Dress, USB-C Cable, Phone Case)

### Cart Items Table
Shopping cart items for authenticated users.

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `product_id` - Foreign key to products
- `quantity` - Item quantity
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

**Relationships:**
- Belongs to User
- Belongs to Product

### Orders Table
Customer orders.

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `total` - Order total amount (decimal)
- `status` - Order status (`pending`, `for_delivery`, `delivered`, `canceled`)
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

**Relationships:**
- Belongs to User
- Has many Order Items

### Order Items Table
Individual items within an order.

**Columns:**
- `id` - Primary key
- `order_id` - Foreign key to orders
- `product_id` - Foreign key to products
- `quantity` - Item quantity
- `price` - Price at time of order (decimal)
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

**Relationships:**
- Belongs to Order
- Belongs to Product

### Personal Access Tokens Table
Laravel Sanctum authentication tokens.

**Columns:**
- `id` - Primary key
- `tokenable_type` - Polymorphic type
- `tokenable_id` - Polymorphic ID
- `name` - Token name
- `token` - Hashed token
- `abilities` - Token abilities (JSON)
- `last_used_at` - Last usage timestamp
- `expires_at` - Token expiration
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

## API Endpoints

All API endpoints are prefixed with `/api` and return standardized JSON responses.

### Response Format

**Success Response:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": { }
}
```

### Authentication Endpoints

#### Register
- **POST** `/api/register`
- **Body:**
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```
- **Response:** User object + authentication token

#### Login
- **POST** `/api/login`
- **Body:**
  ```json
  {
    "email": "admin@purplebug.com",
    "password": "password"
  }
  ```
- **Response:** User object + authentication token

#### Logout
- **POST** `/api/logout`
- **Auth:** Required
- **Response:** Success message

#### Get Current User
- **GET** `/api/user`
- **Auth:** Required
- **Response:** Authenticated user object

### Product Endpoints

#### Get All Products
- **GET** `/api/products`
- **Query Params:**
  - `search` - Search by name or description
  - `sort` - Sort by price (`price_asc`, `price_desc`)
  - `per_page` - Items per page (default: 8)
- **Response:** Paginated products with category

#### Get Single Product
- **GET** `/api/products/{id}`
- **Response:** Product object with category

#### Create Product (Admin)
- **POST** `/api/products`
- **Auth:** Required (Admin only)
- **Body:** (multipart/form-data)
  ```json
  {
    "name": "Product Name",
    "description": "Product description",
    "price": 999.99,
    "stock": 50,
    "category_id": 1,
    "image": "file"
  }
  ```
- **Response:** Created product

#### Update Product (Admin)
- **PUT/PATCH** `/api/products/{id}`
- **Auth:** Required (Admin only)
- **Body:** Same as create (all fields optional)
- **Response:** Updated product

#### Delete Product (Admin)
- **DELETE** `/api/products/{id}`
- **Auth:** Required (Admin only)
- **Response:** Success message

### Cart Endpoints

#### Get Cart
- **GET** `/api/cart`
- **Auth:** Required
- **Response:** Cart items with total

#### Add to Cart
- **POST** `/api/cart`
- **Auth:** Required
- **Body:**
  ```json
  {
    "product_id": 1,
    "quantity": 2
  }
  ```
- **Response:** Cart item

#### Update Cart Item
- **PUT** `/api/cart/{id}`
- **Auth:** Required
- **Body:**
  ```json
  {
    "quantity": 3
  }
  ```
- **Response:** Updated cart item

#### Remove from Cart
- **DELETE** `/api/cart/{id}`
- **Auth:** Required
- **Response:** Success message

#### Clear Cart
- **DELETE** `/api/cart/clear`
- **Auth:** Required
- **Response:** Success message with deleted count

### Order Endpoints

#### Get User Orders
- **GET** `/api/orders`
- **Auth:** Required
- **Response:** User's orders with items

#### Get All Orders (Admin)
- **GET** `/api/orders/admin`
- **Auth:** Required (Admin only)
- **Response:** All orders with user and items

#### Get Single Order
- **GET** `/api/orders/{id}`
- **Auth:** Required
- **Response:** Order with items

#### Create Order
- **POST** `/api/orders`
- **Auth:** Required
- **Description:** Creates order from cart items
- **Response:** Created order

#### Update Order Status (Admin)
- **PUT** `/api/orders/{id}`
- **Auth:** Required (Admin only)
- **Body:**
  ```json
  {
    "status": "for_delivery"
  }
  ```
- **Valid Statuses:** `pending`, `for_delivery`, `delivered`, `canceled`
- **Response:** Updated order

#### Delete Order (Admin)
- **DELETE** `/api/orders/{id}`
- **Auth:** Required (Admin only)
- **Response:** Success message

### User Management Endpoints (Admin)

#### Get All Users
- **GET** `/api/users`
- **Auth:** Required (Admin only)
- **Response:** All users

#### Create User
- **POST** `/api/users`
- **Auth:** Required (Admin only)
- **Body:**
  ```json
  {
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "customer",
    "is_active": true
  }
  ```
- **Response:** Created user

#### Update User
- **PUT** `/api/users/{id}`
- **Auth:** Required (Admin only)
- **Body:** Same as create (all fields optional)
- **Response:** Updated user

#### Delete User
- **DELETE** `/api/users/{id}`
- **Auth:** Required (Admin only)
- **Note:** Cannot delete own account
- **Response:** Success message

## Database Operations

### Resetting the Database
```bash
# Fresh migration (drops all tables and recreates)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Running Seeders Only
```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=ProductSeeder
```

### Database Inspection
```bash
# Show database information
php artisan db:show

# Show table structure
php artisan db:table users
```

### Checking Data via Tinker
```bash
php artisan tinker

# Example queries
>>> User::count()
>>> Product::with('category')->get()
>>> Order::with('user', 'items.product')->latest()->first()
```

## Storage Configuration

Product images are stored in:
- **Physical Location:** `storage/app/public/products/`
- **Public URL:** `/storage/products/`
- **Symlink:** `public/storage` → `storage/app/public`

If images don't display, ensure storage link exists:
```bash
php artisan storage:link
```

## Security Features

1. **Password Hashing:** All passwords hashed using bcrypt
2. **API Token Authentication:** Laravel Sanctum for stateless authentication
3. **Input Validation:** All requests validated with Laravel's validation rules
4. **SQL Injection Protection:** Eloquent ORM prevents SQL injection
5. **Role-Based Access:** Admin/customer role separation
6. **Account Status:** Active/inactive user management

## Error Handling

All controllers use the `ApiResponse` trait for consistent error handling:
- Validation errors (422)
- Not found errors (404)
- Unauthorized errors (401, 403)
- Server errors (500) with logging

## Backup and Restore

### Manual Backup
```bash
# Copy the SQLite file
cp database/database.sqlite database/backup_$(date +%Y%m%d).sqlite
```

### Restore from Backup
```bash
# Replace with backup file
cp database/backup_20260112.sqlite database/database.sqlite
```

## Testing Database

### Create Test Data
Use Laravel factories in tinker:
```bash
php artisan tinker

>>> User::factory()->count(5)->create()
>>> Product::factory()->count(20)->create()
```

### Clear Test Data
```bash
# Clear specific table
php artisan tinker
>>> CartItem::truncate()
>>> Order::truncate()
```
