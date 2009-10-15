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
	protected $message = '';

	public function init(&$pObj, $extkey) {
		$this->pObj = $pObj;
		$this->pMod = $pObj->getPObj();
		$this->extkey = $extkey;
		$this->request = t3lib_div::_GP('ffgen');
		debug($this->request, '$this->request', '', '', 10);

		$this->flexform = t3lib_div::getURL($this->filename);
		$this->flexformArray = t3lib_div::xml2array($this->flexform, 'T3DataStructure');
		$this->flexformCorrection();
		$this->setTypeT3dev();
		debug($this->flexformArray, 'FlexfromArray', '', '', 10);
		
		if (strlen($this->request['newSheet'])) {
			$this->createNewSheet($this->request['newSheet']);
		}

		if($this->request['delSheet'] == 'del') {
			if($this->request['del']) {
				$this->deleteSheet($this->pObj->getFromSession('sheet'), 1);
			} else {
				$this->deleteSheet($this->pObj->getFromSession('sheet'));
			}
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
		$content .= $this->getDelSheetField();
		$content .= $this->pMod->doc->divider(5);
		$content .= $this->getNewFieldSelector();
		$content .= $this->pMod->doc->divider(5);
		$content .= $this->getFieldsForCurrentSheet();
		
		if(trim($this->error) != '') {
			$content = $this->pMod->doc->rfw($this->error);
		}
		if(trim($this->message) != '') {
			$content = $this->message;
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
	
	/**
	 * If there is allready a flexform, than there is no type_t3dev-array
	 * This function will help to find out the missing array
	 *
	 * @return	void
	 */
	protected function setTypeT3dev() {
		$currentFields = $this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'];
		if (is_array($currentFields) && (count($currentFields) > 0)) {
			foreach ($currentFields as $key => $value) {
				if($value['TCEforms']['config']['type_t3dev'] == '') {
					switch($value['TCEforms']['config']['type']) {
						case 'group':
							// If group, than internal_type is a required value
							switch($value['TCEforms']['config']['internal_type']) {
								case 'file':
								case 'folder':
									$typeValue = 'file';
								break;
								case 'db':
									$typeValue = 'rel';
								break;
							}
						break;
						case 'select':
							$typeValue = 'select';
						break;
					}
					$this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'][$key]['TCEforms']['config']['type_t3dev'] = $typeValue;
				}
			}
		}		
	}


	/**
	 * Merges a new sheet into current flexformarray
	 *
	 * @return	void
	 */
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

	/**
	 * Merges current flexformarray to delete one sheet
	 *
	 * @return	html	message to confirm deletion
	 */
	protected function deleteSheet($sheet, $delete = 0) {
		if($delete) {
			$sheet = trim($sheet);
			unset($this->flexformArray['sheets'][$sheet]);
			$this->pObj->setToSession('sheet', 'sDEF');
			$this->save();
			$this->flexform = t3lib_div::getURL($this->filename);
			$this->flexformArray = t3lib_div::xml2array($this->flexform, 'T3DataStructure');			
		} else {
			$this->message = $GLOBALS['LANG']->getLL('label_del_sheet_really').'&nbsp;';
			$this->message .= '<strong>'.$this->pObj->getFromSession('sheet').'</strong><br />';
			$this->message .= '<input type="hidden" name="ffgen[del]" value="1" />';
			$this->message .= '<a href="index.php?ffgen[delSheet]=del&amp;ffgen[del]=1">'.$GLOBALS['LANG']->getLL('label_YES').'</a>&nbsp;';
			$this->message .= '<input type="submit" name="ffgen[submit]" value="'.$GLOBALS['LANG']->getLL('label_NO').'" />';
			$this->message = $this->pMod->doc->funcMenu($this->message, '');
		}
	}

	/**
	 * Merges current flexformarray to add a new field
	 *
	 * @return	void
	 */
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
		$content .= $this->pMod->doc->spacer(5);
		return $this->pMod->doc->funcMenu($GLOBALS['LANG']->getLL('label_sheets'), $content);
	}
	
	/**
	 * Generate fields for adding a new sheet
	 *
	 * @return	html
	 */
	protected function getNewSheetField() {
		$content = '<input type="text" name="ffgen[newSheet]" value="" />&nbsp;';
		$content .= '<input type="submit" name="ffgen[submit]" value="'.$GLOBALS['LANG']->getLL('label_submit').'" />';
		$content .= $this->pMod->doc->spacer(5);
		return $this->pMod->doc->funcMenu($GLOBALS['LANG']->getLL('label_new_sheet'), $content);
	}

	/**
	 * Generate fields for deleting active sheet
	 *
	 * @return	html
	 */
	protected function getDelSheetField() {
		$content = '<a href="index.php?ffgen[delSheet]=del">'.$GLOBALS['LANG']->getLL('label_del').'</a>';
		$content .= $this->pMod->doc->spacer(5);
		return $this->pMod->doc->funcMenu($GLOBALS['LANG']->getLL('label_del_sheet'), $content);
	}

	/**
	 * Generate selectbox with possible fields to insert
	 *
	 * @return	html
	 */
	protected function getNewFieldSelector() {
		$content .= '<select name="ffgen[newField]" onchange="jumpToUrl(\'?ffgen[newField]=\'+this.options[this.selectedIndex].value,this);">';
		$content .= '<option value=""></option>';
		$dummyField = new tx_t3dev_flexformField($this->pObj, '', $this->extkey);
		$fieldTypes = $dummyField->getFieldTypes();
		foreach($fieldTypes as $k => $v) {
			$content .= '<option value="'.$k.'">'.$GLOBALS['LANG']->getLL('label_flexform_'.$k).'</option>';
		}
		$content .= '</select>';
		return $this->pMod->doc->funcMenu($GLOBALS['LANG']->getLL('label_new_field'), $content);
	}
	
	/**
	 * Generate list with fields in flexform
	 *
	 * @return	html
	 */
	protected function getFieldsForCurrentSheet() {
		// Check if sheets-array is available
		if(!is_array($this->flexformArray['sheets'])) {
			$this->error = $GLOBALS['LANG']->getLL('err_no_valid_xml_file');
			return false;
		}
		
		$currentFields = $this->flexformArray['sheets'][$this->pObj->getFromSession('sheet')]['ROOT']['el'];
		if (is_array($currentFields) && (count($currentFields) > 0)) {
			//$flexformFieldClassname = t3lib_div::makeInstanceClassName('tx_t3dev_flexformField');
			foreach ($currentFields as $k => $v) {
				debug($currentFields, 'getFieldsForCurrentSheet');
				$flexformField = new tx_t3dev_flexformField($this->pObj, $k, $this->extkey, $currentFields[$k]);
				$content .= $flexformField->getFieldOverview();
				$content .= $this->getUpdateButton();
				$content .= $this->pMod->doc->divider(2);
			}
			return $content;
		} else {
			return $GLOBALS['LANG']->getLL('err_no_fields');
		}
	}
	
	protected function getUpdateButton() {
		$ret .= '<input type="submit" name="ffgen[update]" value="'.$GLOBALS['LANG']->getLL('label_update').'" />';
		return $this->pMod->doc->funcMenu('', $ret);
	}
}
?>