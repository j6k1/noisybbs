<?php
	class FileReader
	{
		function FileReader()
		{
			
		}
		
		function Read($filename)
		{
			$fp = FileReader::Open($filename);
			
			if(ErrInfo::IsErr($fp))
			{
				return $fp;
			}
			
			$result = file_get_contents($filename);
			
			/*
			if(Util::file_unlock($fp) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fileunlock_ng} File={$filename}");
			}
			*/
			
			if(@fclose($fp) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fileclose_ng} File={$filename}");
			}
			
			return $result;
		}
		
		function ReadLines($filename)
		{
			$fp = FileReader::Open($filename);
			
			if(ErrInfo::IsErr($fp))
			{
				return $fp;
			}
			
			$result = file($filename);
			
			/*
			if(Util::file_unlock($fp) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fileunlock_ng} File={$filename}");
			}
			*/
			
			if(@fclose($fp) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fileclose_ng} File={$filename}");
			}
			
			return $result;
		}

		function Open($filename)
		{
			if(file_exists("{$filename}") == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$filename}が見つかりません。");
			}
			
			$fp = @fopen($filename, "r");
			
			if($fp == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fileopen_ng} File={$filename}");
			}
			
			if(flock($fp, LOCK_SH) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$filename}は排他ロックされています。 File={$filename}");
			}
			
			return $fp;
		}
	}
?>
