# Frontend Guide

Welcome to the ProcessWire Commerce Frontend Guide. This section explains how to fetch products and display them in your ProcessWire templates.

## Overview

ProcessWire Commerce uses the `pwcommerce` API variable to interact with your store data. It leverages Alpine.js and HTMX for dynamic frontend interactions, but can also work without JavaScript.

## Getting Started

1.  **Fetching Products:** [Learn how to query products](fetching-products.md).
2.  **Using API Variables:** [Discover available methods and properties](api-variables.md).
3.  **Dynamic Interactions:** [Integrate Alpine.js and HTMX](fetching-products.md#dynamic-interactions-htmx-alpinejs).
4.  **Non-JS Support:** [Build a functional store without JavaScript](js-disabled.md).

## Example Template

```php
<?php
// Example of a basic product listing template
$products = $pages->find("template=product, limit=12");

foreach($products as $product) {
    echo $product->render(); // Or custom markup
}
?>
```
