<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Downloads: Trait class for PWCommerce Downloads.
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



trait TraitPWCommerceDownloads
{

	private $downloadsCodesTable;
	private $isDownloadsFeatureInstalled;

	/**
	 *  init Trait P W Commerce Downloads.
	 *
	 * @return mixed
	 */
	protected function _initTraitPWCommerceDownloads() {
		// TODO NEEDED IN BACKEND AS WELL?
		$this->downloadsCodesTable = PwCommerce::PWCOMMERCE_DOWNLOAD_CODES;
		$this->isDownloadsFeatureInstalled = $this->isOptionalFeatureInstalled('downloads');
	}

	/**
	 * Fetches single download using download page ID.
	 *
	 * @param integer ID of the download page.
	 * @return WireData Object containing required download.
	 *
	 */
	// TODO DELETE WHEN DONE OR AMMEND; NOT NEEDED AS OUR DOWNLOADS ARE PAGES!
	/**
	 * Get Download By I D.
	 *
	 * @param int $id
	 * @return mixed
	 */
	public function getDownloadByID($id) {
		$download = null;
		if (empty($this->isDownloadsFeatureInstalled)) {
			// DOWNLOADS FEATURE NOT INSTALLED: ABORT
			return $download;
		}
		// ##########
		$id = (int) $id;
		// -----------
		if (!empty($id)) {
			$downloadPage = $this->wire('pages')->get("id={$id},template=" . PwCommerce::DOWNLOAD_TEMPLATE_NAME);
			if (!empty($downloadPage->id)) {
				$download = $downloadPage->get(PwCommerce::DOWNLOAD_SETTINGS_FIELD_NAME);
			}

			// ------
			return $download;
		}
	}

	/**
	 * Fetches single download from download page using a download code.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function getDownloadByCode($code) {
		if (empty($this->isDownloadsFeatureInstalled)) {
			// DOWNLOADS FEATURE NOT INSTALLED: ABORT
			return null;
		}
		// ##########
		// FIRST GET THE DOWNLOAD ID
		// this matches the download page ID.
		$sql = "SELECT download_id FROM `{$this->downloadsCodesTable}` WHERE code = :code";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":code", $code);
		$sth->execute();
		$row = $sth->fetch();
		if ($row)
			return $this->getDownloadByID($row['download_id']);
		else
			return null;
	}

	// Creates random token between 6-16 chars long with prefix if wanted
	/**
	 * Create Code.
	 *
	 * @param string $prefix
	 * @return mixed
	 */
	public function createCode($prefix = '') {
		$bits = rand(3, 8);
		return $prefix . bin2hex(openssl_random_pseudo_bytes($bits));
	}

	/**
	 * Creates and saves new download code into database. Pretty low level, so make sure the code is unique
	 *
	 * @param WireData $download
	 * @param int $orderID
	 * @param bool $code
	 * @return mixed
	 */
	public function createDownloadCode(WireData $download, $orderID = 0, bool $code = false) {

		if (empty($code))
			$code = $this->createCode($orderID . "-");

		// --------------
		$sql = "INSERT INTO `{$this->downloadsCodesTable}` SET code = :code, download_id = :download_id, order_id = :order_id, maximum_downloads = :maximum_downloads, download_expiry = FROM_UNIXTIME(:download_expiry)";
		$sth = $this->database->prepare($sql);
		// --------------
		$downloadID = (int) $download->id;
		$maximumDownloads = (int) $download->maximumDownloads;

		$sth->bindParam(":code", $code);
		// TODO - GETTING ERROR HERE ABOUT INDIRECT MODIFICATION OF ID AND MAXIMUMDOWNLOADS!
		$sth->bindParam(":order_id", $orderID);
		$sth->bindParam(":download_id", $downloadID);
		$sth->bindParam(":maximum_downloads", $maximumDownloads);
		$enddate = strtotime($download->timeToDownload);
		if (!$enddate)
			$enddate = NULL;
		$sth->bindParam(":download_expiry", $enddate);
		$sth->execute();

		return $code;
	}

