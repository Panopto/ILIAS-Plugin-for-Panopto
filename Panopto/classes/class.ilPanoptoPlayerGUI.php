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

class ilPanoptoPlayerGUI
{
	/*var $player;
	var $id;
	var $path;*/
	
	function __construct($id)
	{
		global $ilCtrl, $lng;
		//$this->setPlayer($id); 
	}
	
	/**
	 * includeHeaders
	 */
	public function includeHeaders()
	{
		global $tpl;
		switch($this->getPlayer())
		{
			case 1:
				$tpl->addJavaScript("./".$this->getPath()."/player/flowplayer/flowplayer-3.2.6.min.js", 3, true);
				break;
			case 4:
				$tpl->addJavaScript("./".$this->getPath()."/player/mediaplayer/jwplayer.js", 3, true);
				break;
		}
		
	}
	
	/**
	 * setPath
	 */
	public function setPath()
	{
		$this->path = str_ireplace(str_ireplace("ilias.php", "", $_SERVER[SCRIPT_FILENAME]), "", str_ireplace("/classes/class.ilPanoptoPlayerGUI.php", "", __FILE__));
		//echo $this->path."<br>";
	}
	
	/**
	 * getPath
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	/**
	 * setPlayer
	 */
	public function setPlayer($a)
	{
		$this->player = $a;
	}
	
	/**
	 * getPlayer
	 */
	public function getPlayer()
	{
		return $this->player;
	}
	
	/**
	 * setId
	 */
	public function setData($a)
	{
		$this->data = $a;
	}
	
	/**
	 * getId
	 */
	public function getData($x)
	{
		
		return $this->data[$x];
	}
	
	/**
	 * getHTML
	 */
	public function getHTML()
	{
		$tplsc = new ilTemplate("tpl.xpan_player_".$this->getPlayer().".html", false, false, $this->getPath());
		$tplsc->setVariable("MP4", $this->getData("MP4Url"));
		$tplsc->setVariable("ID", $this->getData("Id"));
		$tplsc->setVariable("PATH", "./".$this->getPath());
		$tplsc->setVariable("VIEWERURL", $this->getData("ViewerUrl"));
		$tplsc->setVariable("THUMB", $this->getData("ThumbUrl"));

		return $tplsc->get();
	}
	
}









?>
