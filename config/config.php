<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['assignment'] = [
    'tables' => ['tl_assignment', 'tl_assignment_data', 'tl_assignee'],
    'icon'   => 'system/modules/assignment/assets/img/icon_assignment.png',
    'merge'  => ['HeimrichHannot\Assignment\Backend\AssignmentData', 'merge'],
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_assignment']      = 'HeimrichHannot\Assignment\AssignmentModel';
$GLOBALS['TL_MODELS']['tl_assignment_data'] = 'HeimrichHannot\Assignment\AssignmentDataModel';
$GLOBALS['TL_MODELS']['tl_assignee']        = 'HeimrichHannot\Assignment\AssigneeModel';

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'assignments';
$GLOBALS['TL_PERMISSIONS'][] = 'assignmentp';