const InputfieldPWCommerceCustomerAddresses = {
	listenToHTMXRequests: function () {
		// after settle
		htmx.on("htmx:afterSettle", function (event) {
			// >>>>>>>>>
			// RUN POST SETTLE OPS
			InputfieldPWCommerceCustomerAddresses.runAfterSettleOperations(event)
			//  RE-PARENT NEWLY INSERTED ITEM (li)
			// it needs to live under a <ul class='Inputfields'></ul>
			// @todo: need to cater for multiple inserts as well!
			InputfieldPWCommerceCustomerAddresses.reparentNewInputfields(event)
		})
	},

	/**
	 * Run afterSettle operations (after htmx swap).
	 * These depend on the htmx request context.
	 * Use this so that alpine js can work on 'settled' dom contents.
	 * @param {object} event Object containing the event that triggered the request or custom object with post-op details.
	 */
	runAfterSettleOperations: function (event) {
		// get markup (ul) of response HTML sent by server
		const responseMarkup = event.detail.elt.lastChild
		if (!responseMarkup) return

		const newFieldsetWrapper = event.detail.elt

		// #################
		// @note: unfortunately, we need jQuery for this and below
		const newFieldsetWrapperJQ = $(newFieldsetWrapper)
		// TRIGGER RELOAD:INIT  (ul.Inputfields)
		PWCommerceCommonScripts.reloadAllInputfields(newFieldsetWrapperJQ) // the ul.Inputfields
		//-----------
		// TRIGGER RELOAD: ALL INPUTFIELDS (li.Inputfield) except special ones below
		PWCommerceCommonScripts.reloadAllInputfield(newFieldsetWrapperJQ) // the li.Inputfield

		// #################

		// TODO NOT IN USE FOR NOW; DELETE IF NOT NEEDED! (we send fieldset open from server)
		// RUN OPEN NEW FIELDSET
		// const newFieldset = event.detail.elt.querySelector(
		// 	"li.InputfieldFieldset:last-child"
		// )
		// used for newly added/created fieldsets
		// open the new fieldset (single, last newly inserted fieldset)
		// Inputfields.open(newFieldset)
		// @note: we need jQuery (sigh!!) since required by Inputfields below
		// Inputfields.open($(newFieldset))
		// Inputfields.toggle(newFieldset)

		// #####################################

		// REINIT ADDRESS TYPE RADIOS CHANGE LISTENER (for AlpineJS use)
		InputfieldPWCommerceCustomerAddresses.initListenToAddressTypeChange()
		//-----------
		// TRIGGER RELOAD: InputfieldPageAutocomplete
		const selectorString = ".InputfieldPageAutocomplete.pwcommerce_is_new_item"
		PWCommerceCommonScripts.reloadPageAutocomplete(selectorString)

		InputfieldPWCommerceCustomerAddresses.runSpecificAfterSettleOperation(event)
	},

	runSpecificAfterSettleOperation: function (event) {
		// FOCUS ON THE FIRST NAME INPUT OF THE LATEST INSERTED ADDRESS
		const firstNameInputElement = event.detail.elt.querySelector(
			"li.InputfieldFieldset:last-child .pwcommerce_customer_address_first_name"
		)

		if (firstNameInputElement) {
			// if we have an element, focus it after a few ms to allow for settling
			setTimeout(() => {
				firstNameInputElement.focus()
			}, 300)
		}
	},

	// ~~~~~~~~~~~

	// used by contexts that add items to runtimemarkup list - e.g add attribute options, add shipping rate and generate product variants
	// these come in under their own (incorrect) ul parents
	// we reparent them here, adding them to the existing ul (correct/new parent) then discard the 'incorrect' and now empty parent.
	reparentNewInputfields: function (event) {
		// the variants parent: ul.Inputfields
		const newParentElement = event.detail.elt
		// the ul.Inputfields that came with the new li.InputfieldFieldset.InputfieldPWCommerceCustomerAddressesItem.pwcommerce_is_new_runtime_markup_item
		// @note: this is the 'incorrect' parent as we end up with <li>existing variant</li><ul><li>new variant</li>...</ul>
		// @note: this old (incorrect) parent is the last child of the correct/new parent above
		const oldParentElement = event.detail.elt.lastElementChild
		// the li.InputfieldFieldset.InputfieldPWCommerceCustomerAddressesItem.pwcommerce_is_new_runtime_markup_item
		//const children = event.detail.elt.lastElementChild.children
		const children = oldParentElement.children

		if (children) {
			// append them!
			// @note: we were having problems with looping (only 1 child getting appended) but spread syntax works. maybe it was a race condition?
			newParentElement.append(...oldParentElement.childNodes)
			// remove their 'incorrect/old parent'
			oldParentElement.remove()
		}
	},

	// ~~~~~~~~~~~~~~~~~~~

	/**
	 * Listen to customer address type changes.
	 *
	 * We use to ensure only one 'primary shipping' and one 'primary billing' address
	 */
	initListenToAddressTypeChange: function () {
		const addressTypeElements = document.querySelectorAll(
			"li input.pwcommerce_customer_addresses_type"
		)

		if (addressTypeElements) {
			for (const addressTypeElement of addressTypeElements) {
				// add event listener to each radio input
				addressTypeElement.addEventListener(
					"change",
					InputfieldPWCommerceCustomerAddresses.handleAddressTypeChange,
					false
				)
			}
		}
	},

	handleAddressTypeChange: function (event) {
		const selectedAddressTypeElement = event.target
		const selectedAddressType = selectedAddressTypeElement.value

		// if changing value to either 'shipping_primary' or 'billing_primary'
		if (["shipping_primary", "billing_primary"].includes(selectedAddressType)) {
			const currentAddressID = selectedAddressTypeElement.id

			// check if there is another radio with an identical 'primary' value
			const selector = `input.pwcommerce_customer_addresses_type[value="${selectedAddressType}"]:checked:not(#${currentAddressID})`
			const existingPrimaryAddressElement = document.querySelector(selector)

			if (existingPrimaryAddressElement) {
				// found 'duplicate' selection for primary!
				// change checked radio to the one with the a similar value but 'non-primary'
				const amendedValue = selectedAddressType.replace("_primary", "")

				/* find the sibling */
				// first get the top parent
				const existingPrimaryAddressParentElement =
					existingPrimaryAddressElement.closest("ul")

				// then get the input by value
				if (existingPrimaryAddressParentElement) {
					const siblingSelector = `input.pwcommerce_customer_addresses_type[value="${amendedValue}"]`
					const existingPrimaryAddressSiblingElement =
						existingPrimaryAddressParentElement.querySelector(siblingSelector)
					if (existingPrimaryAddressSiblingElement) {
						// check the sibling element
						existingPrimaryAddressSiblingElement.checked = true
					}
				}
			}
		}
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		if (typeof htmx !== "undefined") {
			// @TODO: GENERIC FOR PWCOMMERCE RUNTIME ITEMS
			InputfieldPWCommerceCustomerAddresses.listenToHTMXRequests()
		}
		// ----------
		// listen to customer address type (radio) changes
		InputfieldPWCommerceCustomerAddresses.initListenToAddressTypeChange()
	}
	// end: if in pwcommerce shop context
})

