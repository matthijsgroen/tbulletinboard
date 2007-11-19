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

	/**
	 * User is a class to create an user,
	 * information can be stored and retrieved and a user can be logged in and logged out
	 */
	class User {

		/**
		 * Private vars for this object
		 *@var Array $privateVars
		 */
		var $privateVars;

		/**
		 * Instantiates a Database object
		 *@param string $password the password of the user
		 */
		function User() {
			$this->privateVars = array();
		}

		/**
		 * Sign the user in
		 *@return int statusnumber 0 = OK, 1 = username/password combination was incorrect, 2 other error
		 */
		function login($username, $password) {
		}

		/**
		 * Sign the user out
		 *@return int statusnumber 0 = OK, 1 = username/password combination was incorrect, 2 other error
		 */
		function logout() {
		}

		/**
		 * Sets a value with a unique key in the userdata
		 *@param string $key the key of the value
		 *@param object $value the value
		 */
		function setValue($key, $value) {
			$this->privateVars[$key] = $value;
		}

		/**
		 * Checks whether the userdata has a value with the specified key
		 *@param string $key the key of the value
		 *@return bool if there is a value with the specified key
		 */
		function hasValue($key) {
			return isSet($this->privateVars[$key]);
		}

		/**
		 * Gets a stored value from the userdata with the specific key
		 *@param string $key the key of the value
		 *@return mixed the value of the specified key
		 *@return bool false if the key is not present
		 */
		function getValue($key) {
			if($this->hasValue($key))
				return $this->privateVars[$key];
			return false;
		}

		/**
		 * Deletes a value from the userdata
		 *@param string $key the key of the value
		 */
		function eraseValue($key) {
			if(isSet($this->privateVars[$key])) {
				unSet($this->privateVars[$key]);
			}
		}
	}
?>
