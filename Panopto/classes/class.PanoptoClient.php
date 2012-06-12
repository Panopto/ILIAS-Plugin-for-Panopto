<?php
/** 
* PanoptoClient 
* 
* @copyright fschmid.ch
* @author Fabian Schmid
* @version $Id$
**/


class PanoptoClient
{
	/**
	 * __construct
	 */
	function __construct($login_type = 1, $user_key = "")
	{
		foreach(parse_ini_file("./Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto/panopto.ini") as $k => $v)
		{
			$this->$k = $v;
		}
		foreach($this->APIs as $api)
		{
			$this->{"Obj".$api} = new PanoptoSoapClient($this->getWSDL($api));
			
			
			if($api == "Auth" && $login_type == 1)
			{
				$this->ObjAuth->call("LogOnWithPassword", 
					array(
						'userKey' => $this->getUserKey(),
						'password' => $this->getPassword()
					)
				);
				$_SESSION[PanCookie] = $this->ObjAuth->_cookies;
			}
			elseif($api == "Auth" && $login_type == 2)
			{
				$this->ObjAuth->call("LogOnWithExternalProvider", array($user_key => $this->authCode($user_key)));
				$_SESSION[PanCookie] = $this->ObjAuth->_cookies;
			}
		}
	}
	
	/**
	 * getAPIVersion
	 */
	public function getAPIVersion()
	{
		return $this->APIVersion;
	}
	
	/**
	 * getTimeOut
	 */
	public function getTimeOut()
	{
		return $this->TimeOut;
	}
	
	/**
	 * UserManagement
	 */
	public function UserManagement()
	{
		$wsdl = $this->getWSDL("UserManagement");
		$cl = new PanSoapClient($wsdl);
		$this->PUM = $cl;
	}
	
	/**
	 * getPassword
	 */
	public function getPassword()
	{
		return $this->Password;
	}
	
	/**
	 * getUserKey
	 */
	public function getUserKey()
	{
		return $this->UserKey;
	}
	
	/**
	 * getWDSL
	 */
	public function getWSDL($action)
	{
		return $this->getServer()."/Panopto/PublicAPI/".$this->getAPIVersion()."/".$action.".svc?wsdl";
	}
	
	/**
	 *  getServer
	 */
	public function getServer()
	{
		return $this->Server;
	}
	
	/**
	 * getApplicationKey
	 */
	public function getApplicationKey()
	{
		return $this->ApplicationKey;
	}
	
	/**
	 * getInstanceName
	 */
	public function getInstanceName()
	{
		return $this->InstanceName;
	}
	
	/**
	 *  authCode
	 */
	public function authCode($userkey)
	{
		$payload = $this->getInstanceName()."".$userkey."@".$this->getServer()."|".$this->getApplicationKey();
		return strtoupper(sha1($payload));
	}
	
	/**
	 * userKey
	 */
	public function userKey()
	{
		global $ilUser;
		$user_key = $_COOKIE[ilClientId]."$".$ilUser->getEmail()."$".$ilUser->getId();
		//echo $user_key;
		return $user_key;
	}

}


/** 
* PanSoapClient 
* 
* @copyright fschmid.ch
* @author Fabian Schmid
* @version $Id$
**/


class PanoptoSoapClient extends SoapClient
{
	function __construct($wsdl)
	{
		try
		{	
			parent::__construct($wsdl, array(
				"trace" => true,
				"exeption" => true,
				//"soap_version" => "SOAP_1_1",
				//"cache_wsdl" => WSDL_CACHE_NONE,
				//"connection_timeout" => 120
			));
			$this->__setLocation($wsdl);
			foreach($_SESSION[PanCookie] as $k => $v)
			{
				$this->__setCookie($k, $v[0]);
			}
			
		}
		catch(Exception $f)			
		{
			$this->msg = $f->getMessage()."!";
		}
		return $this;
	}
	
	/**
	 * call
	 */
	public function call($f, $p = array())
	{
		try
		{
			$res = call_user_func(array($this, $f), $p);
			return $res->{$f."Result"};
		}
		catch(SoapFault $xpanf)
		{
        	echo $xpanf->faultstring;
        	return false;
		}
	}
	
	/**
	 * logout
	 */
	public function logout()
	{
		$this->__setCookie($k, $v[0]);
	}
}

?>