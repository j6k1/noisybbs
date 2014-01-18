<?php
	class ErrMsgGetter
	{
		var $msginfo;
		var $msgargs;

		function ErrMsgGetter()
		{
			$this->msginfo = array();
			$this->msgargs = null;
			
			$setting = SettingInfo::getInstance();
			$errmsglist = file($setting->errmsgfile);

			foreach($errmsglist as $line)
			{
				if(substr($line, 0, 1) == "#")
				{
					continue;
				}
				
				list($id, $head, $msg) = explode("<>", $line);
				
				$this->msginfo["{$id}"] = array("head" => $head, "msg" => $msg);
			}
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("ErrMsgGetter");
		}			

		function gethead($id)
		{
			return ErrMsgGetter::getInstance()->msginfo["{$id}"]["head"];
		}

		function getmsg($id, $msgargs = null)
		{
			$errmsggetter = ErrMsgGetter::getInstance();
			$errmsggetter->msgargs = $msgargs;
			$msg = $errmsggetter->msginfo["{$id}"]["msg"];
			
			if($errmsggetter->msgargs == null)
			{
				return $msg;
			}
			else
			{
				return ErrMsgGetter::setmsgargs_to_msg($msg);
			}
		}
		
		function setmsgargs_to_msg($msg)
		{
			return preg_replace_callback('/\{(\x5c?\$)([a-zA-Z_][a-zA-Z_\d]*)\}/', //'\\'と記述しても正常に動作しないため、\x5cと記述
				create_function('$match', 'return ErrMsgGetter::getmsgarg($match);'),
				$msg);
		}

		function getmsgarg($match)
		{
			$errmsggetter = ErrMsgGetter::getInstance();

			if($match[1] == '\$')
			{
				return $match[0];
			}
			else
			{
				if(isset($errmsggetter->msgargs[$match[2]]))
				{
					return $errmsggetter->msgargs[$match[2]];
				}
				else
				{
					return "";
				}
			}
		}
	}
?>