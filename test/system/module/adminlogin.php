<?php
	class AdminLogin
	{
		var $passfile = "admin/adminpass.cgi";
		var $backlink;
		
		function AdminLogin($backlink = "login.php")
		{
			$this->backlink = $backlink;
		}
		
		function OutPutErr($msg)
		{
			AdminUtil::OutPutErrHtml($msg, $this->backlink);
			return;
		}
		
		function Execute()
		{
			if((!isset($_POST["mode"])) || 
				(($_POST["mode"] != "login") && ($_POST["mode"] != "setpass")) )
			{
				$this->OutPutErr("フォーム情報が不正です。");
				exit;
			}
			
			if($_POST["mode"] == "login")
			{
				if(!isset($_POST["pass"]))
				{
					$this->OutPutErr("フォーム情報が不正です。");
					exit;
				}
				
				if(md5($_POST["pass"]) == file_get_contents($this->passfile))
				{
					LoginInfo::getInstance()->Login();
				}
				else
				{
					$this->OutPutErr("パスワードが違います。");
					exit;
				}
			}
			else
			{
				if( (file_exists($this->passfile)) && 
					(LoginInfo::getInstance()->Init() == false) )
				{
					$this->OutPutErr("ログインしてないか、セッションがタイムアウトしました。");
					exit;
				}
				
				if( (!isset($_POST["pass1"])) || (!isset($_POST["pass2"])) )
				{
					$this->OutPutErr("フォーム情報が不正です。");
					exit;
				}
				
				if($_POST["pass1"] != $_POST["pass2"])
				{
					$this->OutPutErr("パスワードと確認用パスワードが一致しません。");
					exit;
				}
				
				if(preg_match('/^([0-9a-zA-Z_]+)\z/', $_POST["pass1"]) == 0)
				{
					$this->OutPutErr("パスワードには、半角英数もしくは「_」のみが使用できます。");
					exit;
				}
				
				if(strlen($_POST["pass1"]) < 8)
				{
					$this->OutPutErr("パスワードの長さは8文字以上にしてください。");
					exit;
				}
				
				if(!file_exists($this->passfile))
				{
					fclose(fopen($this->passfile, "w"));
					chmod($this->passfile, 0666);
				}
				
				$output = new BBSOutPutStream();
				
				$output->PrintStr(md5($_POST["pass1"]));
				
				$islocked = false;
				$ret = $output->FlushToFile($this->passfile, $islocked);
	
				if(ErrInfo::IsErr($ret))
				{
					if($islocked == true)
					{
						Util::file_unlock($this->passfile);
					}
					
					$this->OutPutErr("パスワードファイルの更新でエラーが発生しました。");
					exit;
				}
				
				LoginInfo::getInstance()->Login();
			}
			
			Util::Redirect("index.php");
		}
		
		function ExecuteCap()
		{
			$ret = CapList::getInstance()->Init();
			
			if(ErrInfo::IsErr($ret))
			{
				$this->OutPutErr("キャップリストの初期化でエラーが発生しました。");
				exit;
			}
			
			if((!isset($_POST["mode"])) || 
				(($_POST["mode"] != "login") && ($_POST["mode"] != "setpass")) )
			{
				$this->OutPutErr("フォーム情報が不正です。");
				exit;
			}
			
			if($_POST["mode"] == "login")
			{
				if( (!isset($_POST["pass"])) || (!isset($_POST["cappass"])) )
				{
					$this->OutPutErr("フォーム情報が不正です。");
					exit;
				}
				
				$ret = CapInfo::InitCaseAdmin($_POST["cappass"], $_POST["pass"]);
				
				if($ret == true)
				{
					LoginInfo::getInstance()->Login($_POST["cappass"]);
				}
				else
				{
					$this->OutPutErr("パスワードが違います。");
					exit;
				}
			}
			else
			{
				if(LoginInfo::getInstance()->Init() == false)
				{
					$this->OutPutErr("ログインしてないか、セッションがタイムアウトしました。");
					exit;
				}
				
				if( (!isset($_POST["pass1"])) || 
					(!isset($_POST["pass2"])) || (!isset($_POST["cappass"])) )
				{
					$this->OutPutErr("フォーム情報が不正です。");
					exit;
				}
				
				if($_POST["pass1"] != $_POST["pass2"])
				{
					$this->OutPutErr("パスワードと確認用パスワードが一致しません。");
					exit;
				}
				
				if(preg_match('/^([0-9a-zA-Z_]+)\z/', $_POST["pass1"]) == 0)
				{
					$this->OutPutErr("パスワードには、半角英数もしくは「_」のみが使用できます。");
					exit;
				}
				
				if(strlen($_POST["pass1"]) < 8)
				{
					$this->OutPutErr("パスワードの長さは8文字以上にしてください。");
					exit;
				}
				
				CapList::getInstance()->UpdatePass($_POST["cappass"], $_POST["pass1"]);
				$ret = CapList::getInstance()->Save();
				
				if(ErrInfo::IsErr($ret))
				{
					if($islocked == true)
					{
						Util::file_unlock($this->passfile);
					}
					
					$this->OutPutErr("パスワードファイルの更新でエラーが発生しました。");
					exit;
				}
				
				LoginInfo::getInstance()->Login($_POST["cappass"]);
			}
			
			Util::Redirect("index.php");
			
		}
	}
?>
