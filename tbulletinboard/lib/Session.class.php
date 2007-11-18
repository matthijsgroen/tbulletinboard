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

	class Session {
		var $prefix;

		function Session($path = "/", $domain = "", $lifetime=1440) {
			$this->prefix = "session_";
			session_set_cookie_params($lifetime, $path, $domain);
			//ini_set("session.cookie_path")
			//ini_set("session.cookie_lifetime", $lifetime);
			ini_set("session.gc_maxlifetime", $lifetime);
			session_start();
		}

		/**
		 * Sets a value with a unique key in the sessiondata
		 *@param string $key the key of the value
		 *@param mixed $value the value
		 */
		function setValue($key, $value) {
			$_SESSION[$this->prefix.$key] = $value;
		}



		/**
		 * Checks whether the sessiondata has a value with the specified key
		 *@param string $key the key of the value
		 *@return bool if there is a value with the specified key
		 */
		function hasValue($key) {
			return isSet($_SESSION[$this->prefix.$key]);
		}

		/**
		 * Gets a stored value from the sessiondata with the specific key
		 *@param string $key the key of the value
		 *@return mixed the value of the specified key
		 *@return bool false if the key is not present
		 */
		function getValue($key) {
			if($this->hasValue($key))
				return $_SESSION[$this->prefix.$key];
			return false;
		}

		/**
		 * Deletes a value from the sessiondata
		 *@param string $key the key of the value
		 */
		function eraseValue($key) {
			if($this->hasValue($key)) {
				unSet($_SESSION[$this->prefix.$key]);
			}
		}

		function isLoggedIn() {
			if(!$this->hasValue('userID')) return false;
			$userID = $this->getValue('userID');
			if($userID == '') return false;
			return true;
		}

		function getUserID() {
			if ($this->isLoggedIn()) {
				return $this->getValue('userID');
			}
			return false;
		}

		function setUserID($userID) {
			$this->setValue('userID', $userID);
		}

		function getActionID() {
			if(!$this->hasValue('actionID'))
				$this->actionHandled();
			return $this->getValue('actionID');
		}

		function actionHandled() {
			$this->setValue('actionID', uniqid('act'));
		}

		function setMessage($text) {
			$this->setValue('message',$text);
		}

		function getMessage() {

			if ($this->hasValue('message'))
				return $this->getValue('message');
			return false;
		}

		function eraseMessage() {
			$this->eraseValue('message');
		}
	}

?>
