<?php
	class Singleton
	{
		var $objs;

		function Singleton()
		{
		
		}
		
		function &getInstance($name = "Singleton")
		{
			static $objs;
			
			if(!isset($objs))
			{
				$objs = array();
				$objs["Singleton"] = new Singleton();
				$objs["Singleton"]->objs = $objs;
			}
			
			if(isset($objs[$name]))
			{
				return $objs[$name];
			}
			
			$objs[$name] = new $name();
			
			return $objs[$name];
		}
		
		function RemoveInstance($name)
		{
			$Singleton = Singleton::getInstance();
			if(!isset($Singleton->objs))
			{
				return;
			}
			
			unset($Singleton->objs[$name]);
		}
	}
?>