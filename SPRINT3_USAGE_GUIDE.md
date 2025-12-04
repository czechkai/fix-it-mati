# Sprint 3 Usage Guide

## Quick Start

This guide shows how to use the 4 new design patterns implemented in Sprint 3.

---

## 1. Command Pattern - Undo/Redo Operations

### Basic Usage

```php
// Execute a status update command
POST /api/commands/execute
{
    "type": "update_status",
    "request_id": "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
    "status": "in_progress",
    "notes": "Starting work on this request"
}

// Response
{
    "success": true,
    "message": "Command executed successfully",
    "can_undo": true,
    "can_redo": false
}
```

### Undo Operation

```php
// Undo the last command
POST /api/commands/undo

// Response
{
    "success": true,
    "message": "Command undone successfully",
    "can_undo": false,  // No more commands to undo
    "can_redo": true    // Can now redo
}
```

### Redo Operation

```php
// Redo the undone command
POST /api/commands/redo

// Response
{
    "success": true,
    "message": "Command redone successfully",
    "can_undo": true,
    "can_redo": false
}
```

### View Command History

```php
// Get all executed commands
GET /api/commands/history

// Response
{
    "success": true,
    "data": {
        "commands": [
            {
                "description": "Update request status to in_progress",
                "timestamp": "2024-12-15 10:30:00",
                "data": {...}
            }
        ],
        "can_undo": true,
        "can_redo": false
    }
}
```

### Assign Technician Command

```php
POST /api/commands/execute
{
    "type": "assign_technician",
    "request_id": "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
    "technician_id": "018e4ada-1234-5678-abcd-ef0123456789"
}
```

---

## 2. Memento Pattern - State Snapshots

### Create Snapshot

```php
// Save current state
POST /api/snapshots
{
    "request_id": "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
    "label": "Before major update"
}

// Response
{
    "success": true,
    "message": "Snapshot created successfully",
    "data": {
        "timestamp": "2024-12-15T10:30:00Z",
        "label": "Before major update"
    }
}
```

### List Snapshots

```php
// Get all snapshots for a request
GET /api/snapshots?request_id=018e4ad9-8d98-7c89-b8a7-bdc0836b0d77

// Response
{
    "success": true,
    "data": {
        "request_id": "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
        "snapshots": [
            {
                "label": "Before major update",
                "timestamp": "2024-12-15T10:30:00Z"
            },
            {
                "label": "After status change",
                "timestamp": "2024-12-15T11:00:00Z"
            }
        ]
    }
}
```

### Restore from Snapshot

```php
// Restore to previous state
POST /api/snapshots/restore
{
    "request_id": "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
    "index": 0  // First snapshot (oldest)
}

// Response
{
    "success": true,
    "message": "Snapshot restored successfully",
    "data": {
        "status": "pending",
        "priority": "normal",
        "title": "Air conditioning repair",
        ...
    }
}
```

### Delete Snapshot

```php
// Remove a specific snapshot
DELETE /api/snapshots?request_id=018e4ad9-8d98-7c89-b8a7-bdc0836b0d77&index=0

// Response
{
    "success": true,
    "message": "Snapshot deleted successfully"
}
```

---

## 3. Composite Pattern - Batch Operations

### Create Request Group

```php
// Group multiple requests
POST /api/request-groups
{
    "request_ids": [
        "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
        "018e4ada-1234-5678-abcd-ef0123456789",
        "018e4adb-9876-5432-fedc-ba9876543210"
    ],
    "group_name": "Urgent Repairs - Building A"
}

// Response
{
    "success": true,
    "message": "Group created successfully",
    "data": {
        "group_id": "group_6758ab12cd34e",
        "group_name": "Urgent Repairs - Building A",
        "total_requests": 3
    }
}
```

### Update Group Status

```php
// Update status of all requests in group
PATCH /api/request-groups/status
{
    "request_ids": [
        "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
        "018e4ada-1234-5678-abcd-ef0123456789"
    ],
    "status": "in_progress",
    "notes": "Starting all repairs in Building A"
}

// Response
{
    "success": true,
    "message": "Updated 2 requests to status: in_progress",
    "data": {
        "total_updated": 2,
        "request_ids": ["...", "..."],
        "new_status": "in_progress"
    }
}
```

