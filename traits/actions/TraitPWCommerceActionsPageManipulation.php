<?php

namespace ProcessWire;

trait TraitPWCommerceActionsPageManipulation
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PAGE MANIPULATION ~~~~~~~~~~~~~~~~~~



	private function actionPublishItems() {

		$items = $this->items;
		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!
		if (empty($items)) {
			return null;
		}

		//------------------
		// good to go
		$pages = $this->getItemsToAction();
		$i = 0;
		// action each item
		foreach ($pages as $page) {
			// skip if page is locked
			if ($page->isLocked()) {
				continue;
			}

			//------------------------------
			// publish/activate
			// if ($this->action === 'publish') {
			if (in_array($this->action, ['publish', 'activate'])) {
				$page->removeStatus(Page::statusUnpublished);
			} else {
				// TODO: SHOULD WE SKIP UNPUBLISH IF ITEM IS REFERENCED
				// unpublish/deactivate
				$page->addStatus(Page::statusUnpublished);
			}
			//-------------
			$i++;
			//-------------
			// save the page
			$page->save();
		}

		// --------------------
		// prepare messages
		if ($this->action === 'publish') {
			// published
			$notice = sprintf(_n("Published %d item.", "Published %d items.", $i), $i);
		} elseif ($this->action === 'activate') {
			// activated
			$notice = sprintf(_n("Activated %d item.", "Activated %d items.", $i), $i);
		} elseif ($this->action === 'deactivate') {
			// deactivated
			$notice = sprintf(_n("Deactivated %d item.", "Deactivated %d items.", $i), $i);
		} else {
			// unpublished
			$notice = sprintf(_n("Unpublished %d item.", "Unpublished %d items.", $i), $i);
		}

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO: WILL DETERMINE BASED ON HOW MANY ITEMS WE COULD ACTION
		];

		//-------
		return $result;
	}

	private function actionLockItems() {

		$items = $this->items;
		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!
		if (empty($items)) {
			return null;
		}
		//------------------
		// good to go
		$pages = $this->getItemsToAction();
		$i = 0;
		// action each item
		foreach ($pages as $page) {
			// unlock
			if ($this->action === 'unlock') {
				$page->removeStatus(Page::statusLocked);
			} else {
				// lock
				$page->addStatus(Page::statusLocked);
			}
			//-------------
			$i++;
			//-------------
			// save the page
			$page->save();
		}

		// --------------------
		// prepare messages
		if ($this->action === 'lock') {
			$notice = sprintf(_n("Locked %d item.", "Locked %d items.", $i), $i);
		} else {
			$notice = sprintf(_n("Unlocked %d item.", "Unlocked %d items.", $i), $i);
		}

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO: WILL DETERMINE BASED ON HOW MANY ITEMS WE COULD ACTION
		];

		//-------
		return $result;
	}

	private function actionCloneItems() {
		// @NOTE: currently for products only!

		// TODO WHAT IF HAS LOTS OF VARIANTS!? CLONING WILL CRASH THE PAGE!?? or will just fail silently?
		// @see: https://processwire.com/api/ref/pages/clone/
		// TODO SHOULD WE AMEND TITLE OF CLONED ITEM? CONFIGURABLE? OR IN ACTION 'CLONE (KEEP TITLE)' & CLONE??

		$items = $this->items;
		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!
		if (empty($items)) {
			return null;
		}
		//------------------
		// good to go
		$pages = $this->getItemsToAction();
		$i = 0;
		// action each item
		foreach ($pages as $page) {
			// if not a product page; abort!
			if ($page->template->name !== PwCommerce::PRODUCT_TEMPLATE_NAME) {
				break;
			}

			// -------
			// clone
			$clone = $this->wire('pages')->clone($page);
			//-------------
			if ($clone->id) {
				$i++;
			}

		}

		// --------------------
		// prepare messages
		if (!empty($i)) {
			$notice = sprintf(_n("Cloned %d item.", "Cloned %d items.", $i), $i);
			$noticeType = 'success';
		} else {
			$notice = $this->_('Could not clone selected products!');
			$noticeType = 'error';
		}

		$result = [
			'notice' => $notice,
			'notice_type' => $noticeType, // TODO: WILL DETERMINE BASED ON HOW MANY ITEMS WE COULD ACTION
		];

		//-------
		return $result;
	}

	private function actionTrashItems() {

		$items = $this->items;
		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!
		if (empty($items)) {
			return null;
		}

		//------------------
		// good to go
		$pages = $this->getItemsToAction();
		$i = 0;
		// action each item
		// TODO: IN FUTURE RELEASE, CONFIRM DELETED OR TRASHED!
		foreach ($pages as $page) {
			// skip if page is locked
			if ($page->isLocked()) {
				continue;
			}

			//------------------------------
			// trash
			if ($this->action === 'trash') {
				$page->trash();
			} else {
				// delete
				$page->delete(true);
			}
			//-------------
			$i++;
		}

		// --------------------
		// prepare messages
		if ($this->action === 'trash') {
			$notice = sprintf(_n("Trashed %d item.", "Trashed %d items.", $i), $i);
		} else {
			$notice = sprintf(_n("Deleted %d item.", "Deleted %d items.", $i), $i);
		}

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO: WILL DETERMINE BASED ON HOW MANY ITEMS WE COULD ACTION
		];

		//-------
		return $result;
	}

}
