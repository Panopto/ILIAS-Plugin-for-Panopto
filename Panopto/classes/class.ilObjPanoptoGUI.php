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

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* User Interface class for panopto repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Fabian Schmid <fs@studer-raimann.ch>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjPanoptoGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjPanoptoGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
*
*/
class ilObjPanoptoGUI extends ilObjectPluginGUI
{
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - panopto: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
		global $tpl;
		

	}
	
	/**
	* Get type.
	*/
	final function getType()
	{
		return "xpan";
	}
	
	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "updateProperties":
			//case "...":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			
			case "showContent":			// list all commands that need read permission here
			//case "...":
			//case "...":
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "showContent";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd()
	{
		return "showContent";
	}
	
//
// DISPLAY TABS
//
	
	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;
		
		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}

		// standard epermission tab
		$this->addPermissionTab();
	}
	

// THE FOLLOWING METHODS IMPLEMENT SOME EXAMPLE COMMANDS WITH COMMON FEATURES
// YOU MAY REMOVE THEM COMPLETELY AND REPLACE THEM WITH YOUR OWN METHODS.

//
// Edit properties form
//

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	
	
	
	
	
	
	/**
	* Init object creation form
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initEditForm($a_mode = "edit", $a_new_type = "")
	{
		global $lng, $ilCtrl, $ilUser;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTarget("_top");
	
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$this->form->addItem($ta);
		
		
		
		require_once("class.PanoptoClient.php");
		$pan = new PanoptoClient();
		// userKey = ILIAS-Email
		$user_key = PanoptoClient::userKey();
		$user = $pan->ObjUserManagement->call("GetUserByKey", array("userKey" => $user_key));
		if(!$user)
		{
			$usr = array(
				"Email" => $ilUser->getEmail(),
				"EmailSessionNotifications" => false,
				"FirstName" => $ilUser->getFirstname(),
				"LastName" => $ilUser->getLastname(),
				"SystemRole" => "None",
				"UserKey" => $user_key
			);
			$pan->ObjUserManagement->call("CreateUser", array("user" =>$usr));
			$user = $pan->ObjUserManagement->call("GetUserByKey", array("userKey" => $user_key));
		}
		
		$pan2 = new PanoptoClient(2, $user_key);
		$folders = $pan2->ObjSessionManagement->call("GetFoldersList", array("request" => "", "searchQuery" => ""));	
		
		foreach($folders->Results->Folder as $folder)
		{
			$flds[$folder->Id] = $folder->Name;
		}
		
		// Radio
		$radio_prop = new ilRadioGroupInputGUI("Panopto Folder", "folder_type");
		//$radio_prop->setInfo("This is the info text of the Radio Test property.");
			$op = new ilRadioOption("Create New Panopto Folder", "1", "");
		$radio_prop->addOption($op);
			$op2 = new ilRadioOption("Select Existing Panopto Folder", "2", "");
				$cb_prop = new ilSelectInputGUI("Folder Name", "folder_id");
				$cb_prop->setOptions($flds);
			$op2->addSubItem($cb_prop);
		$radio_prop->addOption($op2);
		$radio_prop->setValue("1");
		$radio_prop->setRequired(true);
		
		if ($a_mode != "create")
		{
			$radio_prop->setDisabled(true);
		}
		
		$this->form->addItem($radio_prop);
		
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("save", $this->txt($a_new_type."_add"));
			$this->form->addCommandButton("cancelCreation", $lng->txt("cancel"));
			$this->form->setTitle($this->txt($a_new_type."_new"));
		}
		else
		{
			$this->form->addCommandButton("update", $lng->txt("save"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("edit"));
		}
	                
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	 
	}

	
	/**
	* After saving
	* @access	public
	*/
	function afterSave($newObj)
	{
		global $ilCtrl, $ilUser, $ilCtrl;
		
		$this->initEditForm();
		
		
		if ($this->form->checkInput())
		{
			require_once("class.PanoptoClient.php");
			$pan = new PanoptoClient(2, PanoptoClient::getUserKey());
			
			if($this->form->getInput("folder_type") == 1)
			{
				$new_folder = $pan->ObjSessionManagement->call("AddFolder", array("name" => $newObj->getTitle()));
				$folder_id = $new_folder->Id;
			}
			elseif($this->form->getInput("folder_type") == 2)
			{
				$folder_id = $this->form->getInput("folder_id");
			}
			$newObj->setXpanId($folder_id);
			$newObj->setOnline(1);
			$newObj->update();
		}

		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		$ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
		$ilCtrl->setTargetScript("ilias.php");
		$ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));

		$ilCtrl->setParameterByClass(get_class($this), "ref_id", $newObj->getRefId());
		$ilCtrl->redirectByClass(array("ilobjplugindispatchgui", get_class($this)), $this->getAfterCreationCmd());
	}

	
	
	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm()
	{
		global $ilCtrl, $ilUser;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$this->form->addItem($ta);
		

		// online
		/*
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$this->form->addItem($cb);
		*/

		$this->form->addCommandButton("updateProperties", $this->txt("save"));
	                
		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	
	
	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$values["online"] = 1;//$this->object->getOnline();
		$values["xp1"] = $this->object->getXpanId();
		//$values["op2"] = $this->object->getOptionTwo();
		$this->form->setValuesByArray($values);
	}
	
	/**
	* Update properties
	*/
	public function updateProperties()
	{
		global $tpl, $lng, $ilCtrl;
		
		$this->initPropertiesForm();
		
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->setXpanId($this->form->getInput("xp1"));
			//$this->object->setOptionTwo($this->form->getInput("op2"));
			$this->object->setOnline(1);//$this->form->getInput("online"));
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
//
// Show content
//
	/**
	* Show content
	*/
	function showContent()
	{
		/**
		 * Globals 
		 */
		 
		global $tpl, $ilTabs, $ilUser, $ilStyle;
		
		/**
		 * Style 
		 */
		$tpl->addJavaScript("http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js", 3, true);
		//$tpl->addJavaScript("http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js", 3, true);
		$tpl->addJavaScript($this->plugin->getStyleSheetLocation("xpan.js"), 3, true);
		$tpl->addCSS($this->plugin->getStyleSheetLocation("xpan.css"));
		$ilTabs->activateTab("content");
		
		/**
		 * Require 
		 */
		
		require_once("class.PanoptoClient.php");
		
		
		/**
		 * Panopto 
		 */
		$pan = new PanoptoClient();
		$user_key = PanoptoClient::userKey();
		$user = $pan->ObjUserManagement->call("GetUserByKey", array("userKey" => $user_key));
		if(!$user)
		{
			$usr = array(
				"Email" => $ilUser->getEmail(),
				"EmailSessionNotifications" => false,
				"FirstName" => $ilUser->getFirstname(),
				"LastName" => $ilUser->getLastname(),
				"SystemRole" => "None",
				"UserKey" => $user_key
			);
			$pan->ObjUserManagement->call("CreateUser", array("user" =>$usr));
			$user = $pan->ObjUserManagement->call("GetUserByKey", array("userKey" => $user_key));
		}

		$pan = new PanoptoClient(2, PanoptoClient::userKey());


		
		$list = $pan->ObjSessionManagement->call(
			"GetFoldersById", 
			array(
				"folderIds" => array($this->object->getXpanId())
			)
		);
		
		$sessions = $pan->ObjSessionManagement->call(
			"GetSessionsById", 
			array(
				"sessionIds" => $list->Folder->Sessions->guid 
			)
		);
		
		foreach($sessions->Session as $s)
		{
			$data[] = array(
				"Id" => $s->Id,
				"ThumbUrl" => $pan->getServer().$s->ThumbUrl,
				"Name" => $s->Name,
				"Duration" => $s->Duration,
				"MP4Url" => $s->MP4Url,
				"EditorUrl" => $s->EditorUrl,
				"StatusMessage" => $s->StatusMessage,
				"ViewerUrl" => $s->ViewerUrl
			);
		}

		include_once("class.ilPanoptoTableGUI.php");
		$table_gui = new ilPanoptoTableGUI($this, "showContent", $data, $pan->Player);	
		$tpl->setContent($table_gui->getXpanHTML());

	}
}
?>
