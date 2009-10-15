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

/*
@TODO: Weiter testen mit rel und file
*/

class tx_t3dev_flexformField {
	/**
	 * @var tx_t3dev_flexformModule
	 */
	protected $pObj;
	/**
	 * @var t3lib_SCbase
	 */
	protected $pMod;
	protected $name;
	protected $extkey;
	protected $config;

	//On which way should a field displayed in HTML
	//In most cases the values are egual with attr. "type" in input
	//f.e. "size" can displayed in a textfield. <input type="text">
	//f.e. "required" can displayed as checkbox. <input type="check">
	protected $configOptions = array(
		'eval' 							=> 'selectm|date|time|timesec|datetime|year|int|upper|lower|double2|alpha|num|alphanum|alphanum_x|nospace|is_in',
		'eval-required'			=> 'check',
		'eval-trim'					=> 'check',
		'eval-date'					=> 'check',
		'eval-datetime'			=> 'check',
		'eval-time'					=> 'check',
		'eval-timesec'			=> 'check',
		'eval-year'					=> 'check',
		'eval-int'					=> 'check',
		'eval-upper'				=> 'check',
		'eval-lower'				=> 'check',
		'eval-alpha'				=> 'check',
		'eval-num'					=> 'check',
		'eval-alphanum'			=> 'check',
		'eval-alphanum_x'		=> 'check',
		'eval-nospace'			=> 'check',
		'eval-md5'					=> 'check',
		'eval-is_in'				=> 'check',
		'eval-password'			=> 'check',
		'eval-double2'			=> 'check',
		'eval-unique'				=> 'check',
		'eval-uniqueInPid'	=> 'check',
		'is_in'							=> 'text',
		'name'							=> 'text',
		'size' 							=> 'text',
		'max' 							=> 'text',
		'checkbox'					=> 'check',
		'unique'						=> 'radio|G|L|',
		'wiz_color'					=> 'check',
		'wiz_link'					=> 'check',
		'wiz_addrec'				=> 'check',
		'wiz_listrec'				=> 'check',
		'wiz_editrec'				=> 'check',
		'cols' 							=> 'text',
		'rows' 							=> 'text',
		'rte'								=> 'select|tt_content|none',
		'check_default'			=> 'check',
		'numberBoxes'				=> 'text',
		'select_items'			=> 'text',
		'select_icons'			=> 'check',
		'maxitems'					=> 'text',
		'relations_mm'			=> 'check',
		'rel_table'					=> 'select|tables',
		'rel_type'					=> 'select|group|select|select_cur|select_root|select_storage',
		'rel_dummyitem'			=> 'check',
		'files_type'				=> 'select|images|webimages|all',
		'files'							=> 'text',
		'max_filesize'			=> 'text',
		'files_selsize'			=> 'text',
		'files_thumbs'			=> 'check',
	);
	
	// The possible field types like in kickstarter
	protected $fieldType = array(
		'input'						=> array('name', 'type_t3dev', 'size', 'max', 'eval-required'),
		'input+'					=> array('name', 'type_t3dev', 'size', 'max', 'is_in', 'eval-required', 'checkbox', 'eval', 'eval-trim', 'eval-password', 'eval-md5', 'unique', 'wiz_color', 'wiz_link'),
		'textarea'				=> array('name', 'type_t3dev', 'cols', 'rows'),
		'textarea_rte'		=> array('name', 'type_t3dev', 'rte'),
		'textarea_nowrap'	=> array('name', 'type_t3dev', 'cols', 'rows'),
		'check'						=> array('name', 'type_t3dev', 'check_default'),
		//'check_4'					=> array('name', 'type_t3dev', 'numberBoxes'),
		//'check_10'				=> array('name', 'type_t3dev', 'numberBoxes'),
		'link'						=> array('name', 'type_t3dev', 'checkbox'),
		'date'						=> array('name', 'type_t3dev'),
		'datetime'				=> array('name', 'type_t3dev'),
		'integer'					=> array('name', 'type_t3dev'),
		'select'					=> array('name', 'type_t3dev', 'select_items', 'maxitems', 'size'),
		'radio'						=> array('name', 'type_t3dev', 'select_items'),
		//'rel'							=> array('name', 'type_t3dev', 'rel_table', 'rel_type', 'rel_dummyitem', 'relations', 'relations_selsize', 'relations_mm', 'wiz_addrec', 'wiz_listrec', 'wiz_editrec'),
		//'files'						=> array('name', 'type_t3dev', 'files_type', 'files', 'max_filesize', 'files_selsize', 'files_thumbs'),
		//'flex'						=> array(),
		'none'						=> array('name', 'type_t3dev'),
		'passthrough'			=> array(),
	);
	