### Get Group Information

```php
// Get details about grouped requests
POST /api/request-groups/info
{
    "request_ids": [
        "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
        "018e4ada-1234-5678-abcd-ef0123456789"
    ]
}

// Response
{
    "success": true,
    "data": {
        "total_requests": 2,
        "request_ids": ["...", "..."],
        "info": "Group: 2 requests"
    }
}
```

### Create Nested Groups

```php
// Create hierarchical group structure
POST /api/request-groups/nested
{
    "groups": [
        {
            "name": "High Priority",
            "request_ids": ["018e4ad9-8d98-7c89-b8a7-bdc0836b0d77"]
        },
        {
            "name": "Normal Priority",
            "request_ids": [
                "018e4ada-1234-5678-abcd-ef0123456789",
                "018e4adb-9876-5432-fedc-ba9876543210"
            ]
        }
    ]
}

// Response
{
    "success": true,
    "message": "Nested group created successfully",
    "data": {
        "total_requests": 3
    }
}
```

---

## 4. Decorator Pattern - Feature Enhancement

### Available Features

```php
// Get all available features with pricing
GET /api/requests/available-features

// Response
{
    "success": true,
    "data": {
        "features": {
            "urgent": {
                "name": "Urgent Priority",
                "description": "2-hour response time",
                "cost": 500
            },
            "warranty": {
                "name": "Extended Warranty",
                "description": "Parts and labor coverage",
                "cost_per_month": 150,
                "params": {"months": 12}
            },
            "premium": {
                "name": "Premium Service",
                "description": "Priority scheduling, dedicated technician",
                "cost": 1500
            },
            "photos": {
                "name": "Photo Documentation",
                "cost": 0
            },
            "inspection": {
                "name": "Detailed Inspection Report",
                "cost": 300
            },
            "support": {
                "name": "Extended Support",
                "description": "24-hour response",
                "cost_per_day": 25,
                "params": {"days": 30}
            }
        }
    }
}
```

### Get Cost Estimate

```php
// Calculate cost with features
POST /api/requests/cost-estimate
{
    "request_id": "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
    "features": {
        "urgent": {},
        "warranty": {"months": 12},
        "premium": {}
    }
}

// Response
{
    "success": true,
    "data": {
        "base_cost": 2000,
        "additional_cost": 3800,  // 500 + 1800 + 1500
        "total_cost": 5800,
        "features_applied": ["urgent", "warranty", "premium"]
    }
}
```

### Enhance Request

```php
// Apply features to request
POST /api/requests/enhance
{
    "request_id": "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
    "features": {
        "urgent": {},
        "warranty": {"months": 12},
        "premium": {},
        "photos": {"photos": ["url1", "url2"]},
        "inspection": {},
        "support": {"days": 30}
    }
}

// Response
{
    "success": true,
    "message": "Request enhanced successfully",
    "data": {
        "description": "Air Conditioning Repair (🚨 Urgent) (🛡️ 12-month warranty) (⭐ Premium Service) (📷 2 photos attached) (📋 Detailed inspection report) (💬 30-day support)",
        "cost": 6350,  // Base 2000 + all enhancements
        "data": {
            "warranty": {
                "months": 12,
                "fee": 1800,
                "expires_at": "2025-12-15"
            },
            "support": {
                "days": 30,
                "fee": 750,
                "response_time": "24 hours"
            }
        },
        "processing_result": {
            "features": [
                "urgent_priority",
                "extended_warranty",
                "premium_service",
                "photo_documentation",
                "detailed_inspection",
                "extended_support"
            ],
            "priority_level": "premium",
            "estimated_response": "2 hours",
            "quality_guarantee": true
        }
    }
}
```

### Single Feature Enhancement

