<?php
	class LoginInfo
	{
		var $aclmode;
		var $cappass;
		
		function LoginInfo()
		{
			$this->aclmode = null;
			$this->cappass = null;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("LoginInfo");
		}			
		
		function Init()
		{
			if(!isset($_SESSION["aclmode"]))
			{
				return false;
			}
			
			$this->aclmode = $_SESSION["aclmode"];
			$this->cappass = isset($_SESSION["cappass"]) ? $_SESSION["cappass"] : null;
			
			return true;
		}
		
		function Login($cappass = null)
		{
			$_SESSION = array();
			
			session_destroy();
			session_start();
			
			if(!isset($cappass))
			{
				$_SESSION["aclmode"] = "admin";
			}
			else
			{
				$_SESSION["aclmode"] = "cap";
				$_SESSION["cappass"] = $cappass;
			}
			
			return true;
		}
		
		function LogOut()
		{
			$_SESSION = array();
			
			if (isset($_COOKIE[session_name()])) {
			    setcookie(session_name(), '', 0, '/');
			}
			
			session_destroy();

			return true;
		}
	}
?>
