<?php
	class AdminUtil
	{
		function AdminUtil()
		{
		
		}
		
		function OutPutErrHtml($msg, $backlink)
		{
			echo <<<EOM
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<title>エラー</title>
</head>
<body>
<div>{$msg}</div>
<div><a href={$backlink}>戻る</a></div>
</body>
</html>

EOM;
			return true;
		}		
	}
?>
