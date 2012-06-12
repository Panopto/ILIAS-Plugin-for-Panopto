<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once "./Services/Table/classes/class.ilTable2GUI.php";

/**
* TableGUI implementation for Panopto object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* PLEASE do not create instances of larger classes here. Use the
* ...Access class to get DB data and keep it small.
*
* @author 		Fabian Schmid <fs@studer-raimann.ch>
*/

class ilPanoptoTableGUI extends ilTable2GUI
{
	var $base;
	
	function __construct($a_parent_obj, $a_parent_cmd, $a_data, $p = 1)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		include_once("class.ilPanoptoPlayerGUI.php");
		$this->data = $a_data;
		$this->base = "Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto";
		
		$this->setData($a_data);
		
		$this->addColumn("Thumbnail", "", "150px", "");
		$this->addColumn("Title", "title", "", "");
		$this->addColumn("Duration", "duration", "", "");
		$this->addColumn("Status-Message", "statusmessage", "", "");
		$this->addColumn("", "", "", "");

		$this->setEnableHeader(true);
		$this->setDefaultOrderField("statusmessage");
		$this->setDefaultOrderDirection("desc");
		
		
		//$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.xpan_row_template.html", $this->base);
		
		$this->setTitle("Panopto Sessions");
		
		$this->tplr = new ilPanoptoPlayerGUI();
		$this->tplr->setPath();
		$this->tplr->setPlayer($p);
		$this->tplr->includeHeaders();
	}
		
	/**
	* Fill a single data row.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $plr;
		
		$this->tpl->setVariable("TXT_TITLE", $a_set["Name"]);
		$this->tpl->setVariable("TXT_ID", $a_set["Id"]);
		$this->tpl->setVariable("THUMB", $a_set["ThumbUrl"]);
		$this->tpl->setVariable("TXT_DURATION", $a_set["Duration"]." s");
		$this->tpl->setVariable("DURATION", $a_set["Duration"]);
		$this->tpl->setVariable("TXT_STATUSMESSAGE", $a_set["StatusMessage"]);
		if($a_set["StatusMessage"] == "")
			$this->tpl->setVariable("CLICKABLE", "xpan_click");
		
		$this->tplr->setData($a_set);
		
		$this->tpl->setVariable("PLAYER", $this->tplr->getHTML());
	}
	
	
	public function getXpanHTML()
	{
		$tplsc = new ilTemplate("tpl.xpan_script.html", false, false, $this->base);
		$script = $tplsc->get();
		//$tplsc->includeCSS('xpan.css');
		$parent = parent::getHTML();
		return $script.$parent;
	}
	
	
}


?>
