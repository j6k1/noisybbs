<?php
	class FormUtilElement
	{
		var $key;
		var $val;
		var $list;
		var $chkptn;
		var $err;
		
		function FormUtilElement($key, $val, $chkptn, $list = null)
		{
			$this->key = $key;
			$this->val = $val;
			$this->list = $list;
			$this->chkptn = $chkptn;
			$this->err = false;
		}
	}
?>
