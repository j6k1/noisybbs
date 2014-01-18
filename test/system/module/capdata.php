<?php
	class CapData
	{
		var $name;
		var $cappass;
		var $admpass;
		var $bbslist;
		var $authority;
		
		function CapData($name, $cappass, $admpass, $bbslist, $authority)
		{
			$this->name = $name;
			$this->cappass = $cappass;
			$this->admpass = $admpass;
			$this->bbslist = $bbslist;
			$this->authority = $authority;
		}
	}
?>
