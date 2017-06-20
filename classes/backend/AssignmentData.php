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


use HeimrichHannot\Assignment\Assignment;

class AssignmentData extends \Backend
{
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