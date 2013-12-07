<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Christoph Lehmann (post@christophlehmann.eu)
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

class Tx_DamRemover_Command_MediaTagsCommandController extends Tx_Extbase_MVC_Controller_CommandController {

	/**
	 * @var string
	 */
	private $currentField;

	/**
	 * Convert media tag to default link tag, at the moment only in field tt_content.bodytext
	 *
	 * @return void
	 */
	public function convertCommand() {
		$tableFields = $this->getTableFields();
		if ($tableFields) {
			foreach($tableFields as $table => $fields) {
				foreach($fields as $field) {

					$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						"uid,$field",
						$table,
						"$field LIKE '%<media%'");

					if ($rows) {
						foreach($rows as $row) {

							$this->currentField = "$table.$field:" . $row['uid'];

							$updateFields[$field]  = $this->convertTags($row[$field]);
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
								$table,
								'uid=' . $row['uid'],
								$updateFields
							);

							$this->outputLine("Updated $table.$field:" . $row['uid']);
						}
					}
				}
			}
		}

		$this->outputLine('Conversion completed');
	}

	/**
	 * Search for table fields containing media links
	 *
	 * To get a complete list of tables and fields search for softref in TCA
	 *
	 * @return array nested array of tables and fields
	 */
	public  function getTableFields() {
		$tableFields["tt_content"] = array("bodytext");
		return $tableFields;
	}

	/**
	 * Convert media tags to link tags in text
	 *
	 * @param $content
	 * @return string
	 */
	public function convertTags($content) {

		/* @var $htmlParser t3lib_parsehtml */
		$htmlParser = t3lib_div::makeInstance('t3lib_parsehtml');
		$mediaTags = $htmlParser->splitTags('media', $content);

		$newStartTag = '';
		foreach ($mediaTags as $k => $foundValue) {
			if ($k % 2) {
				preg_match('/<MEDIA[[:space:]]+([0-9]*)/i',$foundValue, $matches);
				if( !$file = $this->getFileByDamUid($matches[1])) {
					$this->outputLine("Error: " . $this->currentField . " has broken links");
				}
				$newStartTag = preg_replace('/<MEDIA[[:space:]]+([0-9]*)/i','<link ' . $file, $foundValue);
			} else {
				$endTag = preg_replace('/<\/media>/', '</link>', $foundValue);
				$oldMediaTag = $mediaTags[$k -1] . $foundValue;
				$newLinkTag =  $newStartTag . $endTag;
			}

			$content = str_replace($oldMediaTag, $newLinkTag, $content);
		}

		return $content;
	}

	/**
	 * Get file from dam uid
	 *
	 * @param int $uid
	 * @return mixed
	 */
	public function getFileByDamUid($uid) {
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'file_path, file_name',
			'tx_dam',
			'uid=' . (int)$uid);

		if (!$row) {
			return FALSE;
		} else {
			return $row['file_path'] . $row['file_name'];
		}
	}
}

?>