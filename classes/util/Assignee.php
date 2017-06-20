<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Assignment\Util;


use HeimrichHannot\Assignment\AssigneeModel;
use HeimrichHannot\Assignment\Assignment;

class Assignee
{
    /**
     * Get the email of an assignee
     *
     * @param AssigneeModel $objAssignees
     *
     * @return string|null
     */
    public static function getAssigneeEmail(AssigneeModel $objAssignees)
    {
        $strEmail = null;

        switch ($objAssignees->type)
        {
            case Assignment::TYPE_ASSIGNEE_MEMBER:

                $id = deserialize($objAssignees->member, true)[0];

                if (($objMember = \MemberModel::findByPk($id)) === null)
                {
                    return $strEmail;
                }

                $strEmail = $objMember->email;

                break;
        }

        return $strEmail;
    }

}