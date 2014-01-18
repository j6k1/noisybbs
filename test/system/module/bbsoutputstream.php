<?php
	class BBSOutPutStream
	{
		var $buff;
		
		function BBSOutPutStream()
		{
			$this->buff = "";
		}
		
		function PrintStr($str)
		{
			$this->buff .= $str;
		}
		
		function TagPrint($name, $elements)
		{
			$this->PrintStr("<{$name}");
			
			foreach($elements as $key => $val)
			{
				$this->PrintStr(" {$key}=\"{$val}\"");
			}
			
			$this->PrintStr(" />");
		}
		
		function StartTagPrint($name, $elements)
		{
			$this->PrintStr("<{$name}");
			
			foreach($elements as $key => $val)
			{
				$this->PrintStr(" {$key}=\"{$val}\"");
			}
			
			$this->PrintStr(">");
		}
		
		function EndTagPrint($name)
		{
			$this->PrintStr("</{$name}>");
		}
		
		function Flush($mode = "stdout", $path = null, &$islocked = null, $writemode = "w", $permission = null)
		{
			$errmsg = ErrMessage::getInstance();

			if($mode == "stdout")
			{
				echo $this->buff;
			}
			else if($mode = "file")
			{
				if($path == null)
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"ファイル名が指定されていません。");
				}
				
				if($islocked === null)
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						'$islocked' . "が指定されていません。");
				}
				
				$islocked = false;				

				$filewriter = new FileWriter($path);
				
				$ret = $filewriter->FileOpen($writemode);
				
				if(ErrInfo::IsErr($ret))
				{
					$islocked = $filewriter->lockflg;
					return $ret;
				}
				
				$ret = $filewriter->WriteToFile($this->buff);
				
				if(ErrInfo::IsErr($ret))
				{
					$filewriter->FileClose();
					$islocked = $filewriter->lockflg;
					return $ret;
				}
				
				$ret = $filewriter->FileClose();
				
				if(ErrInfo::IsErr($ret))
				{
					$islocked = $filewriter->lockflg;
					return $ret;
				}
				
				if(isset($permission))
				{
					chmod($path, $permission);
				}
			}

			return true;
		}
		
		function FlushToFile($path, &$islocked, $permission = null)
		{
			return $this->Flush("file", $path, $islocked, "w", $permission);
		}

		function FlushToFileA($path, &$islocked, $permission = null)
		{
			return $this->Flush("file", $path, $islocked, "a", $permission);
		}
		
		function getBuffString()
		{
			return $this->buff;
		}
	}
?>