```php
// Add just urgent priority
POST /api/requests/enhance
{
    "request_id": "018e4ad9-8d98-7c89-b8a7-bdc0836b0d77",
    "features": {
        "urgent": {}
    }
}

// Response: Base cost + ₱500
```

### Warranty Options

```php
// 6-month warranty
{
    "features": {
        "warranty": {"months": 6}  // ₱900 (6 * ₱150)
    }
}

// 24-month warranty
{
    "features": {
        "warranty": {"months": 24}  // ₱3600 (24 * ₱150)
    }
}
```

### Support Duration

```php
// 15-day support
{
    "features": {
        "support": {"days": 15}  // ₱375 (15 * ₱25)
    }
}

// 60-day support
{
    "features": {
        "support": {"days": 60}  // ₱1500 (60 * ₱25)
    }
}
```

---

## Common Workflows

### Workflow 1: Safe Status Update

```php
// 1. Create snapshot before update
POST /api/snapshots
{
    "request_id": "...",
    "label": "Before status change"
}

// 2. Update status via command
POST /api/commands/execute
{
    "type": "update_status",
    "request_id": "...",
    "status": "completed"
}

// 3. If something goes wrong, undo
POST /api/commands/undo

// OR restore from snapshot
POST /api/snapshots/restore
{
    "request_id": "...",
    "index": 0
}
```

### Workflow 2: Batch Urgent Updates

```php
// 1. Get cost estimate for urgent feature on multiple requests
POST /api/requests/cost-estimate (for each request)

// 2. Create group
POST /api/request-groups
{
    "request_ids": ["...", "..."],
    "group_name": "Urgent Batch"
}

// 3. Update all to urgent status
PATCH /api/request-groups/status
{
    "request_ids": ["...", "..."],
    "status": "urgent"
}

// 4. Apply urgent decorator to each
POST /api/requests/enhance (for each)
{
    "request_id": "...",
    "features": {"urgent": {}}
}
```

### Workflow 3: Premium Service Package

```php
// Apply full premium package
POST /api/requests/enhance
{
    "request_id": "...",
    "features": {
        "premium": {},         // ₱1500 - Priority service
        "warranty": {"months": 12},  // ₱1800 - 1-year warranty
        "inspection": {},      // ₱300 - Detailed report
        "support": {"days": 30}  // ₱750 - Month of support
    }
}

// Total additional: ₱4350
```

---

## Error Handling

### Command Errors

```json
{
    "success": false,
    "message": "Nothing to undo"
}
```

```json
{
    "success": false,
    "message": "Invalid command type"
}
```

### Memento Errors

```json
{
    "success": false,
    "message": "Request not found"
}
```

```json
{
    "success": false,
    "message": "Snapshot not found"
}
```

### Composite Errors

```json
{
    "success": false,
    "message": "No requests provided"
}
```

### Decorator Errors

```json
{
    "success": false,
    "message": "No features specified"
}
```

---

## Best Practices

### Command Pattern
- ✅ Use commands for all state-changing operations
- ✅ Check `can_undo` before attempting undo
- ✅ Keep command history for audit trails
- ❌ Don't rely on undo for critical operations (use transactions)

### Memento Pattern
- ✅ Create snapshots before major changes
- ✅ Label snapshots descriptively
- ✅ Clean up old snapshots periodically
- ❌ Don't create snapshots on every small change (performance)

### Composite Pattern
- ✅ Group related requests for batch operations
- ✅ Use nested groups for hierarchical structures
- ✅ Verify all requests exist before operations
- ❌ Don't create overly deep nesting (performance)

### Decorator Pattern
- ✅ Check cost estimate before applying features
- ✅ Stack features in logical order
- ✅ Use premium package for best value
- ❌ Don't apply conflicting features

---

## Testing

Run the test suite:
```bash
php public/test-sprint3.php
```

This will test all 4 patterns with realistic scenarios.

---

## Support

For issues or questions about Sprint 3 patterns:
- Check `SPRINT3_COMPLETE.md` for detailed documentation
- Review code examples in pattern classes
- Run test script for working examples

---

**Last Updated**: December 2024  
**Version**: 1.3.0
