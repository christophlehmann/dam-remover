<?php

/*******************************************************************
 * Extension Manager/Repository config file for ext "dam_remover".
 *******************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DAM Remover',
	'description' => 'This extension helps you to remove dam and provides extbase commands that i) converts media tags in bodytext fields (currently only tt_contents bodytext field is supported) and ii) image relations from extension dam_ttcontent.',
	'category' => 'misc',
	'shy' => 0,
	'version' => '0.2',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Christoph Lehmann',
	'author_email' => 'post@christophlehmann.eu',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'extbase' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>