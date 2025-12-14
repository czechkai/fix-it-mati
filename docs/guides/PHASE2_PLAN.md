# 📋 Phase 2: Complete Implementation Plan

## Current Status ✅

### Completed (Phase 1):
- ✅ Core system (Router, Request, Response)
- ✅ Database with Singleton pattern
- ✅ User authentication system
- ✅ JWT token generation
- ✅ API endpoints for auth
- ✅ **2 Design Patterns**: Singleton, Chain of Responsibility
- ✅ Clean project structure

### Project Structure:
```
fix-it-mati/
├── Core/                    # Router, Request, Response, Database
├── Models/                  # User (more to add)
├── Controllers/             # AuthController (more to add)
├── Services/                # AuthService (more to add)
├── Middleware/              # AuthMiddleware, RoleMiddleware
├── DesignPatterns/          # All 17 patterns organized
│   ├── Structural/
│   │   ├── Adapter/
│   │   ├── Bridge/
│   │   ├── Composite/
│   │   ├── Decorator/
│   │   ├── Facade/
│   │   ├── Flyweight/
│   │   └── Proxy/
│   └── Behavioral/
│       ├── ChainOfResponsibility/  # ✅ Done (in Middleware)
│       ├── Command/
│       ├── Iterator/
│       ├── Mediator/
│       ├── Memento/
│       ├── Observer/
│       ├── State/
│       ├── Strategy/
│       ├── TemplateMethod/
│       └── Visitor/
├── public/                  # Frontend & API entry
├── database/                # Schema & migrations
└── [config, assets, etc.]
```

---

## 🎯 Phase 2 Goals

Implement **11 more design patterns** while building core features:
1. Service Request Management System
2. Notification System
3. Payment Processing
4. Admin Dashboard Features

**Timeline**: 2-3 weeks (can be done in sprints)

---

## 📅 Detailed Implementation Plan

### **Sprint 1: Service Request System** (3-4 days)

#### Task 1.1: Create ServiceRequest Model
**File**: `Models/ServiceRequest.php`
- Full CRUD operations
- Status tracking
- Assignment to technicians
- History/timeline

#### Task 1.2: Implement State Pattern ⭐
**Location**: `DesignPatterns/Behavioral/State/`
**Files**:
- `RequestState.php` (interface)
- `PendingState.php`
- `ReviewedState.php`
- `AssignedState.php`
- `InProgressState.php`
- `CompletedState.php`
- `CancelledState.php`

**Purpose**: Manage service request lifecycle
**Example**:
```php
$request->setState(new PendingState());
$request->review(); // Transitions to ReviewedState
$request->assign($technician); // Transitions to AssignedState
```

#### Task 1.3: Implement Facade Pattern ⭐
**Location**: `DesignPatterns/Structural/Facade/`
**File**: `ServiceRequestFacade.php`

**Purpose**: Simplify complex operations
**Example**:
```php
// Instead of:
$request = new ServiceRequest();
$request->create($data);
$notification->send();
$audit->log();
$email->send();

// Use facade:
$facade = new ServiceRequestFacade();
$facade->createRequest($data); // Handles everything
```

#### Task 1.4: Create RequestController
**File**: `Controllers/RequestController.php`
- `GET /api/requests` - List requests
- `POST /api/requests` - Create request
- `GET /api/requests/{id}` - Get single request
- `PATCH /api/requests/{id}` - Update request
- `PATCH /api/requests/{id}/status` - Change status
- `DELETE /api/requests/{id}` - Cancel request

#### Task 1.5: Create RequestService
**File**: `Services/RequestService.php`
- Business logic for requests
- Validation
- Authorization checks
- Uses Facade internally

---

### **Sprint 2: Notification System** (2-3 days)

#### Task 2.1: Implement Observer Pattern ⭐
**Location**: `DesignPatterns/Behavioral/Observer/`
**Files**:
- `Subject.php` (interface)
- `Observer.php` (interface)
- `RequestSubject.php` (concrete subject)
- `EmailObserver.php`
- `SMSObserver.php`
- `InAppObserver.php`
- `AuditLogObserver.php`

**Purpose**: Notify multiple parties when request status changes
**Example**:
```php
$request = new RequestSubject();
$request->attach(new EmailObserver());
$request->attach(new SMSObserver());
$request->attach(new InAppObserver());

$request->setStatus('in_progress'); // All observers notified automatically
```

#### Task 2.2: Implement Strategy Pattern ⭐
**Location**: `DesignPatterns/Behavioral/Strategy/`
**Files**:
- `NotificationStrategy.php` (interface)
- `EmailNotificationStrategy.php`
- `SMSNotificationStrategy.php`
- `InAppNotificationStrategy.php`
- `PushNotificationStrategy.php`

**Purpose**: Different notification methods
**Example**:
```php
$notifier = new Notifier();
$notifier->setStrategy(new EmailNotificationStrategy());
$notifier->send($message);

$notifier->setStrategy(new SMSNotificationStrategy());
$notifier->send($message);
```

