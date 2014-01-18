<?php
	class CheckPattern
	{
		var $number = '/^\d+\z/';
		var $checked = '/^checked\z/';
		var $nullstring = '/^\z/';
		var $notnullstr = '/^.+\z/';
		var $ptn_valid  = "valid_ereg_expression";
		
		function CheckPattern()
		{
		
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("CheckPattern");
		}
		
		function &get()
		{
			return CheckPattern::getInstance();
		}
	}
?>
