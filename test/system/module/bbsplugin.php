<?php
	class BBSPlugin
	{
		function BBSPlugin()
		{
		
		}
		
		function ExecBeforeProc()
		{
			return true;
		}
		
		function ExecHostInfo(&$hostinfo)
		{
			return true;
		}
		
		function ExecPostData(&$postdata)
		{
			return true;
		}
		
		function ExecWriteBefore(&$writedata)
		{
			return true;
		}
		
		function ExecWriteAfter(&$writedata)
		{
			return true;
		}
		
		function ExecAfterProc()
		{
			return true;
		}
	}
?>