	// type-array is not f.e input+...it is input
	// So here we have to map these types to the real types
	// which will be inserted in flexform-array 
	protected $fieldTypeMapping = array(
		'input'						=> 'input',
		'input+'					=> 'input',
		'textarea'				=> 'text',
		'textarea_rte'		=> 'text',
		'textarea_nowrap'	=> 'text',
		'check'						=> 'check',
		'check_4'					=> 'check',
		'check_10'				=> 'check',
		'link'						=> 'input',
		'date'						=> 'input',
		'datetime'				=> 'input',
		'integer'					=> 'input',
		'select'					=> 'select',
		'radio'						=> 'radio',
		'rel'							=> 'group',
		'files'						=> 'group',
		'flex'						=> 'flex',
		'none'						=> 'none',
		'passthrough'			=> 'passthrough',
	);
	
	//All eval-values I could find in API-Doc 
	protected $evalValues = array(
		'required',
		'trim',
		'date',
		'datetime',
		'time',
		'timesec',
		'year',
		'int',
		'upper',
		'lower',
		'alpha',
		'num',
		'alphanum',
		'alphanum_x',
		'nospace',
		'md5',
		'is_in',
		'password',
		'double2',
		'unique',
		'uniqueInPid',
	);
	
	public function __construct(&$pObj, $name, $extkey, $config = array()) {
		$this->pObj = $pObj;
		$this->pMod = $this->pObj->getPObj();
		$this->name = (strlen($name)) ? $name : 'a'.substr(md5(time()), 0, 10);
		$this->extkey = $extkey;
		$this->config = $config;
		$this->request = t3lib_div::_GP('ffgen');
		$this->config['TCEforms']['config']['type'] = $this->fieldTypeMapping[$config['TCEforms']['config']['type_t3dev']];

		$this->checkEvalValues();
		
		// parse wizards
		if (is_array($this->config['TCEforms']['config']['wizards']['link'])) {
			$this->config['TCEforms']['config']['wiz_link'] = 1;
		}
		if (is_array($this->config['TCEforms']['config']['wizards']['color'])) {
			$this->config['TCEforms']['config']['wiz_color'] = 1;
		}
		
		// parse RTE
		debug($this->config, 'RTE');
		if (strlen($this->config['TCEforms']['defaultExtras'])) {
			if ($this->config['TCEforms']['defaultExtras'] == 'richtext[*]') {
				$this->config['TCEforms']['config']['rte'] = 'none';
			} else {
				$this->config['TCEforms']['config']['rte'] = 'tt_content';
			}
		}
		
		// parse select
		if ($this->config['TCEforms']['config']['type_t3dev'] == 'select') {
			if (is_array($this->config['TCEforms']['config']['items'])) {
				$this->config['TCEforms']['config']['select_items'] = count($this->config['TCEforms']['config']['items']);
			}
		}
		
		// parse radio
		if ($this->config['TCEforms']['config']['type_t3dev'] == 'radio') {
			if (is_array($this->config['TCEforms']['config']['items'])) {
				$this->config['TCEforms']['config']['select_items'] = count($this->config['TCEforms']['config']['items']);
			}
		}
		
		// get title
		$this->config['TCEforms']['label'] = 'LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.field_'.$this->name;
	}
	
	/**
	 * Convert value of FFG into real eval-values
	 * Check defined eval-values if they are allowed
	 *
	 * @return	HTML
	 */
	protected function checkEvalValues() {
		$eval = array();
		$fieldConfig = $this->config['TCEforms']['config'];
		//Loop selfmade fields by FlexFormGenerator
		foreach($fieldConfig as $key=>$value) {
			if(substr($key, 0, 5) == 'eval-') {
				$eval[] = substr($key, 5);
				unset($this->config['TCEforms']['config'][$key]); 
			}
		}
		//Check values in eval-array
		if($parts = $this->config['TCEforms']['config']['eval']) {
			if(!is_array($parts)) {
				$parts = t3lib_div::trimExplode(',', $parts);
			}
			for($i = 0; $i < count($parts); $i++) {
				if(t3lib_div::inArray($this->evalValues, $parts[$i])) {
					$eval[] = $parts[$i];
				}
			}
		}
		//If eval-values found, than insert them into eval-array
		if(count($eval)) $this->config['TCEforms']['config']['eval'] = implode(',', $eval);
	}

