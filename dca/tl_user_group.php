<?php

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('fop;', 'fop;{assignment_legend},assignments,assignmentp;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['assignments'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['assignments'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'              => 'tl_assignment.title',
	'eval'                    => array('multiple' => true),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['assignmentp'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['assignmentp'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('create', 'delete'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple' => true),
	'sql'                     => "blob NULL"
);