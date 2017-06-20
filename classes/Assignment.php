<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Assignment;


use HeimrichHannot\Haste\Util\Arrays;

class Assignment
{
    const TYPE_DATA_POSTAL = 'postal';

    const TYPE_ASSIGNEE_MEMBER = 'member';


    public static function getConstants(array $arrPrefixes = [])
    {
        $refl = new \ReflectionClass(__CLASS__);

        return array_values(Arrays::filterByPrefixes($refl->getConstants(), $arrPrefixes));
    }

    public static function getDataTypes()
    {
        return static::getConstants(['TYPE_DATA_']);
    }

    public static function getAssigneeTypes()
    {
        return static::getConstants(['TYPE_ASSIGNEE_']);
    }
}