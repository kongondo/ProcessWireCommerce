<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Cart: Trait class for PWCommerce Cart.
 *
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerce Class for PWCommerce
 * Copyright (C) 2024 by Francis Otieno
 * MIT License
 *
 */

trait TraitPWCommerceCart
{




	// protected $session_id;
	protected $tableName = "pwcommerce_cart";
	protected $desired_dbschema_ver = 1;
	protected $items; //WireArray keeping items


	/**
	 * Get Cart For Processing.
	 *
	 * @return mixed
	 */
	public function getCartForProcessing() {
		// rename this method
		# ++++++++++++++
		/** @var stdClass $cart */
		$originalCart = $this->getCart();
		// convert to WireArray
		$cart = new WireArray();
		if (!empty($originalCart)) {
			# ++++++++++++++++
			foreach ($originalCart as $originalCartItem) {
				$cartItem = new WireData();
				$propertiesArray = get_object_vars($originalCartItem);
				// e.g.
				// 'id' => 19940
				// 'product_id' => 3747
				// 'quantity' => 1
				// 'pwcommerce_title' => 'Ambiano Portable Hob New Model'
				// 'pwcommerce_thumb_url' => '/site/assets/files/3747/ambiano-portable-hob-a.0x260.jpg'
				// 'pwcommerce_price' => 20.0
				// 'pwcommerce_price_total' => 20.0
				// 'pwcommerce_is_variant' => false
				// 'pwcommerce_variant_parent_id' => 0
				// 'pwcommerce_variant_parent_title' => null
				$cartItem->setArray($propertiesArray);
				// -----
				$cart->add($cartItem);
			}
		}
		return $cart;


	}

	/**
	 * Get Cart Sub Total Money.
	 *
	 * @return mixed
	 */
	public function getCartSubTotalMoney() {
		/** @var WireArray $cart */
		$cart = $this->getCartForProcessing();
		$isPricesIncludeTaxes = $this->isPricesIncludeTaxes();
		// getTaxAmountFromPriceExclusiveTax
		// ---------
		$cartSubTotalMoney = $this->money(0);
		// +++++
		if ($cart->count()) {
			foreach ($cart as $cartItem) {
				$quantity = $cartItem->quantity;
			}
		}
		// ========
		return $cartSubTotalMoney;
	}
	/**
	 * Get Cart Sub Total.
	 *
	 * @return mixed
	 */
	public function getCartSubTotal() {
		$cartMoney = $this->getCartSubTotalMoney();
		$cartSubTotalAmount = $this->getWholeMoneyAmount($cartMoney);
		return $cartSubTotalAmount;

	}

	/**
	 * Get Cart Total Money.
	 *
	 * @return mixed
	 */
	public function getCartTotalMoney() {
	}

	/**
	 * Get Cart Total.
	 *
	 * @return mixed
	 */
	public function getCartTotal() {
	}

	/**
	 * Get Cart Tax Money.
	 *
	 * @return mixed
	 */
	public function getCartTaxMoney() {
	}

	/**
	 * Get Cart Tax.
	 *
	 * @return mixed
	 */
	public function getCartTax() {
	}

	/**
	 * Get Cart Shipping.
	 *
	 * @return mixed
	 */
	public function getCartShipping() {
	}


	/**
	 * Check if cart is empty.
	 *
	 * @return bool
	 */
	public function isCartEmpty() {
		return empty($this->getCart());
	}

	############### DELETE UNREQUIRED

