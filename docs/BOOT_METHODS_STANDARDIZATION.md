# Standardized Boot Methods and Event Handling

## Overview

This document outlines the standardized approach for boot methods and event handling across all models in the Hospital Management System. The standardization ensures consistency, maintainability, and follows Laravel best practices.

## Core Components

### 1. StandardBootTrait

The `StandardBootTrait` provides a unified boot event handling system with the following features:

- **Automatic Event Registration**: Registers all standard Laravel model events
- **Enterprise Integration**: Automatic enterprise_id assignment
- **UUID Generation**: Automatic UUID generation for models that need it
- **Email Cleaning**: Automatic email field normalization
- **Hook Methods**: Extensible hook methods for custom model logic

### 2. Standard Boot Method Pattern

All models should follow this pattern:

```php
<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model
{
    use HasFactory;
    use EnterpriseScopeTrait;
    use StandardBootTrait;

    /**
     * Boot the model with standardized event handling.
     */
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Custom logic before creating a model.
     */
    protected function processCustomBeforeCreate(): void
    {
        // Custom creation logic here
    }

    /**
     * Custom logic before updating a model.
     */
    protected function processCustomBeforeUpdate(): void
    {
        // Custom update logic here
    }

    /**
     * Custom logic before saving (create or update).
     */
    protected function processCustomBeforeSave(): void
    {
        // Custom save logic here
    }

    /**
     * Custom logic after creating a model.
     */
    protected function processCustomAfterCreate(): void
    {
        // Custom post-creation logic here
    }

    /**
     * Custom logic after updating a model.
     */
    protected function processCustomAfterUpdate(): void
    {
        // Custom post-update logic here
    }

    /**
     * Custom logic after saving (create or update).
     */
    protected function processCustomAfterSave(): void
    {
        // Custom post-save logic here
    }

    /**
     * Custom logic before deleting a model.
     */
    protected function processCustomBeforeDelete(): void
    {
        // Custom deletion logic here
    }

    /**
     * Custom logic after deleting a model.
     */
    protected function processCustomAfterDelete(): void
    {
        // Custom post-deletion logic here
    }
}
```

## Event Handling Guidelines

### 1. Event Types and Usage

| Event | Usage | Example |
|-------|-------|---------|
| `creating` | Before model is created | Generate IDs, validate data |
| `created` | After model is created | Send notifications, create relationships |
| `updating` | Before model is updated | Validate changes, clean data |
| `updated` | After model is updated | Update related models, log changes |
| `saving` | Before create or update | Common validation logic |
| `saved` | After create or update | Common post-save actions |
| `deleting` | Before model is deleted | Delete relationships, check constraints |
| `deleted` | After model is deleted | Clean up references, log deletion |

### 2. Best Practices

1. **Use Hook Methods**: Always use the hook methods provided by `StandardBootTrait` instead of directly registering events
2. **Keep Logic Simple**: Boot methods should be lightweight and delegate complex logic to service classes
3. **Handle Exceptions**: Wrap complex logic in try-catch blocks
4. **Document Custom Logic**: Always document what custom logic does
5. **Test Event Handlers**: Ensure all event handlers are properly tested

### 3. Common Patterns

#### Auto-generating Fields
```php
protected function processCustomBeforeCreate(): void
{
    if (!$this->reference_number) {
        $this->reference_number = $this->generateReferenceNumber();
    }
}

private function generateReferenceNumber(): string
{
    return 'REF-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
```

#### Cascading Deletes
```php
protected function processCustomBeforeDelete(): void
{
    // Delete related models
    $this->relatedItems()->delete();
    
    // Or throw exception if deletion not allowed
    if ($this->relatedItems()->count() > 0) {
        throw new \Exception('Cannot delete model with related items');
    }
}
```

#### Relationship Management
```php
protected function processCustomAfterCreate(): void
{
    if ($this->parent_id) {
        $this->updateParentStatus();
    }
}

private function updateParentStatus(): void
{
    $parent = $this->parent;
    if ($parent) {
        $parent->updateStatus();
    }
}
```

## Implementation Status

### ✅ Completed Models

- [x] **Consultation** - Standardized with data preparation and cascade deletion
- [x] **Enterprise** - Standardized with administrator assignment
- [x] **Patient** - Standardized with patient number generation
- [x] **StandardBootTrait** - Core trait implementation

### 🔄 In Progress

- [ ] **User** - Complex boot logic being refactored
- [ ] **PaymentRecord** - Boot method standardization pending
- [ ] **Company** - Boot method standardization pending

### ⏳ Pending Models

- [ ] **Project**
- [ ] **StockItem**
- [] **ReportModel**
- [ ] **Service**
- [ ] **CardRecord**
- [ ] **DoseItem**
- [ ] **Event**
- [ ] **StockOutRecord**
- [ ] **Image**
- [ ] **StockItemCategory**

## Benefits of Standardization

1. **Consistency**: All models follow the same event handling pattern
2. **Maintainability**: Easy to understand and modify model behavior
3. **Debugging**: Standardized structure makes debugging easier
4. **Testing**: Consistent patterns make testing more straightforward
5. **Documentation**: Clear separation of concerns and well-documented hooks
6. **Enterprise Integration**: Automatic handling of enterprise scoping
7. **Data Integrity**: Automatic validation and data cleaning

## Migration Guide

To migrate existing models to the standardized boot pattern:

1. Add `use StandardBootTrait` to the model
2. Replace existing `boot()` method with standardized version
3. Move custom logic to appropriate hook methods
4. Extract complex logic to private methods
5. Update tests to reflect new structure
6. Document any custom behavior

## Next Steps

1. Complete standardization of remaining models
2. Add comprehensive tests for all boot methods
3. Create model-specific documentation for complex boot logic
4. Implement performance monitoring for event handlers
5. Add logging for critical model events
