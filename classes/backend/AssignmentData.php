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


use HeimrichHannot\Assignment\AssigneeModel;
use HeimrichHannot\Assignment\Assignment;
use HeimrichHannot\Assignment\AssignmentDataModel;

class AssignmentData extends \Backend
{
    /**
     * Merge assignment data and assignees
     */
    public function merge()
    {
        /** @var \BackendTemplate|object $objTemplate */
        $objTemplate               = new \BackendTemplate('be_assignment_data_merge');
        $objTemplate->action       = ampersand(\Environment::get('request'));
        $objTemplate->syncHeadline = $GLOBALS['TL_LANG']['tl_filecredit']['syncHeadline'];

        if (($objAssignmentData = AssignmentDataModel::findByPid(\Input::get('id'))) !== null)
        {
            $arrDataDelete     = [];
            $arrData           = [];
            $arrAssigneeUpdate = [];
            $arrAssigneeData   = [];
            $arrAssigneeDelete = [];

            while ($objAssignmentData->next())
            {
                if (!isset($objAssignmentData->{$objAssignmentData->type}))
                {
                    continue;
                }

                $arrData[$objAssignmentData->type][$objAssignmentData->{$objAssignmentData->type}][] = $objAssignmentData->id;
            }

            foreach ($arrData as $type => $sets)
            {
                foreach ($sets as $key => $values)
                {
                    // skip items with less than 1 item
                    if (count($values) <= 1)
                    {
                        continue;
                    }

                    // use first id to merge children in
                    $newId = array_shift($values);

                    $objAssignees = AssigneeModel::findBy(["tl_assignee.pid IN(" . implode(',', array_map('intval', $values)) . ")"], null);

                    if ($objAssignees === null)
                    {
                        continue;
                    }

                    while ($objAssignees->next())
                    {
                        if ($objAssignees->pid == $newId)
                        {
                            continue;
                        }

                        $oldId               = $objAssignees->id;
                        $arrAssigneeUpdate[] = 'Update assignee ' . $objAssignees->pid . ' for type ' . $type . ':' . $key . ': set pid from ' . $oldId . ' to ' . $newId;
                        $objAssignees->pid   = $newId;
                        $objAssignees->save();
                    }

                    $arrDataDelete = array_merge($arrDataDelete, $values);
                }
            }

            // delete merged assignment data
            if (!empty($arrDataDelete))
            {
                \Database::getInstance()->execute("DELETE FROM tl_assignment_data WHERE id IN(" . implode(',', array_map('intval', $arrDataDelete)) . ")");
            }


            // remove duplicate assignees
            foreach ($arrData as $type => $sets)
            {
                foreach ($sets as $key => $values)
                {
                    $objAssignees = AssigneeModel::findBy(["tl_assignee.pid IN(" . implode(',', array_map('intval', $values)) . ")"], null);

                    while ($objAssignees->next())
                    {
                        if (!isset($objAssignees->{$objAssignees->type}))
                        {
                            continue;
                        }

                        // assignee already exists, delete
                        if (is_array($arrAssigneeData[$key][$objAssignees->type]) && isset($arrAssigneeData[$key][$objAssignees->type][$objAssignees->{$objAssignees->type}]))
                        {
                            $arrAssigneeDelete[] = $objAssignees->id;
                        }

                        $arrAssigneeData[$key][$objAssignees->type][$objAssignees->{$objAssignees->type}][] = $objAssignees->id;
                    }
                }
            }

            // delete duplicate assignees
            if (!empty($arrAssigneeDelete))
            {
                \Database::getInstance()->execute("DELETE FROM tl_assignee WHERE id IN(" . implode(',', array_map('intval', $arrAssigneeDelete)) . ")");
            }
        }

        $objTemplate->deletedData = $arrDataDelete;
        $objTemplate->deletedAssignees = $arrAssigneeDelete;

        // Default variables
        $objTemplate->backHref   = \System::getReferer(true);
        $objTemplate->backTitle  = specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']);
        $objTemplate->backButton = $GLOBALS['TL_LANG']['MSC']['backBT'];

        return $objTemplate->parse();
    }

