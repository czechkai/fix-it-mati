# FixItMati API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
Most endpoints require JWT authentication. Include the token in the Authorization header:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

---

## 🔐 Authentication

### POST `/auth/login`
Login to the system.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "eyJ0eXAiOiJKV1Q...",
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "full_name": "John Doe",
    "role": "customer"
  }
}
```

### POST `/auth/register`
Register a new user.

**Request Body:**
```json
{
  "email": "newuser@example.com",
  "password": "securepassword",
  "full_name": "Jane Smith",
  "phone": "09123456789",
  "address": "123 Main St, Mati City"
}
```

---

## 📋 Service Requests

### GET `/requests`
Get all service requests (filtered by user role).

**Query Parameters:**
- `status` (optional): Filter by status (pending, in_progress, completed)
- `category` (optional): Filter by category (water, electricity)

**Response:**
```json
{
  "success": true,
  "data": {
    "requests": [
      {
        "id": "uuid",
        "user_id": "uuid",
        "title": "Water leak in kitchen",
        "description": "Leaking pipe under sink",
        "category": "water",
        "status": "pending",
        "priority": "high",
        "location": "Poblacion, Mati City",
        "created_at": "2024-12-07T10:00:00Z"
      }
    ],
    "count": 5
  }
}
```

### POST `/requests`
Create a new service request.

**Request Body:**
```json
{
  "title": "No water supply",
  "description": "Water has been out since morning",
  "category": "water",
  "priority": "urgent",
  "location": "Dahican, Mati City"
}
```

### GET `/requests/:id`
Get a specific service request by ID.

### PUT `/requests/:id`
Update a service request.

---

## 💳 Payments

### GET `/payments/current`
Get current unpaid bills for authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "bills": [
      {
        "id": "uuid",
        "bill_month": "December 2024",
        "amount": 1250.00,
        "status": "unpaid",
        "due_date": "2024-12-25",
        "items": [
          {
            "description": "Mati Water District - 24 m³",
            "amount": 450.00,
            "category": "water"
          },
          {
            "description": "Davao Light - 128 kWh",
            "amount": 800.00,
            "category": "electricity"
          }
        ]
      }
    ],
    "total_due": 2630.00
  }
}
```

### POST `/payments/process`
Process a payment.

**Request Body:**
```json
{
  "payment_id": "uuid",
  "gateway": "gcash",
  "amount": 1250.00
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment processed successfully",
  "data": {
    "payment": { ... },
    "transaction": { ... },
    "reference_number": "TRX-ABC12345"
  }
}
```

### GET `/payments/history`
Get payment transaction history.

**Query Parameters:**
- `limit` (optional, default: 10): Number of transactions to return

---

## 📢 Announcements

### GET `/announcements`
Get all published announcements.

**Response:**
```json
{
  "success": true,
  "data": {
    "announcements": [
      {
        "id": "uuid",
        "title": "Scheduled Water Interruption",
        "content": "Water supply will be interrupted...",
        "category": "water",
        "type": "maintenance",
        "status": "published",
        "affected_areas": ["Poblacion", "Central"],
        "start_date": "2024-12-10T08:00:00Z",
        "end_date": "2024-12-10T17:00:00Z",
        "created_at": "2024-12-05T10:00:00Z",
        "author_name": "Admin User"
      }
    ],
    "count": 5
  }
}
```

### GET `/announcements/active`
Get currently active announcements (published and within date range).

### GET `/announcements/:id`
Get a specific announcement with comments.

### GET `/announcements/category/:category`
Get announcements by category (water, electricity, general, maintenance).

### POST `/announcements` (Admin only)
Create a new announcement.

**Request Body:**
```json
{
  "title": "New Announcement",
  "content": "Announcement content...",
  "category": "water",
  "type": "news",
  "status": "published",
  "affected_areas": ["Poblacion", "Central"],
  "start_date": "2024-12-10T00:00:00Z",
  "end_date": null
}
```

### PUT `/announcements/:id` (Admin only)
Update an announcement.

### DELETE `/announcements/:id` (Admin only)
Delete an announcement.

### POST `/announcements/comments`
Add a comment to an announcement.

**Request Body:**
```json
{
  "announcement_id": "uuid",
  "comment": "Thank you for the update!"
}
```

---

## 🔧 Technicians

(To be implemented - models exist)

---

## 📊 Status Codes

- `200 OK`: Success
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `500 Internal Server Error`: Server error

---

## 🚀 Quick Test Examples

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test.customer@example.com","password":"password123"}'
```

### Get Current Bills
```bash
curl -X GET http://localhost:8000/api/payments/current \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get Announcements
```bash
curl -X GET http://localhost:8000/api/announcements
```

### Create Service Request
```bash
curl -X POST http://localhost:8000/api/requests \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Water leak",
    "description": "Leaking pipe in bathroom",
    "category": "water",
    "priority": "high",
    "location": "Poblacion, Mati City"
  }'
```

---

## 📝 Data Seeding

Run the seed script to populate the database with sample data:
```bash
php seed-all-data.php
```

This creates:
- 4 users (customer role)
- 4 technicians
- 12 payment bills (with items)
- 5 announcements
- 6+ service requests

---

## 🎯 Next Steps

1. Test all endpoints using the curl examples above
2. Build frontend pages to consume these APIs
3. Implement real payment gateway integrations
4. Add WebSocket support for real-time notifications
