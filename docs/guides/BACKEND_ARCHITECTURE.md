# FixItMati Backend Architecture

## 🎯 Design Patterns Implemented

### 1. **Singleton Pattern** ✅
**Location:** 
- `src/Core/Database.php`
- `src/Services/AuthService.php`

**Purpose:** Ensures only one instance of Database connection and AuthService exists throughout the application.

**Benefits:**
- Prevents multiple database connections
- Consistent authentication state
- Resource optimization

**Example:**
```php
$db = Database::getInstance(); // Always returns same instance
$auth = AuthService::getInstance(); // Always returns same instance
```

---

### 2. **Chain of Responsibility Pattern** ✅
**Location:** 
- `src/Middleware/AuthMiddleware.php`
- `src/Middleware/RoleMiddleware.php`

**Purpose:** Passes requests through a chain of handlers (middleware) where each decides to process or pass to next.

**Benefits:**
- Decouples request sender from receiver
- Easy to add/remove middleware
- Clean authentication/authorization flow

**Example:**
```php
$router->addMiddleware(new AuthMiddleware());
$router->addMiddleware(new RoleMiddleware(['admin']));
// Request passes through chain: AuthMiddleware → RoleMiddleware → Controller
```

---

## 📁 Current Project Structure

```
fix-it-mati/
├── src/
│   ├── Core/
│   │   ├── Database.php      ✅ Singleton Pattern
│   │   ├── Router.php         ✅ API Routing
│   │   ├── Request.php        ✅ HTTP Request Handler
│   │   └── Response.php       ✅ HTTP Response Handler
│   │
│   ├── Models/
│   │   └── User.php           ✅ User Model with CRUD
│   │
│   ├── Services/
│   │   └── AuthService.php    ✅ Singleton + Auth Logic
│   │
│   ├── Controllers/
│   │   └── AuthController.php ✅ API Endpoints
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php ✅ Chain of Responsibility
│   │   └── RoleMiddleware.php ✅ Chain of Responsibility
│   │
│   └── autoload.php           ✅ PSR-4 Autoloader
│
├── public/
│   └── api/
│       └── index.php          ✅ API Entry Point
│
└── [existing files...]
```

---

## 🚀 API Endpoints Available

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | Register new user | No |
| POST | `/api/auth/login` | Login user | No |
| POST | `/api/auth/logout` | Logout user | Yes |
| GET | `/api/auth/me` | Get current user | Yes |
| POST | `/api/auth/refresh` | Refresh JWT token | Yes |
| GET | `/api/test` | Test API | No |

---

## 🧪 Testing the API

### 1. Test API is Working
```bash
# Using curl
curl http://localhost:8000/api/index.php/api/test

# Using PowerShell
Invoke-WebRequest -Uri "http://localhost:8000/api/index.php/api/test"
```

### 2. Register a User
```bash
curl -X POST http://localhost:8000/api/index.php/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "full_name": "Test User",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "09123456789"
  }'
```

### 3. Login
```bash
curl -X POST http://localhost:8000/api/index.php/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 4. Get Current User (with token)
```bash
curl http://localhost:8000/api/index.php/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## ✅ Course Requirements Coverage

| Requirement | Status | Implementation |
|-------------|--------|----------------|
| **Design Patterns** | | |
| - Singleton | ✅ | Database, AuthService |
| - Chain of Responsibility | ✅ | AuthMiddleware, RoleMiddleware |
| - Facade | 🔜 Next | ServiceRequestFacade |
| - Observer | 🔜 Next | Notification System |
| - State | 🔜 Next | Request State Machine |
| - Strategy | 🔜 Next | Notification Strategies |
| **API Development** | | |
| - REST API | ✅ | Router with GET/POST/PUT/PATCH/DELETE |
| - API Endpoints | ✅ | Auth endpoints working |
| - JSON Responses | ✅ | Response class |
| **PHP Fundamentals** | | |
| - OOP | ✅ | Classes, namespaces, autoloading |
| - Sessions/Cookies | ✅ | AuthService |
| - Database | ✅ | PDO with PostgreSQL |
| **Security** | | |
| - Authentication | ✅ | Login/Register/JWT |
| - Password Hashing | ✅ | bcrypt |
| - Input Validation | ✅ | Validation in AuthService |

---

## 🔜 Next Steps

1. **Update database schema** - Add password_hash and role columns to users table
2. **Create ServiceRequest model** - For handling service requests
3. **Implement State Pattern** - Request lifecycle (Pending → Assigned → In Progress → Completed)
4. **Implement Observer Pattern** - Notification system for status updates
5. **Implement Facade Pattern** - Simplify complex service request operations
6. **Create more API endpoints** - Requests, Announcements, Payments
7. **Add more middleware** - Rate limiting, CORS, validation

---

## 💡 How to Continue Development

1. **Test what we built:**
   - Start PHP server: `cd public && php -S localhost:8000`
   - Test API endpoint: Visit `http://localhost:8000/api/index.php/api/test`

2. **Update database:**
   - Add `password_hash VARCHAR(255)` column to users table
   - Add `role VARCHAR(50) DEFAULT 'customer'` column to users table

3. **Create more models:**
   - ServiceRequest.php
   - Announcement.php
   - Payment.php

4. **Implement more design patterns** as we build features
