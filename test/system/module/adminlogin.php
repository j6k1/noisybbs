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
				$this->OutPutErr("�t�H�[����񂪕s���ł��B");
				exit;
			}
			
			if($_POST["mode"] == "login")
			{
				if(!isset($_POST["pass"]))
				{
					$this->OutPutErr("�t�H�[����񂪕s���ł��B");
					exit;
				}
				
				if(md5($_POST["pass"]) == file_get_contents($this->passfile))
				{
					LoginInfo::getInstance()->Login();
				}
				else
				{
					$this->OutPutErr("�p�X���[�h���Ⴂ�܂��B");
					exit;
				}
			}
			else
			{
				if( (file_exists($this->passfile)) && 
					(LoginInfo::getInstance()->Init() == false) )
				{
					$this->OutPutErr("���O�C�����ĂȂ����A�Z�b�V�������^�C���A�E�g���܂����B");
					exit;
				}
				
				if( (!isset($_POST["pass1"])) || (!isset($_POST["pass2"])) )
				{
					$this->OutPutErr("�t�H�[����񂪕s���ł��B");
					exit;
				}
				
				if($_POST["pass1"] != $_POST["pass2"])
				{
					$this->OutPutErr("�p�X���[�h�Ɗm�F�p�p�X���[�h����v���܂���B");
					exit;
				}
				
				if(preg_match('/^([0-9a-zA-Z_]+)\z/', $_POST["pass1"]) == 0)
				{
					$this->OutPutErr("�p�X���[�h�ɂ́A���p�p���������́u_�v�݂̂��g�p�ł��܂��B");
					exit;
				}
				
				if(strlen($_POST["pass1"]) < 8)
				{
					$this->OutPutErr("�p�X���[�h�̒�����8�����ȏ�ɂ��Ă��������B");
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
					
					$this->OutPutErr("�p�X���[�h�t�@�C���̍X�V�ŃG���[���������܂����B");
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
				$this->OutPutErr("�L���b�v���X�g�̏������ŃG���[���������܂����B");
				exit;
			}
			
			if((!isset($_POST["mode"])) || 
				(($_POST["mode"] != "login") && ($_POST["mode"] != "setpass")) )
			{
				$this->OutPutErr("�t�H�[����񂪕s���ł��B");
				exit;
			}
			
			if($_POST["mode"] == "login")
			{
				if( (!isset($_POST["pass"])) || (!isset($_POST["cappass"])) )
				{
					$this->OutPutErr("�t�H�[����񂪕s���ł��B");
					exit;
				}
				
				$ret = CapInfo::InitCaseAdmin($_POST["cappass"], $_POST["pass"]);
				
				if($ret == true)
				{
					LoginInfo::getInstance()->Login($_POST["cappass"]);
				}
				else
				{
					$this->OutPutErr("�p�X���[�h���Ⴂ�܂��B");
					exit;
				}
			}
			else
			{
				if(LoginInfo::getInstance()->Init() == false)
				{
					$this->OutPutErr("���O�C�����ĂȂ����A�Z�b�V�������^�C���A�E�g���܂����B");
					exit;
				}
				
				if( (!isset($_POST["pass1"])) || 
					(!isset($_POST["pass2"])) || (!isset($_POST["cappass"])) )
				{
					$this->OutPutErr("�t�H�[����񂪕s���ł��B");
					exit;
				}
				
				if($_POST["pass1"] != $_POST["pass2"])
				{
					$this->OutPutErr("�p�X���[�h�Ɗm�F�p�p�X���[�h����v���܂���B");
					exit;
				}
				
				if(preg_match('/^([0-9a-zA-Z_]+)\z/', $_POST["pass1"]) == 0)
				{
					$this->OutPutErr("�p�X���[�h�ɂ́A���p�p���������́u_�v�݂̂��g�p�ł��܂��B");
					exit;
				}
				
				if(strlen($_POST["pass1"]) < 8)
				{
					$this->OutPutErr("�p�X���[�h�̒�����8�����ȏ�ɂ��Ă��������B");
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
					
					$this->OutPutErr("�p�X���[�h�t�@�C���̍X�V�ŃG���[���������܂����B");
					exit;
				}
				
				LoginInfo::getInstance()->Login($_POST["cappass"]);
			}
			
			Util::Redirect("index.php");
			
		}
	}
?>
