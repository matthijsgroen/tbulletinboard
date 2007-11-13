<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	require_once($TBBclassDir."ModulePlugin.class.php");

	/**
	 * This is the base class for MemberModules. All implementations should
	 * extend directly from this class to let the module be detected by the
	 * system for installation.
	 * Also make sure your own module class is loaded in the PHP script.
	 * This can be done with the configuration file located in the config/ directory.
	 */
	class MemberModule extends ModulePlugin {

		var $privateVars;

		function MemberModule() {
			$this->privateVars = array();
		}

		/**
		 * A short description of the module. This description can be seen in the TBB admin.
		 */
		function getModuleDescription() {
			return "geen beschrijving";
		}

		function hasMoreAddGroupSteps($currentStep) {
			return false;
		}

		function getAddGroupForm(&$form, &$formFields, $currentStep) {
		}

		function handleAddGroupAction(&$feedback) {
			return false;
		}

		function isMemberOfGroup(&$user, $groupIDstr) {
			return false;
		}

		function getUserInfo(&$user) {
			return "dinges.";
		}

		function getUserPageData(&$user, &$table) {
		
		}

	}

?>
