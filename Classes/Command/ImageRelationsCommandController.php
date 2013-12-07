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

class Tx_DamRemover_Command_ImageRelationsCommandController extends Tx_Extbase_MVC_Controller_CommandController {

	private $ignoredElements = array();

	/**
	 * Converts image relations of extension dam_ttcontent. Images will be attached tt_content.image and copied to upload/pics
	 *
	 *
	 * @return void
	 */
	public function convertCommand() {

		$destinationDirectory = PATH_site . 'uploads/pics';

		if (!is_writable($destinationDirectory)) {
			$this->outputLine($destinationDirectory . " is not writable");
			$this->quit(1);
		}

		$extFileFunc = t3lib_div::makeInstance('t3lib_extFileFunctions');

		if ($relations = $this->getRelations()) {

			$newImageFields = array();

			foreach ($relations as $relation) {
				$sourceFile = PATH_site . $relation['file_path'] . $relation['file_name'];
				if (!is_file($sourceFile)) {
					$this->outputLine("tt_content:" . $relation['uid_foreign'] . "Missing file $sourceFile");
					continue;
				}

				$newFilename = $extFileFunc->getUniqueName($relation['file_name'], $destinationDirectory);
				copy($sourceFile, $newFilename);

				if (!array_key_exists($relation['uid_foreign'], $newImageFields)) {
					if (empty($relation['image'])) {
						$newImageField = basename($newFilename);
					} else {
						$newImageField = $relation['image'] . ',' . basename($newFilename);
					}
				} else {
					$newImageField = $newImageFields[$relation['uid_foreign']] . ',' . basename($newFilename);
				}
				$newImageFields[$relation['uid_foreign']] = $newImageField;

				// Field tt_content.tx_damttcontent_files is ignored since it will be removed
				$updateFields = array( 'image' => $newImageField);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					'tt_content',
					'uid=' . $relation['uid_foreign'],
					$updateFields
				);

				$GLOBALS['TYPO3_DB']->exec_DELETEquery(
					'tx_dam_mm_ref',
					'uid_local=' . $relation['uid_local'] .
					' AND uid_foreign=' . $relation['uid_foreign'] .
					' AND tablenames="tt_content"' .
					' AND ident="tx_damttcontent_files"'
				);
			}
		}
		$this->outputLine('Conversion completed');
	}

	/**
	 * Get image relations between tt_content and tx_dam
	 *
	 * @TODO Improve the query
	 *
	 * @return array
	 */
	private function getRelations() {

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows	(
			'tx_dam.file_path,
			 tx_dam.file_name,
			 tx_dam_mm_ref.uid_local,
			 tx_dam_mm_ref.uid_foreign,
			 tx_dam_mm_ref.sorting_foreign,
			 tt_content.image',
			'tx_dam, tx_dam_mm_ref, tt_content',
			'tx_dam_mm_ref.uid_foreign = tt_content.uid AND
			 tx_dam_mm_ref.uid_local = tx_dam.uid AND
			 tx_dam_mm_ref.tablenames="tt_content" AND
			 tx_dam_mm_ref.ident="tx_damttcontent_files"',
			'',
			'sorting_foreign'
		);

		return $rows;
	}
}

?>