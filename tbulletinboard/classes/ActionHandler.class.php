<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	global $ivLibDir;
	require_once($ivLibDir."Messages.class.php");
	require_once($ivLibDir."DataArray.class.php");

	class ActionHandler {

		var $correct;
		var $feedback;
		var $actions;
		var $inAction;

		function ActionHandler(&$feedback) {
			$this->correct = true;
			$this->feedback = $feedback;
			$this->inAction = "";
		}

		function defineGetAction($name) {
			$nr = func_num_args();
			$parameters = array();
			if ($nr > 1) {
				for ($i = 1; $i < $nr; $i++) {
					$parameters[] = func_get_arg($i);
				}
			}
			$this->actions[$name] = array("type"=>"get", "name"=>$name, "parameters"=>$parameters);
		}

		function definePostAction($name) {
			$nr = func_num_args();
			$parameters = array();
			if ($nr > 1) {
				for ($i = 1; $i < $nr; $i++) {
					$parameters[] = func_get_arg($i);
				}
			}
			$this->actions[$name] = array("type"=>"post", "name"=>$name, "parameters"=>$parameters);
		}

		function check($boolean, $errMessage) {
			if (!$this->correct) return;
			if (!$boolean) {
				if (strLen(trim($errMessage)) > 0)
					$this->feedback->addMessage($errMessage);
				$this->correct = false;
				return false;
			}
			return true;
		}
		
		function inAction($name) {
			global $TBBsession;
			if (!isSet($this->actions[$name])) return false;
			$action = $this->actions[$name];

			if (($action['type'] == "get") && isSet($_GET['action']) && ($_GET['action'] == $action['name'])
				&& isSet($_GET['actionID']) && ($_GET['actionID'] == $TBBsession->getActionID())) {
				$check = true;
				$actionData = new DataItem();
				foreach($action['parameters'] as $parameter) {
					if (!isSet($_GET[$parameter])) $check = false;
					else $actionData->setProperty($parameter, $_GET[$parameter]);
				}
				$this->inAction = $name;
				return $actionData;
			}

			if (($action['type'] == "post") && isSet($_POST['action']) && ($_POST['action'] == $action['name'])) {
				$check = true;
				$actionData = new DataItem();
				foreach($action['parameters'] as $parameter) {
					if (!isSet($_POST[$parameter])) $check = false;
					else $actionData->setProperty($parameter, $_POST[$parameter]);
				}
				$this->inAction = $name;
				return $actionData;
			}
			
			return false;
		}

		function notEmpty($varName, $errMessage) {
			if (!$this->correct) return;

			if (!isSet($_GET[$varName])) {
				if (strLen(trim($errMessage)) > 0)
					$this->feedback->addMessage($errMessage);
				$this->correct = false;
				return false;
			}
			if (strLen(trim($_GET[$varName])) == 0) {
				if (strLen(trim($errMessage)) > 0)
					$this->feedback->addMessage($errMessage);
				$this->correct = false;
				return false;
			}
			return true;
		}

		function isNumeric($varName, $errMessage) {
			if (!$this->correct) return;

			if (!isSet($_GET[$varName])) {
				if (strLen(trim($errMessage)) > 0)
					$this->feedback->addMessage($errMessage);
				$this->correct = false;
				return false;
			}
			if (!is_numeric($_GET[$varName])) {
				if (strLen(trim($errMessage)) > 0)
					$this->feedback->addMessage($errMessage);
				$this->correct = false;
				return false;
			}
			return true;
		}

		function checkUpload(&$uploadObject, $message) {
			if (!$this->correct) return;
			if (!$uploadObject->checkUpload($this->feedback, $message)) {
				$this->correct = false;
				return false;
			}
			return true;
		}
		
		function actionHandled($message = "") {
			if (!isSet($this->actions[$this->inAction])) return false;
			$action = $this->actions[$this->inAction];
			if ($action['type'] == "get") {
				global $TBBsession;
				$TBBsession->actionHandled();
			}
			if ($message != "") $this->feedback->addMessage($message);
		}

		function finish($message) {
			if (!$this->correct) return;
			global $TBBsession;
			$TBBsession->actionHandled();

			if (strLen(trim($message)) > 0)
				$this->feedback->addMessage($message);
		}

	}

?>
