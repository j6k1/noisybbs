<?php
	require_once("inclueds.php");
	
	$reader = new MobileRead();
	$ret = $reader->Init();

	if(ErrInfo::IsErr($ret))
	{
		MobileRead::OutPutErrHtml($ret);
		exit;
	}
	
	$ret = $reader->Show();

	if(ErrInfo::IsErr($ret))
	{
		MobileRead::OutPutErrHtml($ret);
	}
?>
