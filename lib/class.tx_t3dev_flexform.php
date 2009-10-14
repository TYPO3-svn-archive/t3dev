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
	/**
	 * @var tx_t3dev_flexformModule
	 */
	protected $pObj;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pMod;
	protected $extkey;
	protected $filename;
	protected $flexform;
	protected $flexformArray;
	protected $request;
	protected $error = '';

	public function init(&$pObj, $extkey) {
		$this->pObj = $pObj;
		$this->pMod = $pObj->getPObj();
		$this->extkey = $extkey;
		$this->request = t3lib_div::_GP('ffgen');
		debug($this->request, '$this->request', '', '', 10);

		$this->flexform = t3lib_div::getURL($this->filename);
		$this->flexformArray = t3lib_div::xml2array($this->flexform, 'T3DataStructure');
		$this->flexformCorrection();
		debug($this->flexformArray, 'FlexfromArray', '', '', 10);
		
		if (strlen($this->request['newSheet'])) {
			$this->createNewSheet($this->request['newSheet']);
		}

		if (strlen($this->request['newField'])) {
			$this->createNewField($this->request['newField']);
		}

		if (strlen($this->request['update'])) {
			$this->updateFields($this->request['sheet'], $this->request['sheetData'][$this->request['sheet']]);
		}
	}
	
	public function getPMod() {
		return $this->pMod;
	}
	
	public function getContent() {
		$content .= $this->getSheetSelector();
		$content .= $this->getNewSheetField();
		$content .= $this->getSubmitButton();
		$content .= $this->pMod->doc->divider(5);
		$content .= $this->getNewFieldSelector();
		$content .= $this->pMod->doc->divider(5);
		$content .= $this->getFieldsForCurrentSheet();
		
		if(trim($this->error) != '') {
			$content = $this->pMod->doc->rfw($this->error);
		}
		
		return $this->pMod->doc->section($GLOBALS['LANG']->getLL('label_ffgen'), $content);
	}

	/**
	 * return current filename
	 *
	 * @return	void
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
	}
	
	/**
	 * This function adds the missing sheets-array "sheets->sDEF" if
	 * there is no sheet defined. This is possible if the user writes
	 * a flexform with only one sheet 
	 *
	 * @return	void
	 */
	protected function flexformCorrection() {
		if(!is_array($this->flexformArray['sheets'])) {
			if(!is_array($this->flexformArray['ROOT'])) {
				$this->error = $GLOBALS['LANG']->getLL('err_no_valid_xml_file');
				return false;
			}
			$this->flexformSheet = $this->flexformArray['ROOT'];
			//If there is no sheet title...set one
			if(!is_array($this->flexformSheet['TCEforms'])) {
				$sheetTitle = array(
					'TCEforms' => array(
						'sheetTitle' => 'LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.sheet_sDEF'
					)
				);
				$this->flexformSheet = t3lib_div::array_merge(
					$this->flexformSheet, $sheetTitle
				);
			}

			$this->flexformArray['sheets'] = array(
				'sDEF' => array(
					'ROOT' => $this->flexformSheet
				)
			);
		}
		//unset the original Root-Array
		unset($this->flexformArray['ROOT']);
		$this->save();
	}
	
	protected function createNewSheet($sheet) {
		$sheet = trim($sheet);
		$this->flexformArray['sheets'][$sheet] = array(
			'ROOT' => array(
				'TCEforms' => array(
					'sheetTitle' => 'LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.sheet_'.$sheet
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
		$newField = new tx_t3dev_flexformField($this->pObj, '', $this->extkey);
		$newField->setType($field);
		if (!is_array($this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'])) {
			$this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'] = array();
		}
		$this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'][$newField->getName()] = $newField->asArray();
		$this->save();
	}
	
	protected function updateFields($sheet, $data) {
		foreach ($data as $k => $v) {
			debug($data[$k]['TCEforms']['config'], 'updateFields');
			$name = $data[$k]['TCEforms']['config']['name'];
			unset($data[$k]['TCEforms']['config']['name']);
			$newField = new tx_t3dev_flexformField($this->pObj, $name, $this->extkey, $data[$k]);
			unset($this->flexformArray['sheets'][$sheet]['ROOT']['el'][$k]);
			$this->flexformArray['sheets'][$sheet]['ROOT']['el'][$newField->getName()] = $newField->asArray();
		}
		$this->save();
	}
	
	/**
	 * This function converts our Array back to XML
	 * Spaces are indented with TAB, so everybody can set spaces in
	 * his programm on their own.
	 *
	 * @return	void
	 */
	protected function save() {
		$content = t3lib_div::array2xml($this->flexformArray, '', 0, 'T3DataStructure');
		if(!t3lib_div::writeFile($this->filename, $content)) {
			$this->error = $GLOBALS['LANG']->getLL('err_file_not_written');
		}
	}
	
	protected function getSheetSelector() {
		if (!$this->pObj->getFromSession('sheet')) {
			$this->pObj->setToSession('sheet', 'sDEF');
		}
		if(is_array($this->flexformArray['sheets'])) {
			$sheet = 'sheets';
		} elseif(is_array($this->flexformArray['ROOT'])) {
			$sheet = 'ROOT';
		} else {
			$this->error = $GLOBALS['LANG']->getLL('err_no_valid_xml_file');
			return false;
		} 

		$content = '<select name="ffgen[sheet]" onchange="jumpToUrl(\'?ffgen[sheet]=\'+this.options[this.selectedIndex].value,this);">';
		if($sheet == 'ROOT') {
			$content .= '<option value="Default" selected="selected">Default</option>';
		} else {
			foreach ($this->flexformArray[$sheet] as $k => $v) {
				$sel = '';
				if (!strlen($this->request['sheet'])) {
					$this->request['sheet'] = $this->pObj->getFromSession('sheet');
				}
				if (!strlen($this->request['sheet'])) {
					$this->request['sheet'] = $k;
				}
	
				if ($this->request['sheet'] == $k) {
					$sel = ' selected="selected"';
					$this->pObj->setToSession('sheet', $this->request['sheet']);
				}
				$content .= '<option value="'.$k.'"'.$sel.'>'.$k.'</option>';
			}
		}

		$content .= '</select>';
		return $this->pMod->doc->funcMenu($GLOBALS['LANG']->getLL('label_sheets'), $content);
	}
	
	protected function getNewSheetField() {
		$ret .= '<input type="text" name="ffgen[newSheet]" value="" />';
		return $this->pMod->doc->funcMenu($GLOBALS['LANG']->getLL('label_new_sheet'), $ret);
	}
		
	protected function getNewFieldSelector() {
		$ret .= '<select name="ffgen[newField]" onchange="jumpToUrl(\'?ffgen[newField]=\'+this.options[this.selectedIndex].value,this);">';
		$ret .= '<option value=""></option>';
		$dummyField = new tx_t3dev_flexformField($this->pObj, '', $this->extkey);
		$fieldTypes = $dummyField->getFieldsConfig();
		foreach ($fieldTypes as $k => $v) {
			$ret .= '<option value="'.$k.'">'.$GLOBALS['LANG']->getLL('label_flexform_'.$k).'</option>';
		}
		$ret .= '</select>';
		return $this->pMod->doc->funcMenu($GLOBALS['LANG']->getLL('label_new_field'), $ret);
	}
	
	protected function getFieldsForCurrentSheet() {
		if(is_array($this->flexformArray['sheets'])) {
			$sheet = 'sheets';
			$currentFields = $this->flexformArray[$sheet][$this->pObj->getFromSession('sheet')]['ROOT']['el'];
		} elseif(is_array($this->flexformArray['ROOT'])) {
			$sheet = 'ROOT';
			$currentFields = $this->flexformArray[$sheet]['el'];
		} else {
			$this->error = $GLOBALS['LANG']->getLL('err_no_valid_xml_file');
			return false;
		}
		
		if (is_array($currentFields) && (count($currentFields) > 0)) {
			//$flexformFieldClassname = t3lib_div::makeInstanceClassName('tx_t3dev_flexformField');
			foreach ($currentFields as $k => $v) {
				debug($currentFields, 'getFieldsForCurrentSheet');
				$flexformField = new tx_t3dev_flexformField($this->pObj, $k, $this->extkey, $currentFields[$k]);
				$flexformField->init();
				$ret .= $flexformField->getFieldOverview();
				$ret .= $this->getUpdateButton();
				$ret .= $this->pMod->doc->divider(2);
			}
			return $ret;
		} else {
			return 'no fields';
		}
	}
	
	protected function getSubmitButton() {
		$ret .= '<input type="submit" name="ffgen[submit]" value="'.$GLOBALS['LANG']->getLL('label_submit').'" />';
		return $this->pMod->doc->funcMenu('', $ret);
	}
	
	protected function getUpdateButton() {
		$ret .= '<input type="submit" name="ffgen[update]" value="'.$GLOBALS['LANG']->getLL('label_update').'" />';
		return $this->pMod->doc->funcMenu('', $ret);
	}
}
?>