	/**
	 * Get cart with final titles and prices calculated
	 *
	 * @return mixed
	 */
	public function ___getCart() {
		// ==============
		// TODO @KONGONDO -> COMMENT
		/** @var stdClass $products */
		$products = $this->getCartRaw();

		$items = [];

		// TODO @KONGONDO -> CHANGE BELOW pages->get to getRaw() + let it return an object ?

		foreach ($products as $p) {
			$product = $this->pages->get($p->product_id);
			if ($product instanceof NullPage)
				continue;

			$p->pwcommerce_title = $this->getProductTitle($product);
			// @KONGONDO TODO - NEED TO CHANGE VARIABLES HERE SINCE IN PWCOMMERCE 2 VARIANT AND MAIN PRODUCT WILL BOTH BE Pages! difference is their templates: pwcommerce-product and pwcommerce-product-variant
			// TODO DELETE WHEN DONE; NO SPECIAL VARIATION ID
			// $p->pwcommerce_variation_id = $p->variation_id;

			// TODO @KONGONDO ADD PRODUCT THUMB -> OK?
			// TODO: FOR NOW RETURN THE JUST THE URL
			// $p->pwcommerce_thumb = $this->getProductThumb($product);
			$p->pwcommerce_thumb_url = $this->getProductThumbURL($product);

			// -------------------

			// Calculate the final price (hooks might affect it)
			$p->pwcommerce_price = $this->getProductPrice($product);
			// echo $p->pwcommerce_title . " - TITLE \n";
			// echo $p->pwcommerce_price . " - PRICE \n";
			// TODO  NEW MONEY CLASS!
			// $p->pwcommerce_price_total = $p->pwcommerce_price * $p->quantity;
			$p->pwcommerce_price_total = $this->getMoneyTotalAsWholeMoneyAmount($p->pwcommerce_price, $p->quantity);


			// -----------------
			// is this product a variant?
			$p->pwcommerce_is_variant = $product->template->name === PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME;
			// if variant, track parent product ID and title
			// $p->pwcommerce_variant_parent_id = !empty($p->pwcommerce_is_variant) ? $product->parent->id : 0;
			// // TODO UNFORMATTED TITLE?
			// $p->pwcommerce_variant_parent_title = !empty($p->pwcommerce_is_variant) ? $product->parent->title : NULL;

			if (!empty($p->pwcommerce_is_variant)) {
				$p->pwcommerce_variant_parent_id = $product->parent->id;
				// TODO UNFORMATTED TITLE?
				$p->pwcommerce_variant_parent_title = $product->parent->title;
			} else {
				$p->pwcommerce_variant_parent_id = 0;
				$p->pwcommerce_variant_parent_title = NULL;
			}

			$items[$p->id] = $p;
		}
		return $items;
	}

	// @KONGONDO ADDITION
	/**
	 * Get Product Thumb.
	 *
	 * @param Page $product
	 * @return mixed
	 */
	private function getProductThumb(Page $product) {

		$thumb = null;
		// check if $product is NOT NullPage and if it has images
		if ($product->id && $product->pwcommerce_images->count()) {
			// get 'usual' ProcessWire thumb
			$thumb = $product->pwcommerce_images->first()->height(260);
		}
		return $thumb;
	}

	/**
	 * Get Product Thumb U R L.
	 *
	 * @param Page $product
	 * @return mixed
	 */
	private function getProductThumbURL(Page $product) {

		$thumb = $this->getProductThumb($product);
		$thumbURL = null;
		if (!empty($thumb)) {
			$thumbURL = $thumb->url;
		}
		return $thumbURL;
	}

	/**
	 * Get cart in raw database format
	 *
	 * @return mixed
	 */
	public function getCartRaw() {
		$tableName = PwCommerce::PWCOMMERCE_CART_TABLE_NAME;
		if (empty($this->database->tableExists($tableName))) {
			// FRESH INSTALL; SETUP NOT COMPLETE; NO CART!
			// return early
			return [];
		}

		// ===============
		$sql = "DELETE FROM $tableName WHERE session_id = :session_id AND quantity < 1";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":session_id", $this->session_id);
		$sth->execute();

