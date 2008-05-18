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

require_once(t3lib_extMgm::extPath('t3dev').'lib/interfaces/class.tx_t3dev_moduleInterface.php');

class tx_t3dev_calcModule implements tx_t3dev_moduleInterface {
	protected $LANG;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pObj;
	protected $moduleId = 'calcModule';
	
	public function __construct(&$pObj, &$LANG) {
		$this->pObj = $pObj;
		$this->LANG = &$LANG;
	}
	
	public function getTitle() {
		return $this->LANG->getLL($this->moduleId.'Title');
	}
	
	public function getContent() {
		$ret = $this->LANG->getLL($this->moduleId.'Description');
		$ret .= $this->pObj->doc->divider(5);
		// Time Calculator
		$data = t3lib_div::_GP('timeCalc');
		if ( !$data['format'] ) {
			$data['format'] = 'd-m-Y H:i:s';
		}
		if ( !$data['unixTime_toTime'] && !$data['unixTime_toSeconds']) {
			$data['unixTime']['seconds'] = time();
			$data['unixTime']['time'] = date($data['format'], $data['unixTime']['seconds']);
		}
		if ($data['unixTime_toTime']) {
			$data['unixTime']['time'] = date($data['format'], $data['unixTime']['seconds']);
		}
		if ($data['unixTime_toSeconds']) {
			$data['unixTime']['seconds'] = strtotime($data['unixTime']['time']);
		}
		$title = $this->LANG->getLL($this->moduleId.'TimeTitle');
		$content = $this->LANG->getLL($this->moduleId.'TimeDescription');
		$content .= '
			<input name="timeCalc[unixTime][seconds]" value="'.$data['unixTime']['seconds'].'" size="25" style="" type="text" />
			<input name="timeCalc[unixTime_toTime]" value="&gt;&gt;" type="submit" />
			<input name="timeCalc[unixTime_toSeconds]" value="&lt;&lt;" type="submit" />
			<input name="timeCalc[unixTime][time]" value="'.$data['unixTime']['time'].'" size="25" style="" type="text" />
		';
		$ret .= $this->pObj->doc->section($title, $content);
		$ret .= $this->pObj->doc->divider(5);
		
		// Crypt
		$data = t3lib_div::_GP('crypt');
		$cryptValue = ($data['input']) ? crypt($data['input']) : '';
		$title = $this->LANG->getLL($this->moduleId.'CryptTitle');
		$content = $this->LANG->getLL($this->moduleId.'CryptDescription');
		$content .= '
			<input name="crypt[input]" value="'.htmlspecialchars($cryptValue).'" size="50" style="" type="text" />
			<input name="crypt[crypt]" value="'.htmlspecialchars($this->LANG->getLL($this->moduleId.'CryptTitle')).'" type="submit" />
		';
		$ret .= $this->pObj->doc->section($title, $content);
		$ret .= $this->pObj->doc->divider(5);
		
		// MD5-Hash
		$data = t3lib_div::_GP('md5');
		$md5Hash = ($data['input']) ? md5($data['input']) : '';
		$title = $this->LANG->getLL($this->moduleId.'MD5Title');
		$content = $this->LANG->getLL($this->moduleId.'MD5Description');
		$content .= '
			<textarea name="md5[input]" cols="80" rows="5">'.htmlspecialchars($data['input']).'</textarea>
			<input name="md5[md5]" value="'.htmlspecialchars($this->LANG->getLL($this->moduleId.'MD5Title')).'" type="submit" />
			<p>'.$this->LANG->getLL('label_md5_hash').': '.$md5Hash.'</p>
		';
		$ret .= $this->pObj->doc->section($title, $content);
		$ret .= $this->pObj->doc->divider(5);
		
		return $ret;
	}
}
?>