#### Task 2.3: Implement Bridge Pattern ⭐
**Location**: `DesignPatterns/Structural/Bridge/`
**Files**:
- `Notification.php` (abstraction)
- `NotificationImpl.php` (implementor interface)
- `EmailImplementation.php`
- `SMSImplementation.php`

**Purpose**: Separate notification abstraction from implementation
**Example**:
```php
$email = new EmailImplementation();
$notification = new UrgentNotification($email);
$notification->send();
```

#### Task 2.4: Create NotificationService
**File**: `Services/NotificationService.php`
- Uses Observer pattern
- Uses Strategy pattern
- Uses Bridge pattern
- Manages notification queue

#### Task 2.5: Create Notification Model
**File**: `Models/Notification.php`
- Store notifications in database
- Track read/unread status
- Support in-app notifications

---

### **Sprint 3: Advanced Patterns** (3-4 days)

#### Task 3.1: Implement Command Pattern ⭐
**Location**: `DesignPatterns/Behavioral/Command/`
**Files**:
- `Command.php` (interface)
- `CreateRequestCommand.php`
- `UpdateRequestCommand.php`
- `AssignTechnicianCommand.php`
- `CancelRequestCommand.php`
- `CommandInvoker.php`

**Purpose**: Encapsulate actions, enable undo/redo
**Example**:
```php
$command = new CreateRequestCommand($data);
$invoker->execute($command);
$invoker->undo(); // Undo last command
```

#### Task 3.2: Implement Memento Pattern ⭐
**Location**: `DesignPatterns/Behavioral/Memento/`
**Files**:
- `Memento.php`
- `RequestOriginator.php`
- `RequestHistory.php` (caretaker)

**Purpose**: Save and restore request states (audit trail)
**Example**:
```php
$history = new RequestHistory();
$history->save($request->createMemento());
// ... changes ...
$request->restore($history->getMemento(0)); // Restore previous state
```

#### Task 3.3: Implement Composite Pattern ⭐
**Location**: `DesignPatterns/Structural/Composite/`
**Files**:
- `ServiceCategory.php` (component)
- `CategoryLeaf.php`
- `CategoryComposite.php`

**Purpose**: Service category hierarchy
**Example**:
```
Water Services
├── Leak Repair
│   ├── Pipe Leak
│   └── Faucet Leak
└── Installation
    ├── New Connection
    └── Meter Installation
```

#### Task 3.4: Implement Decorator Pattern ⭐
**Location**: `DesignPatterns/Structural/Decorator/`
**Files**:
- `RequestDecorator.php` (base)
- `UrgentRequestDecorator.php`
- `PriorityRequestDecorator.php`
- `RecurringRequestDecorator.php`

**Purpose**: Add features dynamically
**Example**:
```php
$request = new BasicRequest();
$request = new UrgentRequestDecorator($request);
$request = new PriorityRequestDecorator($request);
$request->getDescription(); // "Urgent, High Priority Request"
```

---

### **Sprint 4: Payment & External Integration** (3-4 days)

#### Task 4.1: Implement Adapter Pattern ⭐
**Location**: `DesignPatterns/Structural/Adapter/`
**Files**:
- `PaymentGatewayInterface.php`
- `PayMongoAdapter.php`
- `DragonPayAdapter.php`
- `PayPalAdapter.php`

**Purpose**: Adapt different payment gateways to common interface
**Example**:
```php
$gateway = new PayMongoAdapter();
$gateway->processPayment($amount); // Works same for all gateways
```

#### Task 4.2: Create Payment Model & Controller
**Files**:
- `Models/Payment.php`
- `Controllers/PaymentController.php`
- `Services/PaymentService.php`

#### Task 4.3: Implement Template Method Pattern ⭐
**Location**: `DesignPatterns/Behavioral/TemplateMethod/`
**Files**:
- `PaymentProcessor.php` (abstract)
- `OnlinePaymentProcessor.php`
- `CashPaymentProcessor.php`

**Purpose**: Define payment processing workflow
**Example**:
```php
abstract class PaymentProcessor {
    public function processPayment() {
        $this->validatePayment();
        $this->calculateFees();
        $this->executePayment();
        $this->generateReceipt();
        $this->sendNotification();
    }
    abstract protected function executePayment();
}
```

---

### **Sprint 5: Caching & Performance** (2-3 days)

#### Task 5.1: Implement Proxy Pattern ⭐
**Location**: `DesignPatterns/Structural/Proxy/`
**Files**:
- `CacheProxy.php` (caching proxy)
- `AccessControlProxy.php` (already exists in middleware)
- `LazyLoadingProxy.php`

**Purpose**: Cache expensive operations
**Example**:
```php
$proxy = new CacheProxy(new AnnouncementService());
$announcements = $proxy->getAll(); // First call: from DB
$announcements = $proxy->getAll(); // Second call: from cache
```

#### Task 5.2: Implement Flyweight Pattern ⭐
**Location**: `DesignPatterns/Structural/Flyweight/`
**Files**:
- `ServiceTypeFlyweight.php`
- `ServiceTypeFactory.php`

