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

        if ($objUser->isAdmin)
        {
            return;
        }

        // Set the root IDs
        if (!is_array($objUser->assignments) || empty($objUser->assignments))
        {
            $root = array(0);
        }
        else
        {
            $root = $objUser->assignments;
        }

        $id = strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Input::get('act'))
        {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(\Input::get('pid')) || !in_array(\Input::get('pid'), $root))
                {
                    \System::log('Not enough permissions to create association data in association archive ID "'.\Input::get('pid').'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Input::get('pid'), $root))
                {
                    \System::log('Not enough permissions to '.\Input::get('act').' association data ID "'.$id.'" to association archive ID "'.\Input::get('pid').'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
            // NO BREAK STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = \Database::getInstance()->prepare("SELECT pid FROM tl_assignment_data WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1)
                {
                    \System::log('Invalid association data ID "'.$id.'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }

                if (!in_array($objArchive->pid, $root))
                {
                    \System::log('Not enough permissions to '.\Input::get('act').' association data ID "'.$id.'" of association archive ID "'.$objArchive->pid.'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root))
                {
                    \System::log('Not enough permissions to access association data ID "'.$id.'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }

                $objArchive = \Database::getInstance()->prepare("SELECT id FROM tl_assignment_data WHERE pid=?")
                    ->execute($id);

                if ($objArchive->numRows < 1)
                {
                    \System::log('Invalid association archive ID "'.$id.'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }

                $session = $objSession->getData();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $objSession->setData($session);
                break;

            default:
                if (strlen(\Input::get('act')))
                {
                    \System::log('Invalid command "'.\Input::get('act').'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                elseif (!in_array($id, $root))
                {
                    \System::log('Not enough permissions to access association archive ID ' . $id, __METHOD__, TL_ERROR);
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