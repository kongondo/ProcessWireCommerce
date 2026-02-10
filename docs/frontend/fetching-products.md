# Fetching Products

This guide explains how to query and display products in your ProcessWire templates using the ProcessWire API and ProcessWire Commerce methods.

## Basic Querying

Use the standard `pages->find()` method with specific selectors:

```php
// Find all active products
$products = $pages->find("template=product, limit=12");

// Find products in a specific category
$category = $pages->get("/categories/electronics/");
$products = $pages->find("template=product, categories=$category");
```

## Rendering Products

You can access product fields directly or use the `render()` method for standardized output:

```php
foreach($products as $product) {
    echo "<h2>{$product->title}</h2>";
    echo "<p>Price: {$product->price}</p>";
    // Add to cart button
    echo $modules->get('PwCommerce')->renderAddToCartButton($product);
}
```

## Advanced Queries

*   **Sorting:** `sort=price` or `sort=-created`
*   **Filtering:** `price>100`, `stock>0`

## Dynamic Interactions (HTMX & Alpine.js)

ProcessWire Commerce supports dynamic loading and interactions. Ensure you include the necessary scripts in your template if you wish to use these features.

```html
<!-- Example HTMX attributes -->
<div hx-get="/shop/products/" hx-trigger="load">
    <!-- Products will be loaded here via AJAX -->
</div>
```
