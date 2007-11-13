<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	require_once($ivLibDir . 'Session.class.php');
	require_once($ivLibDir . 'LibDateTime.class.php');
	require_once($TBBclassDir . 'User.class.php');
	require_once($TBBclassDir . 'User.bean.php');

	class TBBSession extends Session {

		function TBBSession($path = "/", $domain = "", $lifetime=86400) {
			$this->Session($path, $domain, $lifetime);

			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$GLOBALS['TBBcurrentUser'] = new User();
			global $TBBcurrentUser;
			if ($this->isLoggedIn()) {
				$TBBcurrentUser->setUserID($this->getUserID());
				if ($TBBcurrentUser->getLastSession() == session_id()) {

					$userTable = new UserTable($database);
					$user = $userTable->getRowByKey($this->getUserID());
					$user->setValue("lastSeen", new LibDateTime());
					$user->store();
					/*
					$updateQuery = "UPDATE ".$TBBconfiguration->tablePrefix."users SET last_seen = NOW() WHERE ID='".addSlashes($this->getUserID())."'";
					$database->executeQuery($updateQuery);
					*/
				} else {
					$_SESSION['userID'] = '';
					global $feedback;
					$feedback->addMessage('Je bent op een andere lokatie aangemeld!');
					$GLOBALS['TBBcurrentUser'] = new User(); // Reset..
				}
			}
			if (!$this->hasValue("tbbSessID")) {
				$this->setValue("tbbSessID", uniqId("tbbS"));
			}
		}

	}

?>
