# Developer API

Welcome to the ProcessWire Commerce Developer API documentation.

## Overview

This section is for developers who want to extend or customize the functionality of ProcessWire Commerce.

## API Reference

The full API reference, generated from the source code, can be found [here](../api/index.html).

## Extending Classes

You can extend core classes to modify behavior.

```php
class MyProduct extends ProcessPWCommerce\Product {
    // Override methods
}
```

## Creating Custom Actions

Create custom actions for products, orders, etc., by implementing the `ProcessPWCommerce\Action` interface.

```php
class MyAction extends ProcessPWCommerce\Action {
    public function execute($item) {
        // ...
    }
}
```
