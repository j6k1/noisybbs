<?php
	class FileWriter
	{
		var $fp;
		var $filename;
		var $writemode;
		var $lockflg;
		var $istruncate;
		
		function FileWriter($fname)
		{
			$this->fp = null;
			$this->lockflg = false;
			$this->istruncate = false;
			$this->writemode = null;
			$this->filename = $fname;
		}
		
		function FileOpen($mode = "w")
		{
			$errmsg = ErrMessage::getInstance();
			
			if($this->filename == null)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル名が指定されていません。");
			}
			
			if(file_exists("{$this->filename}") == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$this->filename}が見つかりません。");
			}
			
			if($mode == "w+")
			{
				//fopenのモード"r+"でファイルを開いたケースで、ftruncateによるファイル初期化時、
				//初期化前にファイルポインターから読みだした内容が消失するため、未対応とする。
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fopenmode_notsupport} File={$this->filename} mode={$mode}");
			}
			
			if($mode == "w")
			{
				//fopenのモード"w"を指定するとその時点でデータが初期化されるため、"r+"で開いて書き込み前にftruncate
				$mode = "r+";
				$this->istruncate = true;
			}
			
			$this->writemode = $mode;
			
			$this->fp = @fopen($this->filename, $this->writemode);
			
			if($this->fp === false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fileopen_ng} File={$this->filename} mode={$this->writemode}");
			}
			
			if(@Util::file_lock($this->fp) === false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->filelock_ng} File={$this->filename} mode={$this->writemode}");
			}
			
			$this->lockflg = true;
			
			return true;
		}
		
		function WriteToFile($data)
		{
			$errmsg = ErrMessage::getInstance();

			if($this->fp === null)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->not_openedfile} File={$this->filename}");
			}
			
			if($this->istruncate)
			{
				if(!@ftruncate($this->fp, 0))
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"{$errmsg->filetruncate_ng} File={$this->filename}");
				}
			}

			if(@fwrite($this->fp, $data) === false)
			{	
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fwrite_ng} File={$this->filename} mode={$this->writemode}");
			}
		}
		
		function FileClose($permission = null)
		{
			$errarray = array();
			
			if($this->fp === null)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->not_openedfile} File={$this->filename}");
			}
			
			if(@fclose($this->fp) === false)
			{
				array_push($errarray, "{$errmsg->fileclose_ng}");

				$errcnt = count($errarray);
				$msg = "";
				
				if($errcnt == 1)
				{
					$msg .= "{$errarray[0]} File={$this->filename}";
				}
				else
				{
					for($i=0; $i < $errcnt ; $i++)
					{
						$msg .= "err {$i} : {$errarray[$i]} ";
					}
					
					$msg .= "File={$this->filename}";
				}
				
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$msg);
			}			
			
			$this->lockflg = false;
			$this->fp = null;
			
			if($permission !== null)
			{
				if(@chmod($this->filename, $permission) === false)
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"{$errmsg->filechmod_ng} File={$this->filename} permission={$permission}");
				}
			}
			
			return true;
		}
	}
?>
