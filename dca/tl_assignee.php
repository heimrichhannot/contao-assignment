<?php

$GLOBALS['TL_DCA']['tl_assignee'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_assignment_data',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['HeimrichHannot\Assignment\Backend\Assignee', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['HeimrichHannot\Haste\Dca\General', 'setDateAdded'],
        ],
        'sql'               => [
            'keys' => [
                'id'                       => 'primary',
                'pid,start,stop,published' => 'index',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['id'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'                  => 4,
            'headerFields'          => ['postal'],
            'panelLayout'           => 'filter;search,limit',
            'disableGrouping'       => true,
            'child_record_callback' => ['HeimrichHannot\Assignment\Backend\Assignee', 'listChildren'],

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
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_assignee']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_assignee']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_assignee']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_assignee']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['HeimrichHannot\Assignment\Backend\Assignee', 'toggleIcon'],
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_assignee']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__'                                              => ['type', 'published'],
        'default'                                                   => '{type_legend},type;{publish_legend},published;',
        \HeimrichHannot\Assignment\Assignment::TYPE_ASSIGNEE_MEMBER => '{type_legend},type;{assignee_legend},member;{publish_legend},published;',

    ],
    'subpalettes' => [
        'published' => 'start,stop',
    ],
    'fields'      => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'       => [
            'foreignKey' => 'tl_assignment_data.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'    => [
            'label' => &$GLOBALS['TL_LANG']['tl_assignee']['tstamp'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_assignee']['type'],
            'default'          => 'text',
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => ['HeimrichHannot\Assignment\Backend\Assignee', 'getTypes'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_assignee']['references']['type'],
            'eval'             => ['chosen' => true, 'submitOnChange' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'member'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignee']['member'],
            'inputType' => 'tagsinput',
            'sql'       => "blob NULL",
            'eval'      => [
                'placeholder' => &$GLOBALS['TL_LANG']['tl_member']['placeholders']['locations'],
                'freeInput'   => false,
                'mode'        => \TagsInput::MODE_REMOTE,
                'remote'      => [
                    'fields'       => ['email', 'id'],
                    'format'       => '%s [ID:%s]',
                    'queryField'   => 'email',
                    'queryPattern' => '%QUERY%',
                    'foreignKey'   => 'tl_member.id',
                    'limit'        => 10,
                ],
            ],
        ],
        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignee']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignee']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_assignee']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
    ],
];
