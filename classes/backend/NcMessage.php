<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Assignment\Backend;


use HeimrichHannot\Assignment\AssignmentModel;

class NcMessage extends \Backend
{
    /**
     * Get all assignment archives
     * @param \DataContainer $dc
     */
    public function getAssignmentArchives(\DataContainer $dc)
    {
        $arrOptions = [];

        if(($objAssignments = AssignmentModel::findAll()) === null)
        {
            return $arrOptions;
        }

        return $objAssignments->fetchEach('title');
    }

}