<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* Panopto repository object plugin
*
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id$
*
*/
class ilPanoptoObjectPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName()
	{
		return "PanoptoObject";
	}
	
	
}
?>
