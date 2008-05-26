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

class tx_t3dev_flexformField {
	protected $LANG;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pObj;
	protected $name;
	protected $config;
	
	public function __construct(&$pObj, &$LANG, $name, $config) {
		$this->pObj = $pObj;
		$this->LANG = &$LANG;
		$this->name = $name;
		$this->config = $config;
		$this->request = t3lib_div::_GP('ffgen');
	}
	
	public function init() {
		debug($this->config);
	}
	
	public function getFieldOverview() {
		$ret = '<table>';
		foreach ($this->config['TCEforms']['config'] as $k => $v) {
			$ret .= '
			<tr>
				<td>'.$k.'</td>
				<td>'.$v.'</td>			
			</tr>';
		}
		$ret .= '</table>';
		return $this->pObj->doc->section($this->name, $ret);
	}
	
}
?>
