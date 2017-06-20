<?php

$GLOBALS['TL_DCA']['tl_assignment_data'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_assignment',
        'ctable'            => ['tl_assignee'],
        'switchToEdit'      => true,
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['HeimrichHannot\Assignment\Backend\AssignmentData', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['HeimrichHannot\Haste\Dca\General', 'setDateAdded'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'                  => 4,
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;search,limit',
            'disableGrouping'       => true,
            'child_record_callback' => ['HeimrichHannot\Assignment\Backend\AssignmentData', 'listChildren'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_assignment_data']['edit'],
                'href'  => 'table=tl_assignee',
                'icon'  => 'edit.gif',
            ],
            'editheader' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_assignment_data']['editheader'],
                'href'            => 'act=edit',
                'icon'            => 'header.gif',
                'button_callback' => ['HeimrichHannot\Assignment\Backend\AssignmentData', 'editHeader'],
            ],
            'copy'       => [
                'label'           => &$GLOBALS['TL_LANG']['tl_assignment_data']['copy'],
                'href'            => 'act=copy',
                'icon'            => 'copy.gif',
                'button_callback' => ['HeimrichHannot\Assignment\Backend\AssignmentData', 'copyArchive'],
            ],
            'delete'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_assignment_data']['copy'],
                'href'            => 'act=delete',
                'icon'            => 'delete.gif',
                'attributes'      => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
                'button_callback' => ['HeimrichHannot\Assignment\Backend\AssignmentData', 'deleteArchive'],
            ],
            'toggle'     => [
                'label' => &$GLOBALS['TL_LANG']['tl_assignment_data']['toggle'],
                'href'  => 'act=toggle',
                'icon'  => 'toggle.gif',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_assignment_data']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'assignees'  => [
                'label' => &$GLOBALS['TL_LANG']['tl_assignment_data']['assignees'],
                'href'  => 'act=assignees',
                'icon'  => 'assignees.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__'                                          => ['type', 'published'],
        'default'                                               => '{type_legend},type;{publish_legend},published;',
        \HeimrichHannot\Assignment\Assignment::TYPE_DATA_POSTAL => '{type_legend},type;{data_legend},postal,country;{publish_legend},published;',
    ],
    'subpalettes' => [
        'published' => 'start,stop',
    ],
    'fields'      => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'       => [
            'foreignKey' => 'tl_assignment.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'    => [
            'label' => &$GLOBALS['TL_LANG']['tl_assignment_data']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'type'      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_assignment_data']['type'],
            'default'          => 'text',
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => ['HeimrichHannot\Assignment\Backend\AssignmentData', 'getTypes'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_assignment_data']['references']['type'],
            'eval'             => ['chosen' => true, 'submitOnChange' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'postal'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignment_data']['postal'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'country'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignment_data']['country'],
            'exclude'   => true,
            'filter'    => true,
            'sorting'   => true,
            'inputType' => 'select',
            'options'   => System::getCountries(),
            'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(2) NOT NULL default ''",
        ],
        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignment_data']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignment_data']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignment_data']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
    ],
];