	/**
	 * Get html for selected field
	 *
	 * @return	HTML
	 */
	public function getFieldOverview() {
		debug($this->config, 'conf');
		$content = '<div class="t3dev_field_'.$this->config['TCEforms']['config']['type_t3dev'].'">';
		$fieldConfig = $this->fieldType[$this->config['TCEforms']['config']['type_t3dev']];
		for ($i=0; $i < count($fieldConfig); $i++) {
			//I don't understand this part. There is no "type" defined in array?!?!
			//if ($this->fieldType[$this->config['TCEforms']['config']['type_t3dev']][$i] == 'type') {
			//	continue;
			//}
			if ($fieldConfig[$i] == 'name') {
				$content .= '
				<label class="label_name">'.$GLOBALS['LANG']->getLL('label_flexform_param_name').'</label>
					'.$this->getEditField('name', $this->name).'			
				';
			} else {
				//eval can contain more than one value
				if(substr($fieldConfig[$i], 0, 5) == 'eval-') {
					$checked = false;
					$evalValue = substr($fieldConfig[$i], 5);
					$parts = t3lib_div::trimExplode(',', $this->config['TCEforms']['config']['eval']);
					for($x = 0; $x < count($parts); $x++) {
						if($evalValue == $parts[$x]) {
							$checked = true; 
						}
					}
					(!$checked)? $value = 0: $value = $evalValue; 
					$content .= '
						<label class="label_eval-'.$evalValue.'">'.$GLOBALS['LANG']->getLL('label_flexform_param_eval-'.$evalValue).'</label>
						'.$this->getEditField('eval-'.$evalValue, $value).'
					';
				} else {
					$content .= '
						<label class="label_'.$fieldConfig[$i].'">'.$GLOBALS['LANG']->getLL('label_flexform_param_'.$fieldConfig[$i]).'</label>
						'.$this->getEditField($fieldConfig[$i], $this->config['TCEforms']['config'][$fieldConfig[$i]]).'
					';					
				}
			}
		}
		$content .= '</div>';
		return $this->pMod->doc->section($this->name, $content);
	}
	
	/**
	 * Get an array with possible field configurations
	 * like string, text, link
	 *
	 * @return	array
	 */
	public function getFieldTypes() {
		return $this->fieldType;
	}
	
