<?php
	/**
	 *	TBB2, an highly configurable and dynamic bulletin board
	 *	Copyright (C) 2007  Matthijs Groen
	 *
	 *	This program is free software: you can redistribute it and/or modify
	 *	it under the terms of the GNU General Public License as published by
	 *	the Free Software Foundation, either version 3 of the License, or
	 *	(at your option) any later version.
	 *	
	 *	This program is distributed in the hope that it will be useful,
	 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *	GNU General Public License for more details.
	 *	
	 *	You should have received a copy of the GNU General Public License
	 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *	
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