	/**
	 *  download.
	 *
	 * @return mixed
	 */
	private function _download() {
		// TODO

		$code = $this->input->get->code;
		// return if empty code
		if (empty($code))
			return;
		return $this->downloadFromCode($code);
	}

	/**
	 * Sends the downloadable file to browser, based on the code. Also increments the download count in pwcommerce_download_codes table
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function downloadFromCode($code) {

		$sql = "SELECT * FROM `{$this->downloadsCodesTable}` WHERE code = :code";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":code", $code);
		$sth->execute();
		// $row = $sth->fetch();
		// TODO - OK? NEEDED?
		// $row = $sth->fetchObject('ProcessWire\WireData');
		/** @var stdClass $downloadCodes */
		$row = $sth->fetchObject();

		// If download count is over, no luck dude
		if ($row->maximum_downloads > 0 && $row->downloads >= $row->maximum_downloads)
			return false;

		// If enddate/expiry has gone, no luck dude
		$enddate = strtotime($row->download_expiry);

		if ($enddate > 0 && time() > $enddate)
			return false;

		// New download!
		$sql = "UPDATE `{$this->downloadsCodesTable}` SET downloads = downloads + 1 WHERE code = :code";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":code", $code);
		$sth->execute();

		// TODO REVISIT!? WHY EMPTY HERE AND NOT BEFORE? can we have downloads without an order id?
		$orderID = (int) $row->order_id;
		if (!empty((int) $orderID)) {
			// ---------------------------

			/** @var Page $orderPage */
			$orderPage = $this->wire('pages')->get("id={$orderID},template=" . PwCommerce::ORDER_TEMPLATE_NAME);

			if (!empty($orderPage->id)) {
				// WE GOT ORDER PAGE
				// ----
				$download = $this->getDownloadByID($row->download_id);

				$orderPage->of(false);
				// ADD NOTE TO ORDER ABOUT DOWNLOAD
				// TODO @KONGONDO AMENDMENT
				// $orderPage->addNote($this->_("Customer downloaded file") . " " . $download->title . " (" . $download->name . ")");
				$note = $this->_("Customer downloaded file") . " " . $download->title . " (" . $download->name . ")";

				$orderPage = $this->addNote($note, $orderPage);

				// SAVE THE ORDER PAGE
				$orderPage->save();
			}
		}

		// force browser to download the downloadable file
		$this->download($row->download_id);
	}

	/**
	 * Sends the downloadable file to browser, based on the download id.
	 *
	 * @param int $id
	 * @return mixed
	 */
	public function download($id) {

		$download = $this->getDownloadByID((int) $id);
		$name = $download->name;
		$filename = $download->filename;

		if (file_exists($filename)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . $name);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filename));
			@ob_clean();
			flush();
			readfile($filename);
			exit;
		}
	}
	// ---------------------------
	// TODO @KONGONDO AMENDMENT
	// /**
  * Find Downloads From Order.
  *
  * @param PWCommerceOrder $order
  * @return mixed
  */
 public function findDownloadsFromOrder(PWCommerceOrder $order) {
	/**
	 * Find Downloads From Order I D.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	public function findDownloadsFromOrderID(Page $orderPage) {
		if (empty($this->isDownloadsFeatureInstalled)) {
			// DOWNLOADS FEATURE NOT INSTALLED: ABORT
			return null;
		}
		// ##########
		$orderID = $orderPage->id;
		// TODO TEST!
		$sql = "SELECT * FROM`{$this->downloadsCodesTable}` WHERE order_id = :order_id";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":order_id", $orderID);
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_CLASS);
	}

	// ---------------------------

	/**
	 * Create download codes for downloads for a given order.
	 *
	 * @param WireData $order
	 * @param WireArray $orderLineItems
	 * @return mixed
	 */
	public function createDownloadCodesForOrder(WireData $order, WireArray $orderLineItems) {
		if (empty($this->isDownloadsFeatureInstalled)) {
			// DOWNLOADS FEATURE NOT INSTALLED: ABORT
			return null;
		}

		// TODO - SHOULD NOT DISPLAY ORDER DOWNLOADS IF INVOICE!

		if (!$order->id)
			throw new WireException("Order not found");
		$orderID = $order->id;
		$downloads = [];

		// Let's remove the old codes from this order
		$sql = "DELETE FROM `{$this->downloadsCodesTable}` WHERE order_id = :order_id";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":order_id", $orderID);
		$sth->execute();

		// ---------------------------
		foreach ($orderLineItems as $orderLineItem) {

			$productID = $orderLineItem->productID;

			$productPage = $this->wire('pages')->get("id={$productID},template=" . PwCommerce::PRODUCT_TEMPLATE_NAME . "|" . PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME);

			if (!$productPage->id)
				continue;
			// TODO NOT NEEDED, NO?
			// $productPage->of(true);

			// get the product's downloads
			/** @var PageArray $productDownloads */
			$productDownloads = $productPage->get(PwCommerce::PRODUCT_DOWNLOADS_FIELD_NAME);

			// IF VARIANT, CHECK IF PARENT PRODUCT HAS DOWNLOADS AS WELL
			$productParentDownloads = null;
			# >>>>>>>>>>>>>>>>>>>>>>>>>>
			// @note - FOR VARIANTS, WE NEED TO ADD DOWNLOADS OF MAIN PRODUCT AS WELL, IF AVAILABLE!
			# <<<<<<<<<<<<<<<<<<<<<<<<<<<<
			if ($productPage->template->name === PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME) {
				// product is a variant: get its parent's downloads
				$productParentDownloads = $productPage->parent->get(PwCommerce::PRODUCT_DOWNLOADS_FIELD_NAME);

				if (!empty($productParentDownloads->count())) {
					// variant's parent product has downloads; import them!

					$productDownloads->import($productParentDownloads);
				}
			}

			// if product has no downloads, skip
			if (empty($productDownloads->count()))
				continue;

			foreach ($productDownloads as $productDownload) {
				/** @var Pagefile $download */
				$download = $productDownload->get(PwCommerce::DOWNLOAD_SETTINGS_FIELD_NAME);

				// if no file to download, skip
				if (empty($download->filename))
					continue;
				// ----------
				// GOOD TO GO
				$code = $this->createDownloadCode($download, $orderID);

				// -----
				$downloadURL = $this->getDownloadURL($code);
				$downloads[$downloadURL] = $download->title;
			}
		}

		return $downloads;
	}

	// ---------------------------

	/**
	 * Get download codes for an order using order ID.
	 *
	 * @param int $orderID
	 * @return mixed
	 */
	public function getDownloadCodesByOrderID($orderID) {
		if (empty($this->isDownloadsFeatureInstalled)) {
			// DOWNLOADS FEATURE NOT INSTALLED: ABORT
			return [];
		}
		// ##########

		if (empty($orderID))
			throw new WireException("Invalid order");
		$sql = "SELECT download_id, code FROM `{$this->downloadsCodesTable}` WHERE order_id = :order_id";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(":order_id", $orderID);
		$sth->execute();

		/** @var stdClass $downloadCodes */
		$downloadCodes = $sth->fetchAll(\PDO::FETCH_CLASS);

		$codes = [];
		// TODO: DELETE WHEN DONE
		// $http = ($this->config->https) ? "https://" : "http://";

		foreach ($downloadCodes as $downloadCode) {

			$downloadID = $downloadCode->download_id;
			$code = $downloadCode->code;

			/** @var WireData $download */
			$download = $this->getDownloadByID($downloadID);

			// TODO: DELETE WHEN DONE
			// $codes[$http . $this->config->httpHost . $this->config->urls->root . "pwcommerce/d/?code=" . $code] = $download->title;
			$downloadURL = $this->getDownloadURL($code);
			$codes[$downloadURL] = $download->title;
		}

		return $codes;
	}

	/**
	 * Get Download U R L.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	private function getDownloadURL($code) {
		$http = ($this->config->https) ? "https://" : "http://";
		return $http . $this->config->httpHost . $this->config->urls->root . "pwcommerce/d/?code=" . $code;
	}

}