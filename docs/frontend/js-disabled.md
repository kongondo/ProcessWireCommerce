# Non-JS Implementation

ProcessWire Commerce is designed to work seamlessly without JavaScript. This guide explains how to ensure your store remains functional for users with JavaScript disabled.

## Server-Side Rendering

All interactions in ProcessWire Commerce are processed server-side. Forms submit to the server, pages reload, and the state is updated.

## Forms and Buttons

Ensure your forms have a valid `action` attribute and `method="post"`.

```html
<form action="./add-to-cart/" method="post">
    <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
    <input type="number" name="qty" value="1">
    <button type="submit">Add to Cart</button>
</form>
```

## Navigation

Links should always point to full URLs, not just JavaScript handlers.

```html
<a href="/cart/">View Cart</a>
```

## Considerations

*   **Ajax Features:** Features relying purely on Ajax/HTMX will degrade gracefully to full page loads.
*   **Validation:** Ensure server-side validation is robust, as client-side validation won't run without JS.
