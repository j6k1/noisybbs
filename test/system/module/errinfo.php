<?php
	class ErrInfo
	{
		var $usrmsgid;
		var $errhead;
		var $usrmsg;
		var $sysmsg;
		
		function ErrInfo($sysmsg, $usrmsgid = null, $msgargs = null)
		{
			if($usrmsgid == null)
			{
				$usrmsgid = ErrMsgID::get()->SYSERR;
			}
			
			$this->usrmsgid = $usrmsgid;
			$this->errhead = ErrMsgGetter::gethead($usrmsgid);
			$this->usrmsg = ErrMsgGetter::getmsg($usrmsgid, $msgargs);
			$this->sysmsg = $sysmsg;
		}
		
		function IsErr($val)
		{
			if(preg_match('/^4\./', phpversion()))
			{
				return is_a($val, "ErrInfo");
			}
			else
			{
				return ($val instanceof ErrInfo);
			}
		}
	}
?>