<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Frank Nägler <typo3@naegler.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_site.'typo3/interfaces/interface.backend_toolbaritem.php');

class tx_t3dev_additionalToolbarIcons implements backend_toolbarItem {

	/**
	 * constructor that receives a back reference to the backend
	 *
	 * @param       TYPO3backend    TYPO3 backend object reference
	 */
	public function __construct(TYPO3backend &$backendReference = null) {
		$this->backennd = $backendReference;
		$this->_REL_PATH = t3lib_extMgm::extRelPath('t3dev');
	}

	/**
	 * checks whether the user has access to this toolbar item
	 *
	 * @return  boolean  true if user has access, false if not
	 */
	public function checkAccess() {
		return true;
	}

	/**
	 * renders the toolbar item
	 *
 	 * @return      string  the toolbar item rendered as HTML string
	 */
	public function render() {
		// <a class="toolbar-item" href="#"></a>
                // Render the links from the script options in
                // TYPO3_CONF_VARS
                $links=array();
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/t3dev/class.tx_t3dev_additionalToolbarIcons.php']['links'])) {
	                foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/t3dev/class.tx_t3dev_additionalToolbarIcons.php']['links'] as $linkConf)       {
	                        $aOnClick = "return top.openUrlInWindow('".$linkConf[1]."','ShowAPI');";
				if ($linkConf[1] == '#') {
                                	$links[] = '<li><span>'.htmlspecialchars($linkConf[0]).'</span></li>';
				} else {
                                	$links[] = '<li><a href="'.$linkConf[1].'" onclick="'.$aOnClick.'"><img src="'.$linkConf[2].'" width="16" height="16" title="'.$linkConf[1].'" alt="'.$linkConf[1].'" />'.htmlspecialchars($linkConf[0]).'</a></li>';
				}
                        }
                }
		$output = '<a href="#" class="toolbar-item"><img src="'.$this->_REL_PATH.'icons/ico_t3dev.gif" width="54" height="16" title="T3dev" alt="" /></a>';
		$output .= '<ul class="toolbar-item-menu" style="display: none;">';
                $output .= implode('',$links);
		$output .= '</ul>';
		return $output;
	}

	/**
	 * returns additional attributes for the list item in the toolbar
	 *
	 * @return      string          list item HTML attibutes
	 */
	public function getAdditionalAttributes() {
		return ' id="t3dev-links-menu"';
	}
}

$_REL_PATH = t3lib_extMgm::extRelPath('t3dev');
$TYPO3backend->addJavascriptFile($_REL_PATH.'tx_t3dev_menu.js');
$TYPO3backend->addCss('
	#t3dev-links-menu {
		width:60px;
	}

	#t3dev-links-menu ul {
		background-color:#F9F9F9;
		border-color:-moz-use-text-color #ABB2BC rgb(171, 178, 188);
		border-style:none solid solid;
		border-width:medium 1px 1px;
		list-style-image:none;
		list-style-position:outside;
		list-style-type:none;
		margin:0px;
		padding:2px 0px 0px;
		position:absolute;
		width:180px;
	}

	#t3dev-links-menu li {
		float:none;
		height:19px;
		padding-top:2px;
		text-align:left;
		vertical-align:middle;
	}
	
	#t3dev-links-menu li span {
		background: #ebebeb;	
		display: block;
		font-weight: bold;
		padding: 2px;
		text-align: center;
		border-right: 1px solid;
		border-bottom: 1px solid;
		border-color:-moz-use-text-color #ABB2BC rgb(171, 178, 188);
	}

	#t3dev-links-menu li a {
		display:block;
		font-size:11px;
		line-height:12px;
		padding-bottom:2px;
		padding-left:3px;
		text-decoration:none;
		vertical-align:middle;
	}

	#t3dev-links-menu li a img {
		font-size: 44px;
		vertical-align: middle;
		margin-left: 5px;
		margin-right: 5px;
	}

	#t3dev-links-menu .toolbar-item-active {
		background-image:url('.$_REL_PATH.'icons/toolbar_item_active_bg_60.png);
	}
');
$TYPO3backend->addToolbarItem('tx_t3dev_additionalToolbarIcons', 'tx_t3dev_additionalToolbarIcons');

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3dev/class.tx_t3dev_additionalToolbarIcons.php']) {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3dev/class.tx_t3dev_additionalToolbarIcons.php']);
}
?>
