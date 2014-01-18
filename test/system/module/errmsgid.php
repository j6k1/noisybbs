<?php
	class ErrMsgID
	{
		var $overload;
		
		//type_***‚Í10i”5Œ…–Ú
		var $type_sys = 9;
		var $type_input = 1;
		var $type_regulation = 2;
		//cate_***‚Í10i”3,4Œ…–Ú
		var $cate_nml = 0;
		var $cate_deny = 1;
		var $cate_limit = 2;
		//id_***‚Í10i”1,2Œ…–Ú
		var $idlist;
		
		function ErrMsgID()
		{
			$this->overload = true;
			
			if(preg_match('/^4\./', phpversion()))
			{
				if(function_exists("overload") == false)
				{
					$this->overload = false;
				}
				else
				{
					overload(get_class($this));
				}
			}

			$this->idlist = array(
				"SYSERR" => 0,
		
				"SUBJECTSIZE" => 0,
				"NAMESIZE" => 1,
				"MAILSIZE" => 2,
				"MSGSIZE" => 3,
				"LINESIZE" => 4,
		
				"LFCOUNT" => 10,
		
				"NOSUBJECT" => 20,
				"NULLMSG" => 21,
				"NULLNAME" => 22,
				"NGWORD" => 30,
				"NOUNIQID" => 40,
				"URLFORMAT" => 90,
				"BBS_NOT_FOUND" => 91,
				"THREAD_NOT_FOUND" => 92,				
				"POSTEDLIMIT" => 0,
				"SAMBA1" => 1,
				"SAMBA2" => 2,
				"SAMBA3" => 3,
				"SAMBANOW" => 4,
				"READONLY" => 10,
				"TATESUGI" => 20,
				"THREADSTOP" => 30,
				"THREADKEY_EXISTS" => 40,
				
				"DENYHOST" => 0,
				"RESMAX" => 0,
				);
				
			$inputidkeys = array(
				"SUBJECTSIZE",
				"NAMESIZE",
				"MAILSIZE",
				"MSGSIZE",
				"LINESIZE",
		
				"LFCOUNT",
		
				"NOSUBJECT",
				"NULLMSG",
				"NULLNAME",
				"NGWORD",
				"NOUNIQID",
				"URLFORMAT",
				"BBS_NOT_FOUND",
				"THREAD_NOT_FOUND",				
				);
				
			$denyhostidkeys = array(
				"DENYHOST",
				);
				
			$sysidkeys = array(
				"SYSERR",
				);
				
			$regulationidkeys = array(
				"SAMBA1",
				"SAMBA2",
				"SAMBA3",
				"SAMBANOW",
				"POSTEDLIMIT",
				"READONLY",
				"TATESUGI",
				"THREADSTOP",
				"THREADKEY_EXISTS",
				);
			$limitedkeys = array(
				"RESMAX",
			);
			
			$this->setErrType($this->type_sys, $this->cate_nml, $sysidkeys);
			$this->setErrType($this->type_input, $this->cate_nml, $inputidkeys);
			$this->setErrType($this->type_regulation, $this->cate_nml, $regulationidkeys);
			$this->setErrType($this->type_regulation, $this->cate_deny, $denyhostidkeys);
			$this->setErrType($this->type_regulation, $this->cate_limit, $limitedkeys);
		}
		
		function setErrType($type, $cat, $idkeys)
		{
			$cnt = count($idkeys);
			for($i=0; $i < $cnt ; $i++)
			{
				$this->idlist[$idkeys[$i]] = ErrMsgID::genid($type, 
					$cat, $this->idlist[$idkeys[$i]]);
			}
		}
		
		function genid($type, $cate, $baseid)
		{
			return (($type * 10000) + ($cate * 100) + $baseid);
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("ErrMsgID");
		}			

		function &get()
		{
			return ErrMsgID::getInstance();
		}			

		function __get($name)
		{
			if(isset($this->idlist[$name]) == false)
			{
				return null;
			}
			else
			{
				return $this->idlist[$name];
			}
		}		
	}
?>
