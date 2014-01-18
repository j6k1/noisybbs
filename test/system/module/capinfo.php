<?php
	class CapInfo
	{
		var $capdata;
		
		function CapInfo()
		{
			$this->capdata = null;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("CapInfo");
		}
		
		function InitCaseAdmin($cappass, $admpass = null)
		{
			$capdatas = CapList::getInstance();

			if(!isset($capdatas->data[$cappass]))
			{
				return false;
			}
			
			if( (isset($admpass)) && 
				($capdatas->data[$cappass]->admpass != md5($admpass)) )
			{
				return false;
			}
			
			$this->capdata = $capdatas->data[$cappass];
			
			return true;
		}
		
		function hasBBSAuthority($key)
		{
			if(!isset(CapInfo::getInstance()->capdata))
			{
				return false;
			}
			
			$capdata = CapInfo::getInstance()->capdata;
			
			return in_array($key, $capdata->bbslist);
		}
		
		function Init(&$mail, $bbs)
		{
			$find = false;
			
			if(preg_match('/#([0-9a-zA-Z_-]+)/', $mail, $match))
			{
				$find = true;
			}
			
			$mail = preg_replace('/#.*$/', '', $mail);
			
			if($find == false)
			{
				return true;
			}
			
			$capdatas = CapList::getInstance();
			$cappass = $match[1];
			
			if(!isset($capdatas->data[$cappass]))
			{
				return true;
			}
			
			if(!in_array($bbs, $capdatas->data[$cappass]->bbslist))
			{
				return true;
			}
			
			$this->capdata = $capdatas->data[$cappass];
			
			return true;
		}
		
		function getName()
		{
			if(!isset($this->capdata))
			{
				return false;
			}
			
			return $this->capdata->name;
		}
		
		function getAuthoritys()
		{
			if(!isset($this->capdata))
			{
				return false;
			}
			
			return $this->capdata->authority;
		}
		
		function hasAuthority($key)
		{
			if(!isset(CapInfo::getInstance()->capdata))
			{
				return false;
			}
			
			$capdata = CapInfo::getInstance()->capdata;
			return in_array($key, $capdata->authority);
		}
		
		function getHasBBSList()
		{
			$capdata = CapInfo::getInstance()->capdata;
			return $capdata->bbslist;
		}
	}
?>
