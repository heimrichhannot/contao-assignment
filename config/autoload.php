<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Models
	'HeimrichHannot\Assignment\AssignmentModel'        => 'system/modules/assignment/models/AssignmentModel.php',
	'HeimrichHannot\Assignment\AssignmentDataModel'    => 'system/modules/assignment/models/AssignmentDataModel.php',
	'HeimrichHannot\Assignment\AssigneeModel'          => 'system/modules/assignment/models/AssigneeModel.php',

	// Classes
	'HeimrichHannot\Assignment\Backend\AssignmentData' => 'system/modules/assignment/classes/backend/AssignmentData.php',
	'HeimrichHannot\Assignment\Backend\NcMessage'      => 'system/modules/assignment/classes/backend/NcMessage.php',
	'HeimrichHannot\Assignment\Backend\Assignment'     => 'system/modules/assignment/classes/backend/Assignment.php',
	'HeimrichHannot\Assignment\Backend\Assignee'       => 'system/modules/assignment/classes/backend/Assignee.php',
	'HeimrichHannot\Assignment\Util\Assignee'          => 'system/modules/assignment/classes/util/Assignee.php',
	'HeimrichHannot\Assignment\Assignment'             => 'system/modules/assignment/classes/Assignment.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_assignment_data_merge' => 'system/modules/assignment/templates/backend',
));
