# Frontend API Variables

ProcessWire Commerce provides several API variables to help you interact with the store data in your templates.

## Main Variables

*   **`$pwcommerce`:** The main entry point to ProcessWire Commerce functionality. This variable is automatically available in your templates.
    *   **`$pwcommerce->getCart()`**: Returns the current shopping cart object.
    *   **`$pwcommerce->getCustomer()`**: Returns the current customer object.
    *   **`$pwcommerce->import($items, $importType, $options)`**: Import items (e.g. products) into the store.
    *   **`$pwcommerce->renderAddToCartButton($product)`**: Renders an "Add to Cart" button for a given product.

## Product Object

When working with products (which are standard ProcessWire pages with the `product` template), you have access to standard ProcessWire fields plus Commerce-specific properties:

*   **`$product->title`**: The product name.
*   **`$product->pw_sku`**: The product's Stock Keeping Unit.
*   **`$product->pw_price`**: The product's price.
*   **`$product->pw_stock`**: Current stock level.
*   **`$product->pw_images`**: Field containing product images.

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