    public function listChildren($arrRow)
    {
        $name = $arrRow['id'];

        switch ($arrRow['type'])
        {
            case 'postal':
                $name = $arrRow['postal'];
                break;
        }

        return '<div class="tl_content_left">' . ($name) . ' <span style="color:#b3b3b3; padding-left:3px">[' . \Date::parse(
                \Config::get('datimFormat'),
                trim($arrRow['dateAdded'])
            ) . ']</span></div>';
    }

    public function checkPermission()
    {
        $objUser     = \BackendUser::getInstance();
        $objSession  = \Session::getInstance();
        $objDatabase = \Database::getInstance();

        if ($objUser->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($objUser->assignments) || empty($objUser->assignments))
        {
            $root = [0];
        }
        else
        {
            $root = $objUser->assignments;
        }

        $GLOBALS['TL_DCA']['tl_assignment_data']['list']['sorting']['root'] = $root;

        // Check permissions to add archives
        if (!$objUser->hasAccess('create', 'assignmentp'))
        {
            $GLOBALS['TL_DCA']['tl_assignment_data']['config']['closed'] = true;
        }

        // Check current action
        switch (\Input::get('act'))
        {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root))
                {
                    $arrNew = $objSession->get('new_records');

                    if (is_array($arrNew['tl_assignment_data']) && in_array(\Input::get('id'), $arrNew['tl_assignment_data']))
                    {
                        // Add permissions on user level
                        if ($objUser->inherit == 'custom' || !$objUser->groups[0])
                        {
                            $objUser = $objDatabase->prepare("SELECT assignments, assignmentp FROM tl_user WHERE id=?")->limit(1)->execute($objUser->id);

                            $arrModulep = deserialize($objUser->assignmentp);

                            if (is_array($arrModulep) && in_array('create', $arrModulep))
                            {
                                $arrModules   = deserialize($objUser->assignments);
                                $arrModules[] = \Input::get('id');

                                $objDatabase->prepare("UPDATE tl_user SET assignments=? WHERE id=?")->execute(serialize($arrModules), $objUser->id);
                            }
                        }

                        // Add permissions on group level
                        elseif ($objUser->groups[0] > 0)
                        {
                            $objGroup = $objDatabase->prepare("SELECT assignments, assignmentp FROM tl_user_group WHERE id=?")->limit(1)->execute($objUser->groups[0]);

                            $arrModulep = deserialize($objGroup->assignmentp);

                            if (is_array($arrModulep) && in_array('create', $arrModulep))
                            {
                                $arrModules   = deserialize($objGroup->assignments);
                                $arrModules[] = \Input::get('id');

                                $objDatabase->prepare("UPDATE tl_user_group SET assignments=? WHERE id=?")->execute(serialize($arrModules), $objUser->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[]               = \Input::get('id');
                        $objUser->assignments = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$objUser->hasAccess('delete', 'assignmentp')))
                {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' assignment_data ID "' . \Input::get('id') . '"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $objSession->getData();
                if (\Input::get('act') == 'deleteAll' && !$objUser->hasAccess('delete', 'assignmentp'))
                {
                    $session['CURRENT']['IDS'] = [];
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $objSession->setData($session);
                break;

            default:
                if (strlen(\Input::get('act')))
                {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' assignment_data archives', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return \BackendUser::getInstance()->canEditFieldsOf('tl_assignment_data') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars(
                $title
            ) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }

    public function copyArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return \BackendUser::getInstance()->hasAccess('create', 'assignmentp')
            ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label)
              . '</a> '
            : \Image::getHtml(
                preg_replace('/\.gif$/i', '_.gif', $icon)
            ) . ' ';
    }

    public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return \BackendUser::getInstance()->hasAccess('delete', 'assignmentp')
            ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label)
              . '</a> '
            : \Image::getHtml(
                preg_replace('/\.gif$/i', '_.gif', $icon)
            ) . ' ';
    }

    /**
     * Get available data types
     *
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function getTypes(\DataContainer $dc)
    {
        return Assignment::getDataTypes();
    }

}