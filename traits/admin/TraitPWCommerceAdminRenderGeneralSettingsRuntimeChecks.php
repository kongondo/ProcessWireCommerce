<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsRuntimeChecks
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ RUNTIME CHECKS TAB  ~~~~~~~~~~~~~~~~~~

	private function isExistAddonsPage() {
		$pageID = (int) $this->pwcommerce->getRaw("template=" . PwCommerce::PWCOMMERCE_TEMPLATE_NAME . ",name=addons", 'id');
		return !empty($pageID);
	}


}