// ~~~~~~~~~~~~~
// ALPINE
document.addEventListener("alpine:init", () => {
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		Alpine.store("InputfieldPWCommerceCustomerAddressesStore", {
			// collection of IDs of items marked for deletion
			items_to_delete: [],
			primary_shipping_address_id: null,
			primary_billing_address_id: null,
		})

		Alpine.data("InputfieldPWCommerceCustomerAddressesData", () => ({
			//---------------
			// FUNCTIONS

			/**
			 * Set a store property value.
			 * @param any value Value to set in store.
			 * @return {void}.
			 */
			setStoreValue(property, value) {
				this.$store.InputfieldPWCommerceCustomerAddressesStore[property] = value
			},

			// ~~~~~~~~~~~~~~~~

			/**
			 * Get the value of a given store property.
			 * @param string property Property in store whose value to return
			 * @returns {any}
			 */
			getStoreValue(property) {
				return this.$store.InputfieldPWCommerceCustomerAddressesStore[property]
			},

			// ~~~~~~~~~~~~~~~~
			handleCopyCustomerNames(customer_address_id) {
				const properties = ["first_name", "middle_name", "last_name"]
				for (const property of properties) {
					const customerInputID = `pwcommerce_customer_${property}`
					// get the corresponding input element from customer main details
					customerInputElement = document.getElementById(customerInputID)
					if (customerInputElement) {
						const customerAddressPropertyValue = customerInputElement.value
						const customerAddressProperty = `pwcommerce_customer_address_${property}_${customer_address_id}`
						// set store value using copied value
						this.setStoreValue(
							customerAddressProperty,
							customerAddressPropertyValue
						)
					}
				}
			},

			// ~~~~~~~~~~~~~~~~

			toggleMarkItemForDeletion(event = {}, itemID, trash_element = null) {
				let showDeleteIntent = false
				// clone items to delete
				let itemsToDeleteClone = [...this.getStoreValue("items_to_delete")]

				// IF ITEM ALREADY SELECTED, remove it
				if (itemsToDeleteClone.includes(itemID)) {
					itemsToDeleteClone = itemsToDeleteClone.filter(
						(item_id) => item_id !== itemID
					)
				} else {
					// else ADD IT
					itemsToDeleteClone.push(itemID)
					showDeleteIntent = true
				}

				//---------------
				this.setStoreValue("items_to_delete", itemsToDeleteClone)

				//------------------
				// TOGGLE DELETE INTENT 'classes'
				// trashElement IS: i.fa-trash.pwcommerce_customer_address_item_delete
				// ancestor parent of
				// const trashElement = event.target;
				const trashElement = trash_element ? trash_element : event.target
				// ancestor parent of
				// trashElement IS: i.fa-trash.pwcommerce_customer_address_item_delete
				// if trash element sent, use it, else get from event
				const parentInputfieldHeaderLabelElement = trashElement.closest(
					"label.InputfieldHeader"
				)
				// toggle class for in 'pending-delete-bin'
				this.toggleDeletePending(
					parentInputfieldHeaderLabelElement,
					showDeleteIntent
				)
				// toggle class to show 'ui-state-error'
				this.toggleUIStateError(
					parentInputfieldHeaderLabelElement,
					showDeleteIntent
				)
			},

			handleDoubleClickToggleMarkItemsForDeletion() {
				// GET ALL TRASH ELEMENTS
				const trashElements = document.querySelectorAll(
					"i.pwcommerce_customer_address_item_delete"
				)
				if (trashElements) {
					// PASS EACH TO TOGGLE DELETE METHOD
					for (const trashElement of trashElements) {
						const itemID = parseInt(trashElement.dataset.itemId)
						// mark for deletion
						this.toggleMarkItemForDeletion({}, itemID, trashElement)
					}
				}
			},

			// hovering the trash of a runtime item gives a preview of what clicking it would do
			handleDeleteIntent(event) {
				const showDeleteIntent = event.type === "mouseenter"
				//------------------
				const trashElement = event.target // i.fa-trash.pwcommerce_customer_address_item_delete
				// ancestor parent of
				const parentInputfieldHeaderLabelElement = trashElement.closest(
					"label.InputfieldHeader"
				)
				// if we found the parent label and it is NOT ALREADY marked for deletion
				// toggle class 'ui-state-error'
				if (
					parentInputfieldHeaderLabelElement &&
					!parentInputfieldHeaderLabelElement.classList.contains(
						"pwcommerce_customer_address_item_delete_pending"
					)
				) {
					this.toggleUIStateError(
						parentInputfieldHeaderLabelElement,
						showDeleteIntent
					)
				}
			},

			// add/remove class 'ui-state-error' for showing intention to delete an item (page)
			toggleUIStateError(element, isAddErrorState = true) {
				// element.classList.toggle("ui-state-error") // @note: clashes with with toggleDeletePending()!
				if (isAddErrorState) {
					element.classList.add("ui-state-error")
				} else {
					element.classList.remove("ui-state-error")
				}
			},

			// add/remove class 'pwcommerce_customer_address_item_delete_pending' to show item ID in 'pending-delete-bin'
			// @note: needed to ignore handleDeleteIntent() since already marked for deletion
			toggleDeletePending(element, isAddDeletePending = true) {
				if (isAddDeletePending) {
					element.classList.add("pwcommerce_customer_address_item_delete_pending")
				} else {
					element.classList.remove(
						"pwcommerce_customer_address_item_delete_pending"
					)
				}
			},
		}))
	}
	// end: if in pwcommerce shop context
})