**Purpose**: Share common data (icons, colors, categories)
**Example**:
```php
$factory = new ServiceTypeFactory();
$waterService = $factory->getServiceType('water');
$waterService2 = $factory->getServiceType('water');
// Both reference same object - saves memory
```

---

### **Sprint 6: Advanced Features** (2-3 days)

#### Task 6.1: Implement Iterator Pattern ⭐
**Location**: `DesignPatterns/Behavioral/Iterator/`
**Files**:
- `RequestIterator.php`
- `FilteredRequestIterator.php`
- `PaginatedRequestIterator.php`

**Purpose**: Traverse requests with different filters
**Example**:
```php
$iterator = new FilteredRequestIterator($requests, 'pending');
foreach ($iterator as $request) {
    // Only pending requests
}
```

#### Task 6.2: Implement Mediator Pattern ⭐
**Location**: `DesignPatterns/Behavioral/Mediator/`
**Files**:
- `Mediator.php` (interface)
- `RequestMediator.php`

**Purpose**: Coordinate communication between Customer, Admin, Technician
**Example**:
```php
$mediator = new RequestMediator();
$customer->setMediator($mediator);
$technician->setMediator($mediator);

$customer->createRequest($data);
// Mediator coordinates: creates request, notifies admin, assigns technician
```

#### Task 6.3: Implement Visitor Pattern ⭐
**Location**: `DesignPatterns/Behavioral/Visitor/`
**Files**:
- `Visitor.php` (interface)
- `ReportGeneratorVisitor.php`
- `ExportVisitor.php`
- `StatisticsVisitor.php`

**Purpose**: Generate different reports/exports from same data
**Example**:
```php
$requests = [...];
$pdfVisitor = new PDFReportVisitor();
$csvVisitor = new CSVExportVisitor();

foreach ($requests as $request) {
    $request->accept($pdfVisitor);
}
$pdfVisitor->generate(); // PDF report
```

---

## 📊 Progress Tracking

### Design Patterns Status:

| Pattern | Status | Sprint | Files | Use Case |
|---------|--------|--------|-------|----------|
| Singleton | ✅ Done | 1 | Database, AuthService | Single instance |
| Chain of Responsibility | ✅ Done | 1 | Middleware | Auth chain |
| State | 🔜 Sprint 1 | 1 | State/*.php | Request lifecycle |
| Facade | 🔜 Sprint 1 | 1 | Facade/*.php | Simplify operations |
| Observer | 🔜 Sprint 2 | 2 | Observer/*.php | Notifications |
| Strategy | 🔜 Sprint 2 | 2 | Strategy/*.php | Notification methods |
| Bridge | 🔜 Sprint 2 | 2 | Bridge/*.php | Notification impl |
| Command | 🔜 Sprint 3 | 3 | Command/*.php | Action objects |
| Memento | 🔜 Sprint 3 | 3 | Memento/*.php | State history |
| Composite | 🔜 Sprint 3 | 3 | Composite/*.php | Category tree |
| Decorator | 🔜 Sprint 3 | 3 | Decorator/*.php | Dynamic features |
| Adapter | 🔜 Sprint 4 | 4 | Adapter/*.php | Payment gateways |
| Template Method | 🔜 Sprint 4 | 4 | TemplateMethod/*.php | Payment workflow |
| Proxy | 🔜 Sprint 5 | 5 | Proxy/*.php | Caching, lazy loading |
| Flyweight | 🔜 Sprint 5 | 5 | Flyweight/*.php | Shared data |
| Iterator | 🔜 Sprint 6 | 6 | Iterator/*.php | Data traversal |
| Mediator | 🔜 Sprint 6 | 6 | Mediator/*.php | Component coordination |
| Visitor | 🔜 Sprint 6 | 6 | Visitor/*.php | Reports/exports |

---

## 🎓 Demonstration Strategy

For each pattern, document:
1. **What it is** - Brief explanation
2. **Why we use it** - Problem it solves
3. **Where it's used** - File location
4. **How it works** - Code example
5. **Benefits** - Advantages gained

**Example for Professor:**
```
Pattern: Observer
Problem: When a request status changes, we need to notify customer, admin, technician, and log the change.
Without Pattern: Coupling notifications directly to request logic
With Pattern: Request doesn't know about notifications, observers subscribe independently
Code: DesignPatterns/Behavioral/Observer/
Demo: Change request status, show all observers being notified automatically
```

---

## ⚡ Quick Start for Next Sprint

**To begin Sprint 1 immediately:**

1. Run database migration (if not done)
2. Create `Models/ServiceRequest.php`
3. Implement State pattern classes
4. Create RequestController
5. Test endpoints

**Command to start:**
```powershell
# We'll create these files together in Sprint 1
```

---

## 📝 Notes

- Each sprint builds on previous ones
- Patterns are integrated into actual features (not just examples)
- Can demonstrate each pattern independently
- All code is production-ready
- Full test coverage possible

---

**Ready to start Sprint 1?** Let me know and we'll implement:
1. ServiceRequest model
2. State pattern for request lifecycle
3. Facade pattern for operations
4. Request API endpoints

This will add **2 more patterns** and a complete feature!
