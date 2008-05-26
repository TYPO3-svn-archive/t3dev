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

require_once(t3lib_extMgm::extPath('t3dev').'lib/class.tx_t3dev_flexformField.php');

class tx_t3dev_flexform {
	protected $LANG;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pObj;
	protected $extkey;
	protected $filename;
	protected $flexform;
	protected $flexformArray;
	protected $request;
	protected $fieldTypes = array(
		'input' => 'Input',
		'db' => 'DB Relation',
	);
	
	public function __construct(&$pObj, &$LANG, $extkey) {
		$this->pObj = $pObj;
		$this->LANG = &$LANG;
		$this->extkey = $extkey;
		$this->request = t3lib_div::_GP('ffgen');
	}
	
	public function init() {
		$this->flexform = t3lib_div::getURL($this->filename);
		$this->flexformArray = t3lib_div::xml2array($this->flexform, 'T3DataStructure');
		debug($this->flexformArray, 'FlexfromArray', '', '', 10);
		
		if (strlen($this->request['newSheet'])) {
			$this->createNewSheet($this->request['newSheet']);
		}

		if (strlen($this->request['newField'])) {
			$this->createNewField($this->request['newField']);
		}
	}
	
	public function getContent() {
		$ret .= $this->getSheetSelector();
		$ret .= $this->getNewSheetField();
		$ret .= $this->getSubmitButton();
		$ret .= $this->pObj->doc->divider(5);
		$ret .= $this->getNewFieldSelector();
		$ret .= $this->getFieldsForCurrentSheet();
		$ret .= $this->pObj->doc->divider(5);
		
		return $this->pObj->doc->section($this->LANG->getLL('label_ffgen'), $ret);
	}

	public function setFilename($filename) {
		$this->filename = $filename;
	}
	
	protected function createNewSheet($sheet) {
		$newSheet = trim($sheet);
		$this->flexformArray['sheets'][$newSheet] = array(
			'ROOT' => array(
				'TCEforms' => array(
					'sheetTitle' => 'LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.sheet_'.$newSheet
				),
				'type' => 'array',
				'el' => array()
			)
		);
		$this->save();
		$this->flexform = t3lib_div::getURL($this->filename);
		$this->flexformArray = t3lib_div::xml2array($this->flexform, 'T3DataStructure');
	}
	
	protected function createNewField($field) {
		$newField = trim($field);
/*		$this->flexformArray['sheets'][$newSheet] = array(
			'ROOT' => array(
				'TCEforms' => array(
					'sheetTitle' => 'LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.sheet_'.$newSheet
				),
				'type' => 'array',
				'el' => array()
			)
		);
*/
		$this->save();
		$this->flexform = t3lib_div::getURL($this->filename);
		$this->flexformArray = t3lib_div::xml2array($this->flexform, 'T3DataStructure', true);
	}
	
	protected function save() {
		$content = t3lib_div::array2xml($this->flexformArray, '', 0, 'T3DataStructure', 1);
		t3lib_div::writeFile($this->filename, $content);
	}
	
	protected function getSheetSelector() {
		$ret .= '<select name="ffgen[sheet]" onchange="jumpToUrl(\'?ffgen[sheet]=\'+this.options[this.selectedIndex].value,this);">';
		foreach ($this->flexformArray['sheets'] as $k => $v) {
			$sel = '';
			if ($this->request['sheet'] == $k) {
				$sel = ' selected="selected"';
			}
			$ret .= '<option value="'.$k.'"'.$sel.'>'.$k.'</option>';
		}
		$ret .= '</select>';
		return $this->pObj->doc->funcMenu($this->LANG->getLL('label_sheets'), $ret);
	}
	
	protected function getNewSheetField() {
		$ret .= '<input type="text" name="ffgen[newSheet]" value="" />';
		return $this->pObj->doc->funcMenu($this->LANG->getLL('label_new_sheet'), $ret);
	}
		
	protected function getNewFieldSelector() {
		$ret .= '<select name="ffgen[newField]" onchange="jumpToUrl(\'?ffgen[newField]=\'+this.options[this.selectedIndex].value,this);">';
		$ret .= '<option value=""></option>';
		foreach ($this->fieldTypes as $k => $v) {
			$ret .= '<option value="'.$k.'">'.$this->fieldTypes[$k].'</option>';
		}
		$ret .= '</select>';
		return $this->pObj->doc->funcMenu($this->LANG->getLL('label_new_field'), $ret);
	}
	
	protected function getFieldsForCurrentSheet() {
		$currentFields = $this->flexformArray['sheets'][$this->request['sheet']]['ROOT']['el'];
		if (is_array($currentFields) && (count($currentFields) > 0)) {
			//$flexformFieldClassname = t3lib_div::makeInstanceClassName('tx_t3dev_flexformField');
			foreach ($currentFields as $k => $v) {
				$flexformField = new tx_t3dev_flexformField($this->pObj, $this->LANG, $k, $currentFields[$k]);
				$flexformField->init();
				$ret .= $flexformField->getFieldOverview();
			}
			return $ret;
		} else {
			return 'no fields';
		}
	}
	
	protected function getSubmitButton() {
		$ret .= '<input type="submit" name="ffgen[submit]" value="'.$this->LANG->getLL('label_submit').'" />';
		return $this->pObj->doc->funcMenu('', $ret);
	}
}
?>
