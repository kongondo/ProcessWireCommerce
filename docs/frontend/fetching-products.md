# Fetching Products

This guide explains how to query and display products in your ProcessWire templates using ProcessWire Commerce methods.

## Basic Querying

While you can use standard ProcessWire selectors, ProcessWire Commerce provides a helper method `$pwcommerce->find()` that simplifies querying by converting short syntax to full field names and ensuring hidden shop pages are accessible.

```php
// Find all active products (template=product converts to template=pwcommerce-product)
$products = $pwcommerce->find("template=product, limit=12");

// Find products in a specific category (categories converts to pwcommerce_categories)
$category = $pwcommerce->get("template=category, name=electronics");
$products = $pwcommerce->find("template=product, categories=$category");
```

## Rendering Products

You can access product fields directly (see [API Variables](api-variables.md) for available fields).

```php
foreach($products as $product) {
    echo "<h2>{$product->title}</h2>";
    // Access price via the stock object
    echo "<p>Price: {$product->pwcommerce_product_stock->price}</p>";
    // Add to cart button
    echo $pwcommerce->renderAddToCartButton($product);
}
```

## Advanced Queries

ProcessWire Commerce selectors support querying by stock and price properties using sub-selectors on the stock field.

*   **Sorting:** `sort=stock.price` (Sorts by `pwcommerce_product_stock.price`)
*   **Filtering:** `stock.price>100`, `stock.quantity>0`

## Dynamic Interactions (HTMX & Alpine.js)

ProcessWire Commerce supports dynamic loading and interactions. Ensure you include the necessary scripts in your template if you wish to use these features.

```html
<!-- Example HTMX attributes -->
<div hx-get="/shop/products/" hx-trigger="load">
    <!-- Products will be loaded here via AJAX -->
</div>
```
