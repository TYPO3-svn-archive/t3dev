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
	 * @var tx_t3dev_flexformModule
	 */
	protected $pObj;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pMod;
	protected $name;
	protected $config;
	protected $configOptions = array(
		'name' 				=> 'text',
		'eval' 				=> 'select||date|time|timesec|datetime|year|int|int+|double2|alphanum|upper|lower',
		'size' 				=> 'text',
		'max' 				=> 'text',
		'required'			=> 'check',
		'check'				=> 'check',
		'stripspace'		=> 'check',
		'pass'				=> 'check',
		'md5'				=> 'check',
		'unique'			=> 'radio|G|L|',
		'wiz_color'			=> 'check',
		'wiz_link'			=> 'check',
		'wiz_example'		=> 'check',
		'wiz_addrec'		=> 'check',
		'wiz_listrec'		=> 'check',
		'wiz_editrec'		=> 'check',
		'cols' 				=> 'text',
		'rows' 				=> 'text',
		'rte'				=> 'select|tt_content|basic|moderate|none|custom',
		'rte_fullscreen'	=> 'check',
		'check_default'		=> 'check',
		'numberBoxes'		=> 'text',
		'select_items'		=> 'text',
		'select_icons'		=> 'check',
		'relations'			=> 'text',
		'relations_selsize'	=> 'text',
		'relations_mm'		=> 'check',
		'rel_table'			=> 'select|tables',
		'rel_type'			=> 'select|group|select|select_cur|select_root|select_storage',
		'rel_dummyitem'		=> 'check',
		'files_type'		=> 'select|images|webimages|all',
		'files'				=> 'text',
		'max_filesize'		=> 'text',
		'files_selsize'		=> 'text',
		'files_thumbs'		=> 'check',
	);
	protected $fieldConfigs = array(
		'input'				=> array('name', 'type', 'size', 'max', 'required'),
		'input_advanced'	=> array('name', 'type', 'size', 'max', 'required', 'check', 'eval', 'stripspace', 'pass', 'md5', 'unique', 'wiz_color', 'wiz_link'),
		'textarea'			=> array('name', 'type', 'cols', 'rows', 'wiz_example'),
		'textarea_rte'		=> array('name', 'type', 'rte', 'rte_fullscreen'),
		'textarea_nowrap'	=> array('name', 'type', 'cols', 'rows', 'wiz_example'),
		'check'				=> array('name', 'type', 'check_default'),
		'check_multi'		=> array('name', 'type', 'numberBoxes'),
		'link'				=> array('name', 'type'),
		'date'				=> array('name', 'type'),
		'datetime'			=> array('name', 'type'),
		'integer'			=> array('name', 'type'),
		'select'			=> array('name', 'type', 'select_items', 'select_icons', 'relations', 'relations_selsize'),
		'radio'				=> array('name', 'type', 'select_items'),
		'rel'				=> array('name', 'type', 'rel_table', 'rel_type', 'rel_dummyitem', 'relations', 'relations_selsize', 'relations_mm', 'wiz_addrec', 'wiz_listrec', 'wiz_editrec'),
		'files'				=> array('name', 'type', 'files_type', 'files', 'max_filesize', 'files_selsize', 'files_thumbs'),
		'none'				=> array('name', 'type'),
		'passtrough'		=> array('name', 'type'),
	);
	
	public function __construct(&$pObj, &$LANG, $name, $config = array()) {
		$this->pObj = $pObj;
		$this->pMod = $this->pObj->getPObj();
		$this->LANG = &$LANG;
		$this->name = (strlen($name)) ? $name : 'a'.substr(md5(time()), 0, 10);
		$this->config = $config;
		$this->request = t3lib_div::_GP('ffgen');
	}
	
	public function init() {
	}
	
	public function getFieldOverview() {
		$ret = '<table>';
		for ($i=0; $i < count($this->fieldConfigs[$this->config['TCEforms']['config']['type']]); $i++) {
			if ($this->fieldConfigs[$this->config['TCEforms']['config']['type']][$i] == 'name') {
				$ret .= '
				<tr>
					<td>'.$this->LANG->getLL('label_flexform_param_name').'</td>
					<td>'.$this->getEditField('name', $this->name).'</td>			
				</tr>';
			} else {
				$ret .= '
				<tr>
					<td>'.$this->LANG->getLL('label_flexform_param_'.$this->fieldConfigs[$this->config['TCEforms']['config']['type']][$i]).'</td>
					<td>'.$this->getEditField($this->fieldConfigs[$this->config['TCEforms']['config']['type']][$i], $this->config['TCEforms']['config'][$this->fieldConfigs[$this->config['TCEforms']['config']['type']][$i]]).'</td>			
				</tr>';
			}
		}
		$ret .= '</table>';
		return $this->pMod->doc->section($this->name, $ret);
	}
	
	public function getFieldsConfig() {
		return $this->fieldConfigs;
	}
	
	public function getEditField($param, $value) {
		if ($param == 'type') {
			$ret = '<select name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']">';
			foreach ($this->fieldConfigs as $k => $v) {
				$sel = ($k == $value) ? ' selected="selected"' : '';
				$ret .= '<option value="'.$k.'"'.$sel.'>'.$this->LANG->getLL('label_flexform_'.$k).'</option>';
			}
			$ret .= '</select>';
			return $ret;
		}
		if (is_array($this->configOptions[$param])) {
			$ret = '<select name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']">';
			for ($i=0; $i<count($this->configOptions[$param]); $i++) {
				$sel = ($this->configOptions[$param][$i] == $value) ? ' selected="selected"' : '';
				$ret .= '<option value="'.$this->configOptions[$param][$i].'"'.$sel.'>'.$this->LANG->getLL('label_flexform_'.$param.'_'.$this->configOptions[$param][$i]).'</option>';
			}
			$ret .= '</select>';
			return $ret;
		} else {
			$parts = t3lib_div::trimExplode('|', $this->configOptions[$param]);
			switch ($parts[0]) {
				case 'text' :
					return '<input type="text" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="'.$value.'" />';
				break;
				case 'check' :
					if ($value) {
						$checked = ' checked="checked"';
					}
					return '<input type="checkbox" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="1" '.$checked.'/>';
				break;
				case 'select' :
					$values = t3lib_div::trimExplode('|', $this->configOptions[$param]);
					$ret = '<select name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']">';
					if ($param == 'rel_table') {
						$ret .= '<option value=""></option>';
						$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLES');
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
							while ($data = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
								$sel = ($data[0] == $value) ? ' selected="selected"' : '';
								$ret .= '<option value="'.$data[0].'"'.$sel.'>'.$data[0].'</option>';
							}
						}
					} else {
						for ($i = 1; $i<count($values); $i++) {
							$sel = ($values[$i] == $value) ? ' selected="selected"' : '';
							$ret .= '<option value="'.$values[$i].'"'.$sel.'>'.$this->LANG->getLL('label_flexform_'.$param.'_'.$values[$i]).'</option>';
						}
					}
					$ret .= '</select>';
					return $ret;
				break;
				case 'radio' :
					$values = t3lib_div::trimExplode('|', $this->configOptions[$param]);
					for ($i = 1; $i<count($values); $i++) {
						$sel = ($values[$i] == $value) ? ' checked="checked"' : '';
						$ret .= '<input type="radio" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="'.$values[$i].'"'.$sel.' /> '.$this->LANG->getLL('label_flexform_'.$param.'_'.$values[$i]);
					}
					return $ret;
				break;
			}
		}
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setType($type) {
		$this->config['TCEforms']['config']['type'] = $type;
	}		
	
	public function asArray() {
		return $this->config;
	}
}
?>
