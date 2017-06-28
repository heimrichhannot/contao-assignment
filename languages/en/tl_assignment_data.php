<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_assignment_data'];

/**
 * Fields
 */
$arrLang['type']      = ['Datatype', 'Please select a data type.'];
$arrLang['postal']    = ['Postal', 'Please enter a postal.'];
$arrLang['published'] = ['Publish assignment data', 'Make the assignment data publicly visible on the website.'];
$arrLang['start']     = ['Show from', 'Do not publish the assignment data on the website before this date.'];
$arrLang['stop']      = ['Show until', 'Unpublish the assignment data on the website after this date.'];
$arrLang['tstamp']    = ['Revision date', ''];


/**
 * Legends
 */
$arrLang['type_legend']    = 'Type';
$arrLang['data_legend']    = 'Data';
$arrLang['publish_legend'] = 'Publish settings';


/**
 * Buttons
 */
$arrLang['new']    = ['New assignment data', 'assignment data create'];
$arrLang['edit']   = ['Edit assignment data', 'Edit assignment data ID %s'];
$arrLang['copy']   = ['Duplicate assignment data', 'Duplicate assignment data ID %s'];
$arrLang['delete'] = ['Delete assignment data', 'Delete assignment data ID %s'];
$arrLang['toggle'] = ['Publish/unpublish assignment data', 'Publish/unpublish assignment data ID %s'];
$arrLang['show']   = ['Assignment data details', 'Show the details of assignment data ID %s'];
$arrLang['merge']  = [
    'Merge',
    'Merge assignment data und assignee based an assignment data type (assignment data will last distinct based on its type, assignees will be merged and duplicates removed).',
];

/**
 * References
 */
$arrLang['references']['type']['postal'] = 'Postleitzahl';