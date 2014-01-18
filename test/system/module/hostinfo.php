<?php
	class HostInfo
	{
		var $ipaddr;
		var $intip;
		var $hostname;
		var $carrier;
		var $uniqno;
		var $mobileid;
		var $useragent;
		var $p2userid;
		var $p2ipaddr;
		var $p2bbm;
		
		var $ismona;
		var $is_cookie_id;
		
		function HostInfo()
		{
			$this->ipaddr = null;
			$this->intip = null;
			$this->hostname = null;
			$this->carrier = null;
			$this->uniqno = null;
			$this->modelid = null;
			$this->useragent = null;
			$this->p2userid = null;
			$this->p2ipaddr = null;
			$this->p2bbm = null;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("HostInfo");
		}			

		function Init($ip, $agent)
		{
			$this->ismona = false;
			$this->is_cookie_id = false;
			$this->ipaddr = $ip;
			
			$ipiinfo = Util::cnvip_to_int($this->ipaddr);
			$this->intip = $ipinfo["ip"];
			
			$this->hostname = gethostbyaddr($this->ipaddr);
			
			if(isset($agent))
			{
				$this->useragent = $agent;
			}
			else
			{
				$this->useragent = null;
			}
			
			if($this->hostname == null)
			{
				$this->hostname = $this->ipaddr;
			}
			
			if($this->hostname == "")
			{
				$this->hostname = $this->ipaddr;
			}
			
			$this->carrier = Util::get_carrier_id($this->hostname);
			
			if(Util::is_air_phone($this->intip, $this->useragent) == true)
			{
				$this->carrier = "H";
			}
			else if($this->carrier != null)
			{
				$mobileid = Util::getmoblieid($this->useragent, $this->carrier);
				$this->uniqno = $mobileid['uniq'];
				$this->modelid = $mobileid['model'];
			}
			
			$this->is_cookie_id = Util::use_cookie_id($this->carrier);
			
			if($this->useragent != null)
			{
				if(preg_match('/^Monazilla\//', $this->useragent))
				{
					$this->ismona = true;
				}
			}
			
			$this->p2userid = Util::getP2UserId($this->ipaddr, $this->useragent);
			$this->p2ipaddr = Util::getP2IP($this->ipaddr, $this->useragent);
			$this->p2bbm = Util::getP2BBM($this->ipaddr, $this->useragent);
		}
		
		function getidseed()
		{
			if($this->carrier === null)
			{
				if(isset($this->p2bbm))
				{
					$hostid = $this->p2bbm;
				}
				else if(isset($this->p2ipaddr))
				{
					$hostid = $this->p2ipaddr;
				}
				else
				{
					$hostid = $this->ipaddr;
				}
			}
			else
			{
				if($this->uniqno !== null)
				{
					$hostid = $this->uniqno;
				}
				else if($this->modelid !== null)
				{
					$hostid = $this->modelid;
				}
				else
				{
					$hostid = $this->ipaddr;
				}
				
				if(($this->uniqno != null) || ($this->modelid != null))
				{
					if($this->uniqno != null)
					{
						$idtype = "id";
					}
					else
					{
						$idtype = "model";
					}
					
					switch($this->carrier)
					{
						case "D" :
							$hostid = "docomo:{$idtype}={$hostid}";
							break;
						case "A" :
							$hostid = "au:{$idtype}={$hostid}";
							break;
						case "S" :
							$hostid = "softbank:{$idtype}={$hostid}";
							break;
						case "SI" :
							$hostid = "i.softbank-cookie:{$idtype}={$hostid}";
							break;
						case "E" :
							$hostid = "emobile-cookie:{$idtype}={$hostid}";
							break;
					}
				}
			}
			
			return $hostid;
		}

		function gethostid()
		{
			if($this->carrier === null)
			{
				if(isset($this->p2userid))
				{
					$hostid = "P2ID:{$this->p2userid}";
				}
				else
				{
					$hostid = $this->hostname;
				}
			}
			else
			{
				if($this->uniqno !== null)
				{
					$hostid = $this->uniqno;
				}
				else if($this->modelid !== null)
				{
					$hostid = $this->modelid;
				}
				else
				{
					$hostid = $this->ipaddr;
				}
				
				if(($this->uniqno != null) || ($this->modelid != null))
				{
					if($this->uniqno != null)
					{
						$idtype = "id";
					}
					else
					{
						$idtype = "model";
					}
					
					switch($this->carrier)
					{
						case "D" :
							$hostid = "docomo:{$idtype}={$hostid}";
							break;
						case "A" :
							$hostid = "au:{$idtype}={$hostid}";
							break;
						case "S" :
							$hostid = "softbank:{$idtype}={$hostid}";
							break;
						case "SI" :
							$hostid = "i.softbank-cookie:{$idtype}={$hostid}";
							break;
						case "E" :
							$hostid = "emobile-cookie:{$idtype}={$hostid}";
							break;
					}
				}
			}
			
			return $hostid;
		}
	}
?>