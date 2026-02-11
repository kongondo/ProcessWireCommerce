# Frontend API Variables

ProcessWire Commerce provides several API variables to help you interact with the store data in your templates.

## Main Variables

*   **`$pwcommerce`:** The main entry point to ProcessWire Commerce functionality. This variable is automatically available in your templates.
    *   **`$pwcommerce->getCart()`**: Returns the current shopping cart object.
    *   **`$pwcommerce->getCustomer()`**: Returns the current customer object.
    *   **`$pwcommerce->import($items, $importType, $options)`**: Import items (e.g. products) into the store.
    *   **`$pwcommerce->renderAddToCartButton($product)`**: Renders an "Add to Cart" button for a given product.
    *   **`$pwcommerce->find($selector)`**: Find commerce-related pages (products, categories, etc.) using short syntax selectors.

## Product Object

When working with products (which are ProcessWire pages with the `pwcommerce-product` template), you have access to standard ProcessWire fields plus Commerce-specific properties stored in custom fields:

*   **`$product->title`**: The product name (standard ProcessWire field).
*   **`$product->pwcommerce_product_stock`**: An object containing inventory and pricing data.
    *   **`price`**: The current selling price.
    *   **`comparePrice`**: The comparison price (e.g., MSRP).
    *   **`sku`**: The product's Stock Keeping Unit.
    *   **`quantity`**: The current stock level.
    *   **`isOnSale`**: Boolean indicating if the product is on sale.
    *   **`allowBackorders`**: Boolean indicating if backorders are allowed.
*   **`$product->pwcommerce_images`**: Field containing product images (ProcessWire Image field).
*   **`$product->pwcommerce_description`**: The product description (ProcessWire Textarea/CKEditor field).
*   **`$product->pwcommerce_brand`**: A Page Reference field linking to the brand/manufacturer.
*   **`$product->pwcommerce_categories`**: A Page Reference field linking to product categories.
*   **`$product->pwcommerce_tags`**: A Page Reference field linking to product tags.
*   **`$product->pwcommerce_product_settings`**: An object containing product settings.
    *   **`shippingType`**: The shipping type (e.g., 'physical', 'digital').
    *   **`taxable`**: Boolean indicating if the product is taxable.
    *   **`trackInventory`**: Boolean indicating if inventory tracking is enabled.

### Example Access

```php
// Get product price
$price = $product->pwcommerce_product_stock->price;

// Get product SKU
$sku = $product->pwcommerce_product_stock->sku;

// Check if on sale
if ($product->pwcommerce_product_stock->isOnSale) {
    // ...
}
```

## Cart Object

The cart object allows you to manage items in the customer's session.

*   **`$cart->items()`**: Returns an array of items in the cart.
*   **`$cart->getTotal()`**: Returns the total price of the cart.
*   **`$cart->getNumberOfItems()`**: Returns the total count of items.
*   **`$cart->add($product, $qty)`**: Add a product to the cart programmatically.

## Example Usage

```php
<?php
// Get the cart
$cart = $pwcommerce->getCart();

// Check if cart is empty
if($cart->getNumberOfItems() > 0) {
    echo "You have items in your cart!";
    echo "Total: " . $cart->getTotal();
} else {
    echo "Your cart is empty.";
}
?>
```
