<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesAssets
{
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ASSETS ~~~~~~~~~~~~~~~~~~

	/**
	 * Get Assets U R L.
	 *
	 * @return mixed
	 */
	public function getAssetsURL() {
		// TODO: CHANGE PROCESS MODULE NAME  IN FINAL! SHOULD BE ProcessPWCommerce!!!
		// assets URL
		$assetsURL = $this->wire('config')->urls->ProcessPWCommerce . "assets/";

		return $assetsURL;
	}



	/**
	 * Get Backend Partial Template.
	 *
	 * @param mixed $file
	 * @return mixed
	 */
	public function getBackendPartialTemplate($file) {
		$t = NULL;
		// $templatePath = __DIR__ . "/templates/" . $file;
		// $templatePath = __DIR__ . "/../templates/" . $file;
		$templatePath = $this->config->paths->templates . PwCommerce::PWCOMMERCE_BACKEND_TEMPLATES_PATH . $file;

		// if (file_exists($this->config->paths->templates . "blocks/" . $file)) {
		if (file_exists($templatePath)) {
			// $templatePath = $this->blocksTemplatesPath . $file;

			$t = new TemplateFile($templatePath);
		} else {
			// TODO DELETE WHEN DONE

		}

		return $t;
		# +++++++++++

	}

}