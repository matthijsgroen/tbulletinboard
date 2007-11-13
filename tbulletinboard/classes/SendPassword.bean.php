<?php
	/**
	 * A Database Bean
	 *
	 *@package Beans
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.0
	 */

	/**
	 *
	 */
	require_once($ivLibDir."DataObjects.class.php");

	/**
	 * Usefull for editing schedules
	 */
	class SendPasswordTable extends DataTable {

		var $privateVars;

		function SendPasswordTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "sendpassword");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("userID", "userID", false);
			$this->defineText("validation", "validation", 32, false);
			$this->defineDate("insertTime", "insertTime", false);
		}
	}

/**
ID 	bigint(20) 	UNSIGNED	No 	 	auto_increment
userID 	bigint(20) 	UNSIGNED	No 	0
insertTime 	datetime	 	No 	0000-00-00 00:00:00
validation 	char(32)	 	No
*/

?>