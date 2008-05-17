<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Frank Nägler <typo3@naegler.net>
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

class tx_t3dev_basicsModule {
	protected $LANG;
	protected $pObj;
	protected $moduleId = 'basicsModule';

	public function __construct(&$pObj, &$LANG) {
		$this->pObj = $pObj;
		$this->LANG = &$LANG;
	}

	public function getTitle() {
		return $this->LANG->getLL($this->moduleId.'Title');
	}

	public function getContent() {
		$ret = $this->LANG->getLL($this->moduleId.'Description');

		return $ret;
	}

	/**
	 * Generates a selector box with the extension keys locally available for this install.
	 * copy from extdeveval extension
	 *
	 * @return    string        Selector box for selecting the local extension to work on (or error message)
	 */
	function getSelectForLocalExtensions() {
		$path = PATH_site.$this->pObj->extensionDir;
		if (@is_dir($path)) {
			$dirs = $this->extensionList = t3lib_div::get_dirs($path);
			if (is_array($dirs)) {
				sort($dirs);
				$opt=array();
				$opt[]='<option value=""> ['.$this->LANG->getLL('label_select_extension').' ]</option>';
				foreach($dirs as $dirName)        {
					$selVal = strcmp($dirName, $this->pObj->MOD_SETTINGS['extSel']) ? '' : ' selected="selected"';
					$opt[]='<option value="'.htmlspecialchars($dirName).'"'.$selVal.'>'.htmlspecialchars($dirName).'</option>';
				}
				return '<select name="SET[extSel]" onchange="jumpToUrl(\'?SET[extSel]=\'+this.options[this.selectedIndex].value,this);">'.implode('',$opt).'</select>';
			} else return 'ERROR: Could not read directories from path: "'.$path.'"';
		} else return 'ERROR: No local extensions path: "'.$path.'"';
	}

	/**
	 * Generates a selector box with file names of the currently selected extension
	 * copy from extdeveval extension
	 *
	 * @param    string        List of file extensions to select
	 * @return    string        Selectorbox or error message.
	 */
	function getSelectForExtensionFiles($extList='php,inc') {
		if ($this->pObj->MOD_SETTINGS['extSel']) {
			$path = PATH_site.$this->pObj->extensionDir.ereg_replace('\/$','',$this->pObj->MOD_SETTINGS['extSel']).'/';
			if (@is_dir($path))    {
				$phpFiles = t3lib_div::removePrefixPathFromList(t3lib_div::getAllFilesAndFoldersInPath(array(),$path,$extList,0,($this->pObj->MOD_SETTINGS['extSel']==='_TYPO3'?0:99)),$path);
				if (is_array($phpFiles))    {
					sort($phpFiles);
					$opt=array();
					$allFilesToComment=array();
					$opt[]='<option value="">[ '.$this->LANG->getLL('label_select_file').' ]</option>';
					foreach($phpFiles as $phpName)        {
						$selVal = strcmp($phpName,$this->pObj->MOD_SETTINGS['phpFile']) ? '' : ' selected="selected"';
						$opt[]='<option value="'.htmlspecialchars($phpName).'"'.$selVal.'>'.htmlspecialchars($phpName).'</option>';
						$allFilesToComment[]=htmlspecialchars($phpName);
					}
					return '<select name="SET[phpFile]" onchange="jumpToUrl(\'?SET[phpFile]=\'+this.options[this.selectedIndex].value,this);">'.implode('',$opt).'</select>'.
					chr(10).chr(10).'<!--'.chr(10).implode(chr(10),$allFilesToComment).chr(10).'-->'.chr(10);
				} else return 'No PHP files found in path: "'.$path.'"';
			} else return 'ERROR: Local extension not found: "'.$this->pObj->MOD_SETTINGS['extSel'].'"';
		}
	}

	/**
	 * Returns the currently selected PHP file name according to the selectors with field names SET[extSel] and SET[phpFile]
	 * copy from extdeveval extension
	 * 
	 * @return    mixed        String: Error message. Array: The PHP-file as first value in key "0" (zero)
	 */
	function getCurrentPHPfileName() {
		if ($this->pObj->MOD_SETTINGS['extSel'])    {
			$path = PATH_site.$this->pObj->getExtensionDir().ereg_replace('\/$','',$this->pObj->MOD_SETTINGS['extSel']).'/';
			if (@is_dir($path))    {
				if ($this->pObj->MOD_SETTINGS['phpFile'])    {
					$currentFile = $path.$this->pObj->MOD_SETTINGS['phpFile'];
					if (@is_file($currentFile))    {
						return array($currentFile);
					} else return 'Currently selected PHP file was not found: '.$this->pObj->MOD_SETTINGS['phpFile'];
				} else return 'You must select a file from the selector box above.';
			} else return 'ERROR: Local extension not found: "'.$this->pObj->MOD_SETTINGS['extSel'].'"';
		} else return 'You must select an extension from the selector box above.';
	}

	/**
	 * Returns the absolute path to the currently selected extension directory.
	 * copy from extdeveval extension
	 * 
	 * @return    string        Returns the directory IF it is also found to be a true directory. Otherwise blank.
	 */
	function getCurrentExtDir() {
		if ($this->pObj->MOD_SETTINGS['extSel']) {
			$path = PATH_site.$this->extensionDir.ereg_replace('\/$','',$this->pObj->MOD_SETTINGS['extSel']).'/';
			if (@is_dir($path)) {
				return $path;
			}
		}
	}
}
?>