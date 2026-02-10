# PWCommerce Pending Issues Analysis

This document outlines the pending issues identified in the repository, categorized by severity and type.

## Critical Bugs

### 1. Variant Selection Race Condition (Frontend)
*   **File:** `FieldtypePWCommerceOrder/InputfieldPWCommerceOrder.js`
*   **Issue:** There is a known intermittent bug when selecting products with variants. The issue appears to be a race condition between HTMX swapping content and Alpine.js initializing or interacting with the new DOM elements.
*   **Symptoms:** Checkboxes for variants might not work correctly or state might not be updated as expected after a search or filter action.
*   **Relevant Code:**
    ```javascript
    // @TODO: TRYING TO SORT OUT BUG BELOW
    if (!isReadyForProductsCheckboxes) {
        return
    }
    ```
*   **Advice:**
    *   Investigate the timing of `htmx:afterSwap` and `htmx:afterSettle` events.
    *   Ensure that Alpine.js components are properly re-initialized or updated after HTMX injects new HTML.
    *   Consider using `Alpine.nextTick()` or similar mechanisms to ensure the DOM is ready before manipulating state based on DOM elements.

### 2. Missing Discount Calculation for Live Order Items (Backend)
*   **File:** `traits/utilities/TraitPWCommerceUtilitiesOrderLineItem.php`
*   **Issue:** Discount calculation logic is missing for "live calculate only" order line items.
*   **Symptoms:** Order totals might be incorrect during the checkout process or when dynamically calculating totals before saving an order.
*   **Relevant Code:**
    ```php
    if ($this->order->isLiveCalculateOnly) {
        // TODO WE WILL NEED TO CALCULATE DISCOUNTS ETC!!!! - BUGGY FOR NOW!
        $orderLineItems = $this->order->liveOrderLineItems;
    }
    ```
*   **Advice:**
    *   Implement the discount calculation logic for `liveOrderLineItems` similar to how it's done for saved order line items.
    *   Ensure that `TraitPWCommerceUtilitiesDiscount::getOrderLineItemDiscountsAmount` or equivalent is called for these transient items.

### 3. Pagination Synchronization in AJAX Context (Backend)
*   **File:** `traits/admin/TraitPWCommerceAdminExecute.php`
*   **Issue:** When filtering lists via AJAX (e.g., in custom listers), the pagination state can get out of sync with the current filter selector.
*   **Symptoms:** Users might see incorrect pages or empty results when changing filters while on a paginated page (e.g., page 2 of results).
*   **Relevant Code:**
    ```php
    // TODO THIS IS STILL BUGGY! IT TRIPS WHEN THIS CHANGES BUT CURRENT PAGE IS NOT IN SYNC...
    $this->setPaginationLimitCookieForContext();
    ```
*   **Advice:**
    *   Ensure that whenever a filter selector changes, the current pagination number is reset to 1 *before* the query is executed.
    *   Verify that the cookie storing the pagination state is updated correctly and synchronously with the AJAX request.

## Code Quality & Technical Debt

### 1. Hardcoded Values
*   **File:** `FieldtypePWCommerceOrder/InputfieldPWCommerceOrder.js`
*   **Issue:** Hardcoded shipping fees found in the code.
*   **Relevant Code:**
    ```javascript
    // @todo: client_order_shipping_amount_plus_handling_fee_amount
    const orderShippingFee = 3
    ```
*   **Advice:** Replace hardcoded values with dynamic values fetched from the backend configuration or the order object.

### 2. Excessive "TODO" and Dead Code
*   **Observation:** There are numerous `TODO` comments throughout the codebase, many of which suggest deleting code ("DELETE IF NOT IN USE", "NO LONGER IN USE").
*   **Files:** `PwCommerceHooks/PwCommerceHooks.module`, `traits/actions/TraitPWCommerceActionsGiftCard.php`, and others.
*   **Advice:**
    *   Conduct a cleanup sprint to remove commented-out code and obsolete TODOs.
    *   Verify if the "temporary" code is still needed or can be refactored.

### 3. Debugging Code Left In
*   **Observation:** Several files contain `// DEBUG` or `// ~~~~~~~~~~~~~~~~~~~~ DEBUG ~~~~~~~~~~~~~~~~~~` sections.
*   **Advice:** Remove all debug statements and logging from production code. Use a proper logging service or ProcessWire's logging facility instead of inline debug comments/code.

## Missing Features / Enhancements

### 1. Hook Implementations
*   **File:** `PwCommerceHooks/PwCommerceHooks.module`
*   **Issue:** Several hooks are planned but not fully implemented or tested.
*   **Examples:**
    *   `// TODO: IF NORMAL EDIT, SHOW WARNING ABOUT LIMITED FUNCTIONALITY IF EDITING A PWCOMMERCE PAGE!`
    *   `// TODO ADD HOOK(S) TO AMEND PRODUCT PAGE VISIBLE FIELDS WITH RESPECT TO VARIANTS.`
*   **Advice:** Prioritize and implement these hooks to ensure full functionality and better user experience.

### 2. GUI Improvements
*   **File:** `traits/actions/TraitPWCommerceActionsNewItem.php`
*   **Issue:** "TODO WORK ON HOW TO ADD THIS! GUI, ETC!"
*   **Advice:** Design and implement the missing GUI components for adding new items where programmatic addition is currently the only option.