		// TODO DELETE WHEN DONE - WE DON'T HAVE A SPECIAL VARIATION ID; IT IS JUST A PRODUCT
		// $sql = "SELECT id, product_id, variation_id, quantity FROM
		$sql = "SELECT id, product_id, quantity FROM $tableName WHERE session_id = :session_id ORDER BY id";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":session_id", $this->session_id);
		$sth->execute();

		return $sth->fetchAll(\PDO::FETCH_CLASS);
	}

	/**
	 * Get quantity based on product id, includes all variations too
	 *
	 * @param mixed $product_id
	 * @return mixed
	 */
	public function getProductQuantityFromCart($product_id) {
		$sql = "SELECT quantity FROM " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " WHERE session_id = :session_id AND product_id = :product_id";
		//$sql = "SELECT quantity FROM $this->dbname WHERE session_id = :session_id AND product_id = :product_id AND variation_id = :variation_id LIMIT 1";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":session_id", $this->session_id);
		$sth->bindParam(":product_id", $product_id);
		//$sth->bindParam(":variation_id", $variation_id);
		$sth->execute();

		$oldQty = 0;
		foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
			$oldQty = $oldQty + $row['quantity'];
		}
		return $oldQty;
	}


	// ~~~~~~~~~~~~~~~~~

	/**
	 *  prepare Add.
	 *
	 * @return mixed
	 */
	private function _prepareAdd() {
		// TODO - DO WE REALLY NEED THIS METHOD? WHAT DOES IT CHECK?!!!
		$id = (int) $this->input->post->product_id;
		$redirect = "";
		if ($this->input->post->pwcommerce_cart_add_product_quantity) {
			// TODO @KONGONDO -> ADDING MUTLIPLE PRODUCTS PER BUTTON/LINK CLICK
			$quantity = (int) $this->input->post->pwcommerce_cart_add_product_quantity;
		} else {
			// TODO @KONGONDO -> ADDING ONE PRODUCT PER BUTTON/LINK CLICK
			$quantity = 1;
		}

		if ($this->input->post->pwcommerce_cart_redirect) {
			$redirect = $this->input->post->pwcommerce_cart_redirect;
		} else {
			// TODO FOR NOW WE DON'T REDIRECT IF REDIRECT PAGE NOT SPECIFIED; THIS IS BECAUSE PWCOMMERCE PRODUCT PAGES ARE HIDDEN AND BELOW (1ST) WILL REDIRECT TO BACKEND!
			// $redirect = $this->pages->get($id)->httpUrl;
			// $redirect = $this->input->canonicalUrl();
		}
		// TODO WIP - WILL NEED CHANGING AS NO SPECIAL VARIATION ID; THEY ARE JUST PRODUCTS THEMSELVES
		if ($this->input->post->pwcommerce_cart_add_product_variation_id) {
			$variation_id = $this->input->post->pwcommerce_cart_add_product_variation_id;
		} else {
			$variation_id = 0;
		}

		$this->add($id, $quantity, $redirect, $variation_id);
		// TODO TESTING NEW SPECIAL ADD FOR LIVE STOCK CHECKER
	}

	/**
	 *  prepare Remove.
	 *
	 * @return mixed
	 */
	private function _prepareRemove() {
		$id = (int) $this->input->post->product_id;
		$this->remove($id);
	}

	/**
	 *  prepare Update Cart.
	 *
	 * @return mixed
	 */
	private function _prepareUpdateCart() {
		$products = $this->input->post->pwcommerce_cart_products;
		$rem_products = $this->input->post->pwcommerce_cart_remove_product;
		$this->updateCart($products, $rem_products);
	}

	// ~~~~~~~~~~~~~~~~~

	/**
	 * Add product into cart. If same product already exists in cart, then update quantity
	 *
	 * @param mixed $product_id
	 * @param int $quantity
	 * @param int $variation_id
	 * @return mixed
	 */
	public function ___addProduct($product_id, $quantity = 1, $variation_id = 0) {
		// TODO - USE TRY/CATCH?
		// =================


		$product_id = (int) $product_id;
		$product = $this->pages->get($product_id);

		# ERROR: PRODUCT NOT FOUND #
		if (!$product->id)
			throw new WireException("Product not found!");
		# ERROR: NOT A PRODUCT OR VARIANT
		if (!in_array($product->template->name, [PwCommerce::PRODUCT_TEMPLATE_NAME, PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME]))
			throw new WireException("Page template is not allowed product template!");

		# ERROR: PRODUCT/VARIANT IS NOT ENABLED for selling #
		// TODO: silent fail?
		if (!$this->isProductEnabledForSelling($product))
			throw new WireException("Product not enabled for selling!");

		# CHECK IF TRACKING INVENTORY && ALLOWING BACK ORDERS/OVERSELLING OF THIS PRODUCT/VARIANT #
		// @note: back orders only make sense if tracking inventory! otherwise, stock never depletes!
		// TODO; add notes/docs about above!

		// TODO - DELETE WHEN DONE
		// if (!$this->allow_negative_stock) {
		// 	$oldQty = $this->getProductQuantityFromCart($product_id);
		// 	$newQty = $oldQty + $quantity;
		// 	$stockOk = $this->checkStock($product, $newQty);
		// 	if (!$stockOk) return false;
		// }

		if ($this->isProductTrackInventory($product) && !$this->isProductAllowOverSelling($product)) {
			// product is tracking inventory AND product/variant DOES NOT ALLOW OVERSELLING
			// CHECK if we have ENOUGH STOCK of this product/variant
			$oldQty = $this->getProductQuantityFromCart($product_id);
			$newQty = $oldQty + $quantity;
			$stockOk = $this->checkStock($product, $newQty);
			if (!$stockOk)
				return false;
		}

		// -------------
		// check if given product/variant is already in the cart
		$cart_row_id = $this->checkIfProductInCart($product_id, $variation_id);
		if ($cart_row_id) {
			// product/variant already in cart; update cart instead of adding again
			$this->updateProduct($cart_row_id, $quantity, $addQty = true);
			return true;
		}

		// ---------
		// ADD NEW ITEM TO CART!
		$this->addNewProduct($product_id, $quantity);
		return true;

	}

	/**
	 * Updates session reference in database. This is required when user logs in and
	 *
	 * @param mixed $old_session
	 * @param mixed $new_session
	 * @return mixed
	 */
	public function updateSession($old_session, $new_session) {
		$sql = "UPDATE " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " SET session_id = :new_session WHERE session_id = :old_session OR user_id = :user_id";
		$sth = $this->database->prepare($sql);
		$user_id = wire('user')->id;
		$sth->bindParam(":new_session", $new_session);
		$sth->bindParam(":old_session", $old_session);
		$sth->bindParam(":user_id", $user_id);
		$sth->execute();
	}

	/**
	 * Updates product quantity.
	 *
	 * @param mixed $cart_row_id
	 * @param mixed $quantity
	 * @param bool $addQty
	 * @return mixed
	 */
	public function ___updateProduct($cart_row_id, $quantity, bool $addQty = false) {
		$quantity = (int) $quantity;

		if ($quantity == 0) {
			return $this->removeProduct($cart_row_id);
		}

		$product = $this->getProduct($cart_row_id);
		# CHECK IF TRACKING INVENTORY && ALLOWING BACK ORDERS/OVERSELLING OF THIS PRODUCT/VARIANT #
		// @note: back orders only make sense if tracking inventory! otherwise, stock never depletes!
		// TODO; add notes/docs about above!

		// TODO - DELETE WHEN DONE
		// if (!$this->allow_negative_stock) {
		// 	$oldQty = $this->getProductQuantityFromCart($product->id);
		// 	if ($addQty) $newQty = $oldQty + $quantity;
		// 	else $newQty = $quantity;
		// 	$stockOk = $this->checkStock($product, $newQty);
		// 	if (!$stockOk) return false;
		// }

		if ($this->isProductTrackInventory($product) && !$this->isProductAllowOverSelling($product)) {
			// product is tracking inventory AND product/variant DOES NOT ALLOW OVERSELLING
			// CHECK if we have ENOUGH STOCK of this product/variant
			$oldQty = $this->getProductQuantityFromCart($product->id);
			if ($addQty) {
				$newQty = $oldQty + $quantity;
			} else {
				$newQty = $quantity;
			}
			$stockOk = $this->checkStock($product, $newQty);
			if (!$stockOk)
				return false;
		}

		// ###############

		$tableName = PwCommerce::PWCOMMERCE_CART_TABLE_NAME;

		if ($addQty) {
			$sql = "UPDATE $tableName SET quantity = quantity + :quantity WHERE session_id = :session_id AND id = :id";
		} else {
			$sql = "UPDATE $tableName SET quantity = :quantity WHERE session_id = :session_id AND id = :id";
		}

		$sth = $this->database->prepare($sql);
		$sth->bindParam(":quantity", $quantity);
		$sth->bindParam(":session_id", $this->session_id);
		$sth->bindParam(":id", $cart_row_id);
		$sth->execute();

		return true;
	}

	/**
	 * Add new product into cart.
	 *
	 * @param mixed $product_id
	 * @param mixed $quantity
	 * @return mixed
	 */
	public function ___addNewProduct($product_id, $quantity) {
		// NOTE: FOR NEWLY ADD ITEMS. FOR UPDATE CART, @see ___updateProduct

		// TODO DELETE WHEN DONE; WE DON'T HAVE A SPECIAL VARIATION ID; IT IS JUST A PRODUCT
		// $sql = "INSERT INTO " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " SET session_id = :session_id, user_id = :user_id, product_id = :product_id, variation_id = :variation_id, quantity = :quantity";
		$sql = "INSERT INTO " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " SET session_id = :session_id, user_id = :user_id, product_id = :product_id, quantity = :quantity";

		$sth = $this->database->prepare($sql);
		$sth->bindParam(":session_id", $this->session_id);
		$user_id = ($this->user->isGuest()) ? null : $this->user->id;
		$sth->bindParam(":user_id", $user_id);
		$sth->bindParam(":product_id", $product_id);
		// $sth->bindParam(":variation_id", $variation_id);
		$sth->bindParam(":quantity", $quantity);
		$sth->execute();

	}

	/**
	 *    add.
	 *
	 * @param int $id
	 * @param int $quantity
	 * @param bool $redirect
	 * @param int $variation_id
	 * @return mixed
	 */
	public function ___add($id, $quantity = 1, bool $redirect = false, $variation_id = 0) {


		// TODO @KONGONDO NOT SURE IF WE NEED THIS VARIATION_ID SINCE OUR VARIATIONS ARE INDEPENDENT PAGES
		// TODO REVISIT THIS TO HANDLE ERRORS BETTER!
		$errors = [];
		if (empty($id)) {
			$errors[] = $this->productNotFound;
			$this->_redirect($redirect, array("errors" => $errors));
		}

		if ($this->addProduct($id, $quantity, $variation_id)) {
			// ++++++++++++++++++
			// ===============
			$totalQty = $this->getQuantity();
			$numberOfTitles = $this->getNumberOfTitles();
			$productTitle = $this->getProductTitle($this->pages->get($id));
			$totalAmount = $this->renderPriceAndCurrency($this->getTotalAmount());
			$this->_redirect(
				$redirect,
				array(
					"productId" => $id,
					"variationId" => $variation_id,
					"productTitle" => $productTitle,
					"quantity" => $quantity,
					"totalQty" => $totalQty,
					"numberOfTitles" => $numberOfTitles,
					"totalAmount" => $totalAmount,
				)
			);
		} else {
			// TODO REVISIT THIS TO HANDLE ERRORS BETTER!
			$errors[] = $this->outOfStock;
			$this->_redirect($redirect, array("errors" => $errors));
		}
	}

	/**
	 *    remove.
	 *
	 * @param int $id
	 * @return mixed
	 */
	public function ___remove($id) {


		$this->removeProduct($id);
		// ++++++++++++++++++

		$this->_redirect($this->input->post->pwcommerce_cart_redirect, array("removedProduct" => $id));
	}

	/**
	 * Remove product from cart
	 *
	 * @param mixed $cart_row_id
	 * @param bool $isDeleteFromOrder
	 * @return mixed
	 */
	public function ___removeProduct($cart_row_id, bool $isDeleteFromOrder = false) {
		if (!empty($isDeleteFromOrder)) {
			// delete associated order line item page if it exists. this can happen if customer had already started checkout process
			$this->deleteProductFromOrder($cart_row_id);
		}
		// =============
		$sql = "DELETE FROM " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " WHERE session_id = :session_id AND id = :id";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":session_id", $this->session_id);
		$sth->bindParam(":id", $cart_row_id);
		$sth->execute();
	}

	// TODO @kongondo
	/**
	 * Delete Product From Order.
	 *
	 * @param mixed $cart_row_id
	 * @return mixed
	 */
	private function deleteProductFromOrder($cart_row_id) {

		// First, get the product. We need its ID to get associated line item
		$product = $this->getProduct($cart_row_id);

		if (!empty($product->id)) {
			$orderPage = $this->getOrderPage();
			if ($orderPage instanceof Page) {

				// TWO WAYS
				// 1. get from order page
				// ------------
				// TODO, check access? check_access=0 YES! but use $pwcommerce finder below to sort it out for us
				// $orderLineItemPage = $orderPage->child("pwcommerce_order_line_item.productID={$product->id},check_access=0");

				// or alernative
				$orderLineItemPage = $this->get("parent={$orderPage},line_item.productID={$product->id}");

				if ($orderLineItemPage->id) {

					$orderLineItemPage->delete();
				}
			}
		}
		// -----------
		// OR
		// 2. get from getOrderLineItems()
		// ------------
		/** @var WireArray $orderLineItems */
		// $orderLineItems = $this->getOrderLineItems();

		// $orderLineItemToDelete = $orderLineItems->get("productID={$product->id}");

		// if (!empty($orderLineItemToDelete)) {
		// 	$orderLineItemPageID = $orderLineItemToDelete->id;

		// 	$orderLineItemPage = $this->wire('pages')->get($orderLineItemPageID);

		// 	if ($orderLineItemPage->id) {

		// 		// $orderLineItemPage->delete();
		// 	}
		// }

		//
	}

	/**
	 * Empty the whole cart
	 *
	 * @return mixed
	 */
	public function ___emptyCart() {
		$sql = "DELETE FROM " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " WHERE session_id = :session_id";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":session_id", $this->session_id);
		$sth->execute();
	}

	/**
	 * Get number of items in cart. Gives actual quantity, so might be more than there are items in cart.
	 *
	 * @return mixed
	 */
	public function getQuantity() {
		$count = 0;
		foreach ($this->getCartRaw() as $prod) {
			$count = $count + $prod->quantity;
		}
		// TODO (int) ???
		return $count;
	}

	/**
	 * Get number of unique titles in cart.
	 *
	 * @return mixed
	 */
	public function getNumberOfTitles() {
		return count($this->getCartRaw());
	}

	/**
	 * Get total amount from cart.
	 *
	 * @return mixed
	 */
	public function getTotalAmount() {
		$amount = 0;
		foreach ($this->getCart() as $product) {
			$amount += $product->pwcommerce_price_total;
		}
		return $amount;
	}

	/**
	 * Check if there is enough stock left for required product and quantity
	 *
	 * @param Page $product
	 * @param mixed $quantity
	 * @return mixed
	 */
	public function ___checkStock(Page $product, $quantity) {
		// TODO: IMPLEMENT TOGETHER WITH ALLOW BACK ORDERS - ALSO, PWCOMMERCE 2 FIELD IS IN PRODUCT STOCK
		// return;
		// $quantityfield = $this->quantityfield;
		$stockQuantityProduct = $this->getProductRemainingStockQuantity($product);
		// if ($product->$quantityfield >= $quantity) return true;
		if ($stockQuantityProduct >= $quantity)
			return true;
		else
			return false;
	}

	/**
	 * Check if certain product is already in cart
	 *
	 * @param mixed $product_id
	 * @param int $variation_id
	 * @return mixed
	 */
	public function ___checkIfProductInCart($product_id, $variation_id = 0) {

		$alreadyInCart = false;
		// TODO DELETE WHEN DONE; WE DON'T HAVE A SPECIAL VARIATION ID
		// $sql = "SELECT * FROM " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " WHERE session_id = :session_id AND product_id = :product_id AND variation_id = :variation_id";
		$sql = "SELECT * FROM " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " WHERE session_id = :session_id AND product_id = :product_id";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":session_id", $this->session_id);
		$sth->bindParam(":product_id", $product_id);
		// $sth->bindParam(":variation_id", $variation_id);
		$sth->execute();
		$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

		if (count($rows))
			$alreadyInCart = $rows[0]['id'];

		return $alreadyInCart;
	}

	/**
	 * When given $cart_row_id, it returns product page
	 *
	 * @param mixed $cart_row_id
	 * @return mixed
	 */
	public function ___getProduct($cart_row_id) {
		$sql = "SELECT product_id FROM " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " WHERE id = :id LIMIT 1";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":id", $cart_row_id);
		$sth->execute();
		$row = $sth->fetch(\PDO::FETCH_ASSOC);
		// ---------------------------
		// TODO use getRaw() below?
		$rowProductID = !empty($row['product_id']) ? (int) $row['product_id'] : 0;
		// $product = $this->pages->get($row['product_id']);
		$product = $this->pages->get($rowProductID);

		return $product;
	}


	/**
	 * Returns product price - method specifially for hooking.
	 *
	 * @param Page $product
	 * @return mixed
	 */
	public function ___getProductPrice(Page $product) {
		// --------------
		/** @var WireData $stock */
		$stock = $product->get('pwcommerce_product_stock');
		// GET DETERMINED PRODUCT PRICE
		// @note: 'price' has been determined in FieldtypePWCommerceProductStock
		// it considers 'sale vs normal price' if applicable
		// else uses 'price' if shop strategy is 'price and compare price'
		$price = (float) $stock->price;
		// ----------------
		return $price;
	}

	/**
	 * Return product title - method specifially for hooking.
	 *
	 * @param Page $product
	 * @param bool $lang
	 * @return mixed
	 */
	public function ___getProductTitle(Page $product, bool $lang = false) {
		if ($lang) {
			return $product->getLanguageValue($lang, 'title');
		}
		return $product->title;
	}





	/**
	 * Check Live Stock.
	 *
	 * @param int $productID
	 * @param int $quantity
	 * @param int $intervalValue
	 * @param string $intervalType
	 * @return array
	 */
	public function checkLiveStock(int $productID, int $quantity = 1, int $intervalValue = 1, string $intervalType = 'day'): array {
		$intervalType = $this->wire('sanitizer')->fieldName($intervalType);

		$results = [
			'notice' => '',
			'notice_type' => 'error',
			'interval_value' => $intervalValue,
			'interval_type' => $intervalType,
		];

		// FIRST, CONFIRM THE PRODUCT TRACKS INVENTORY
		$product = $this->pages->get($productID);
		if ($product instanceof NullPage) {
			$results['notice'] = "product_not_found";
			return $results;
		}

		$isProductTrackInventory = $this->isProductTrackInventory($product);
		if (empty($isProductTrackInventory)) {
			// TODO RETURN error? or other message? e.g. 'yes', 'no', 'n/a'??? 'null'??
			$results['notice'] = "product_does_not_track_inventory";
			return $results;
		}
		// ----
		// GET CURRENT STOCK QUANTITY
		// as saved since last completed order
		$maximumStockAvailable = $this->getProductRemainingStockQuantity($product);

		// GET CURRENT/LIVE GLOBAL COUNT OF PRODUCT IN CARTS
		/** @var WireData $productCountInAllCarts */
		$productCountInAllCarts = $this->getProductCountInAllCarts($productID, $intervalValue, $intervalType);

		$remainingStock = ($maximumStockAvailable - $productCountInAllCarts->count);


		$isProductLiveStockSufficient = !($quantity > $remainingStock);

		if (empty($isProductLiveStockSufficient)) {
			// INSUFFICIENT LIVE STOCK
			$notice = 'insufficent_stock';
			$noticeType = 'error';
		} else {
			//  SUFFICIENT LIVE STOCK
			$notice = 'sufficent_stock';
			$noticeType = 'success';
		}

		$results = [
			'notice' => $notice,
			'notice_type' => $noticeType,
			'product_id' => $productID,
			'requested_quantity' => $quantity,
			'maximum_stock_available' => $maximumStockAvailable,
			'product_count_in_all_carts' => (int) $productCountInAllCarts->count,
			'remaining_stock_level' => $remainingStock,
			'is_product_live_stock_sufficient' => $isProductLiveStockSufficient,
			// ------
			'interval_value' => $intervalValue,
			'interval_type' => $intervalType,
		];

		return $results;

	}

	/**
	 * Get Product Count In All Carts.
	 *
	 * @param int $productID
	 * @param int $intervalValue
	 * @param string $intervalType
	 * @return mixed
	 */
	private function getProductCountInAllCarts(int $productID, int $intervalValue, string $intervalType) {
		return $this->processQueryProductCountInAllCarts($productID, $intervalValue, $intervalType);
	}

	/**
	 * Get Product Stock.
	 *
	 * @param int $productID
	 * @return mixed
	 */
	private function getProductStock($productID) {
		$fields = ['title', 'pwcommerce_product_stock' => 'stock'];
		$productStock = $this->getRaw("id={$productID}", $fields);
		if (empty($productStock)) {
			// TODO ERROR HANDLING!
		}
	}

	/**
	 *    add Product Get Array.
	 *
	 * @param int $id
	 * @param int $quantity
	 * @return mixed
	 */
	public function ___addProductGetArray($id, $quantity = 1) {


		// TODO @KONGONDO NOT SURE IF WE NEED THIS VARIATION_ID SINCE OUR VARIATIONS ARE INDEPENDENT PAGES
		// TODO REVISIT THIS TO HANDLE ERRORS BETTER!
		$errors = [];
		if (empty($id)) {
			$errors[] = $this->productNotFound;
		}

		if (empty($errors)) {
			$addProductResponse = $this->addProduct($id, $quantity);
			if (empty($addProductResponse)) {
				// TODO DIFFERENTIATE BETWEEN 'LIVE' OUT OF STOCK AND OTHER DB ERRO!
				$errors[] = $this->_('Could not add product to cart');
			}
		}

		if (empty($errors)) {
			// SUCCESS
			$noticeType = 'success';
			$notice = sprintf(__('Item with ID %d successfully added to cart'), $id);
		} else {
			// ERRORS
			$noticeType = 'error';
			$notice = sprintf(__('Item with ID %d could not be added to cart'), $id);
		}

		$result = [
			'notice' => $notice,
			'notice_type' => $noticeType,
			'product_id' => $id,
			'quantity' => $quantity,
		];

		// --------
		return $result;

	}

	// /**
  *    update Cart.
  *
  * @param mixed $products
  * @param mixed $rem_products
  * @return mixed
  */
 public function ___updateCart($products = null, $rem_products = null) {
	// TODO @KONGONDO AMENDMENT $isRedirect!
	// @note: for some htmx cases, we don't need to redirect; we use $isRedirect argument for this

	/**
	 *    update Cart.
	 *
	 * @param mixed $products
	 * @param mixed $rem_products
	 * @param bool $isRedirect
	 * @return mixed
	 */
	public function ___updateCart($products = null, $rem_products = null, bool $isRedirect = true) {

		$removedProductIDs = [];
		$errors = [];
		$isOrderAlreadyConfirmed = $this->isOrderAlreadyConfirmed();

		// gather cart items IDs of removed cart products/items
		// needed to check if item is wholly removed from the basket (if using non-ajax POST)
		$removedProductsCartItemsIDs = !is_null($rem_products) ? array_keys($rem_products) : [];

		if (!empty($products)) {
			foreach ($products as $id => $cartProductQuantity) {
				if ($isOrderAlreadyConfirmed) {
					if (in_array($id, $removedProductsCartItemsIDs) || empty($cartProductQuantity)) {
						$removedProduct = $this->getProduct($id);
						$removedProductIDs[] = $removedProduct->id;
					}
				} else {

				}
				// ---------------
				if (!$this->updateProduct($id, $cartProductQuantity)) {
					$product = $this->getProduct($id);
					$productTitle = $this->getProductTitle($product);
					$errors[] = sprintf(__('Quantity for %s could not be updated'), $productTitle);
				}
			}
		}

		if (!empty($rem_products)) {
			foreach ($rem_products as $id => $remove) {
				if ($remove)
					$this->removeProduct($id);
			}
		}

		// -------------------
		// PROCESS REMOVED PRODUCTS IF ORDER HAD ALREADY BEEN CONFIRMED AND BASKET SUBSEQUENTLY AMENDED
		if (!empty($removedProductIDs)) {
			// TODO MERGE WITH THOSE IN SESSION? CREATE NEW?
			// removedProductIDsForLineItems
			$this->processExistingLineItemsRemovedFromCart($removedProductIDs);
		} else {

		}
		// ++++++++++++++++++

		// --------------
		// TODO FOR AJAX/HTMX CASES, NEED TO SEE HOW TO RETURN ERRORS!
		if (!empty($isRedirect)) {
			$this->_redirect($this->input->post->pwcommerce_cart_redirect, array("cartUpdated" => 1, "errors" => $errors));
		}
	}

	/**
	 * Is Order Already Confirmed.
	 *
	 * @return bool
	 */
	private function isOrderAlreadyConfirmed() {
		return $this->getOrderPage() instanceof Page;
	}

	# ~~~~~~~~~~~~~


	/**
	 * Update database schema
	 *
	 * @return mixed
	 */
	private function updateDatabaseSchema() {
		// TODO - DELETE WHEN DONE; NOT NEEDED AS IN PWCOMMERCE 2, WE DON'T USE THIS COLUMN; NO SPECIAL VARIATION ID; THEY ARE ALL JUST PRODUCTS
		while ($this->schema_version < $this->desired_dbschema_ver) {
			++$this->schema_version;
			switch ($this->schema_version) {
				case 1:
					$sql = "ALTER TABLE " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " ADD variation_id VARCHAR(255) NOT NULL DEFAULT 0 AFTER product_id";
					break;
				default:
					throw new WireException("Unrecognized database schema version: $this->schema_version");
			}
			if ($sql && $this->database->exec($sql) !== false) {
				$configData = $this->modules->getModuleConfigData($this);
				$configData['schema_version'] = $this->schema_version;
				$this->modules->saveModuleConfigData($this, $configData);
			} else {
				throw new WireException("Couldn't update database schema to version $this->schema_version");
			}
		}
	}

	/**
	 *    upgrade.
	 *
	 * @param mixed $fromVersion
	 * @param mixed $toVersion
	 * @return mixed
	 */
	public function ___upgrade($fromVersion, $toVersion) {
		return;
		// @KONDONGO -> DELETE AS WE WON'T NEED THIS!
		if ($fromVersion == 1 && $toVersion == 2) {
			$fields_json = file_get_contents(__DIR__ . "/data/fields.json");
			$fieldsData = json_decode($fields_json, true);
			$f = new Field();
			$f->setImportData($fieldsData['pwcommerce_variation_id']);
			$f->save();

			$pp = $this->templates->get("padorder_product");
			$pp->fields->add($f);
			$pp->fields->save();
			$this->message("Added variation id field into padorder_product template");
		}
	}

	/**
	 * Install.
	 *
	 * @return mixed
	 */
	public function install() {
		$sql = <<<_END

    CREATE TABLE `$this->dbname` (
      id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      session_id VARCHAR(255) NOT NULL,
      user_id INT(10) UNSIGNED NULL,
      product_id INT(10) UNSIGNED NULL,
      quantity INT UNSIGNED NULL,
      last_modified TIMESTAMP NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      INDEX `session_id` (`session_id`)
      )
      ENGINE = MyISAM DEFAULT CHARSET=utf8;
_END;

		$sth = $this->database->prepare($sql);
		$sth->execute();
	}

	/**
	 * Uninstall.
	 *
	 * @return mixed
	 */
	public function uninstall() {
		$sth = $this->database->prepare("DROP TABLE `$this->dbname`");
		$sth->execute();
	}

	# >>>>>>>>>>>>>>>>>>>>>>>>>> DEPRECATED <<<<<<<<<<<<<<<<<<<<<<<

	/**
	 * Gets the session's order total amount (value).
	 *
	 * @return mixed
	 */
	public function getOrderTotalAmount() {
		// TODO RENAME THIS METHOD? TO BE CLEAR NO TAXES?
		return $this->getTotalAmount();
	}
}