	/**
	 * Get html for selected field
	 * $param = something like "max", "eval" or "type"
	 * $value = the value of "max", "eval" or "type" 
	 *
	 * @return	HTML
	 */
	public function getEditField($param, $value) {
		if ($param == 'type_t3dev') {
			$content = '<select class="field_'.$param.'" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']">';
			foreach ($this->fieldType as $k => $v) {
				$sel = ($k == $value) ? ' selected="selected"' : '';
				$content .= '<option value="'.$k.'"'.$sel.'>'.$GLOBALS['LANG']->getLL('label_flexform_'.$k).'</option>';
			}
			$content .= '</select>';
			return $content;
		}
		if (is_array($this->configOptions[$param])) {
			$ret = '<select class="field_'.$param.'" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']">';
			for ($i=0; $i<count($this->configOptions[$param]); $i++) {
				$sel = ($this->configOptions[$param][$i] == $value) ? ' selected="selected"' : '';
				$ret .= '<option value="'.$this->configOptions[$param][$i].'"'.$sel.'>'.$GLOBALS['LANG']->getLL('label_flexform_'.$param.'_'.$this->configOptions[$param][$i]).'</option>';
			}
			$ret .= '</select>';
			return $ret;
		} else {
			$parts = t3lib_div::trimExplode('|', $this->configOptions[$param]);
			switch ($parts[0]) {
				case 'text' :
					return '<input class="field_'.$param.'" type="text" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="'.$value.'" />';
				break;
				case 'check':
					if ($value) {
						$checked = ' checked="checked"';
					}
					return '<input class="field_'.$param.'" type="checkbox" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="1" '.$checked.'/>';
				break;
				case 'selectm' :
				case 'select' :
					$values = t3lib_div::trimExplode('|', $this->configOptions[$param]);
					$multi  = ($parts[0] == 'selectm') ? ' size="5" multiple="multiple"' : '';
					$multin = ($parts[0] == 'selectm') ? '[]' : '';
					$ret = '<select class="field_'.$param.'" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']'.$multin.'"'.$multi.'>';
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
						$value = ($parts[0] == 'selectm') ? t3lib_div::trimExplode(',', $value) : $value;
						for ($i = 1; $i<count($values); $i++) {
							if ($parts[0] == 'selectm') {
								$sel = (in_array($values[$i], $value)) ? ' selected="selected"' : '';
							} else {
								$sel = ($values[$i] == $value) ? ' selected="selected"' : '';
							}
							$ret .= '<option value="'.$values[$i].'"'.$sel.'>'.$GLOBALS['LANG']->getLL('label_flexform_'.$param.'_'.$values[$i]).'</option>';
						}
					}
					$ret .= '</select>';
					return $ret;
				break;
				case 'radio' :
					$values = t3lib_div::trimExplode('|', $this->configOptions[$param]);
					for ($i = 1; $i<count($values); $i++) {
						$sel = ($values[$i] == $value) ? ' checked="checked"' : '';
						$ret .= '<input class="field_'.$param.'" type="radio" name="ffgen[sheetData]['.$this->pObj->getFromSession('sheet').']['.$this->name.'][TCEforms][config]['.$param.']" value="'.$values[$i].'"'.$sel.' /> '.$GLOBALS['LANG']->getLL('label_flexform_'.$param.'_'.$values[$i]) . '<br />';
					}
					return $ret;
				break;
			}
		}
	}
	
	/**
	 * Get name of field. If there is no name, than a md5 hashed value
	 * will be returned.
	 *
	 * @return	void
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * $type is the value from the field selectbox. So value
	 * has to be mapped to the real type first
	 * f.e. textarea_rte => text
	 *
	 * @return	void
	 */
	public function setType($type) {
		$this->config['TCEforms']['config']['type_t3dev'] = $type;
		$this->config['TCEforms']['config']['type'] = $this->fieldTypeMapping[$type];
	}		
	
	/**
	 * returns a array with field configuration for current field
	 *
	 * @return	array		configuration array
	 */
	public function asArray() {
		// post process eval values
		$this->checkEvalValues();
		$ret = $this->config;
		/*
		for($i=0; $i<count($this->evalValues); $i++) {
			if($value = $ret['TCEforms']['config'][$this->evalValues[$i]]) {
				if (!t3lib_div::inList($ret['TCEforms']['config']['eval'], $this->evalValues[$i])) {
					$ret['TCEforms']['config']['eval'] = $this->evalValues[$i] . ',' . $ret['TCEforms']['config']['eval'];
				}
				unset($ret['TCEforms']['config'][$this->evalValues[$i]]);
			}
		}*/
		
		// post process wiz_link
		if ($ret['TCEforms']['config']['wiz_link']) {
			$ret['TCEforms']['config']['wizards']['_PADDING'] = 2;
			$ret['TCEforms']['config']['wizards']['link'] = array(
				'type' => 'popup',
				'title' => 'Link',
				'icon' => 'link_popup.gif',
				'script' => 'browse_links.php?mode=wizard',
				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
			);
			unset($ret['TCEforms']['config']['wiz_link']);
		}

		// post process wiz_color
		if ($ret['TCEforms']['config']['wiz_color']) {
			$ret['TCEforms']['config']['wizards']['_PADDING'] = 2;
			$ret['TCEforms']['config']['wizards']['color'] = array(
				'title' => 'Color:',
				'type' => 'colorbox',
				'dim' => '12x12',
				'tableStyle' => 'border:solid 1px black;',
				'script' => 'wizard_colorpicker.php',
				'JSopenParams' => 'height=300,width=250,status=0,menubar=0,scrollbars=1',
			);
			unset($ret['TCEforms']['config']['wiz_color']);
		}
		
		// post process for field textarea_rte
		if ($ret['TCEforms']['config']['type_t3dev'] == 'textarea_rte') {
			$ret['TCEforms']['config']['wizards']['_PADDING'] = 2;
			$ret['TCEforms']['config']['wizards']['RTE'] = array(
				'notNewRecords' => 1,
				'RTEonly' => 1,
				'type' => 'script',
				'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
				'icon' => 'wizard_rte2.gif',
				'script' => 'wizard_rte.php',
			);
			debug($ret, 'RET');
			switch ($ret['TCEforms']['config']['rte']) {
				case 'tt_content':
					$ret['TCEforms']['defaultExtras'] = 'richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts]';
				break;
				case 'none':
					$ret['TCEforms']['defaultExtras'] = 'richtext[*]';
				break;
			}
			unset($ret['TCEforms']['config']['rte']);
		}
	
		// post process for field textarea_nowrap
		if ($ret['TCEforms']['config']['type_t3dev'] == 'textarea_nowrap') {
			$ret['TCEforms']['config']['wrap'] = 'OFF';
		}
	
		// post process for field link
		if ($ret['TCEforms']['config']['type_t3dev'] == 'link') {
			$ret['TCEforms']['config']['size'] = 15;
			$ret['TCEforms']['config']['max'] = 255;
			$ret['TCEforms']['config']['eval'] = 'trim';
			$ret['TCEforms']['config']['wizards']['_PADDING'] = 2;
			$ret['TCEforms']['config']['wizards']['link'] = array(
				'type' => 'popup',
				'title' => 'Link',
				'icon' => 'link_popup.gif',
				'script' => 'browse_links.php?mode=wizard',
				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
			);
		}
		
		// post process for field date
		if ($ret['TCEforms']['config']['type_t3dev'] == 'date') {
			$ret['TCEforms']['config']['size'] = 8;
			$ret['TCEforms']['config']['max'] = 20;
			$ret['TCEforms']['config']['eval'] = 'date';
			$ret['TCEforms']['config']['default'] = 0;
			$ret['TCEforms']['config']['checkbox'] = 0;
		}
		
		// post process for field datetime
		if ($ret['TCEforms']['config']['type_t3dev'] == 'datetime') {
			$ret['TCEforms']['config']['size'] = 12;
			$ret['TCEforms']['config']['max'] = 20;
			$ret['TCEforms']['config']['eval'] = 'datetime';
			$ret['TCEforms']['config']['default'] = 0;
			$ret['TCEforms']['config']['checkbox'] = 0;
		}
		
		// post process for field integer
		if ($ret['TCEforms']['config']['type_t3dev'] == 'integer') {
			$ret['TCEforms']['config']['size'] = 4;
			$ret['TCEforms']['config']['max'] = 4;
			$ret['TCEforms']['config']['eval'] = 'int';
			$ret['TCEforms']['config']['default'] = 0;
			$ret['TCEforms']['config']['checkbox'] = 0;
			$ret['TCEforms']['config']['range'] = array(
				'upper' => 1000,
				'lower' => 10
			);
		}

		// post process for field select
		if ($ret['TCEforms']['config']['type_t3dev'] == 'select') {
			$ret['TCEforms']['config']['items'] = array();
			for ($i=0; $i < $ret['TCEforms']['config']['select_items']; $i++) {
				$ret['TCEforms']['config']['items'][] = Array('LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.field_'.$this->name.'.I.'.$i, $i);
			}
			unset($ret['TCEforms']['config']['select_items']);
		}
		
		// post process for field select
		if ($ret['TCEforms']['config']['type_t3dev'] == 'radio') {
			$ret['TCEforms']['config']['items'] = array();
			for ($i=0; $i < $ret['TCEforms']['config']['select_items']; $i++) {
				$ret['TCEforms']['config']['items'][] = Array('LLL:EXT:'.$this->extkey.'/locallang_db.php:tt_content.pi_flexform.field_'.$this->name.'.I.'.$i, $i);
			}
			unset($ret['TCEforms']['config']['select_items']);
		}

		// post process for field rel
		if ($ret['TCEforms']['config']['type_t3dev'] == 'rel') {
			$ret['TCEforms']['config']['internal_type'] = 'db';
			$ret['TCEforms']['config']['allowed'] = $ret['TCEforms']['config']['rel_table'];
			unset($ret['TCEforms']['config']['rel_table']);
			
		}
		return $ret;
	}
}
?>