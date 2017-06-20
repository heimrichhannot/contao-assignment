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


class Assignee extends \Backend
{

    public function listChildren($arrRow)
    {
        $name = $arrRow['id'];

        switch ($arrRow['type'])
        {
            case 'member':
                if (($objMember = \MemberModel::findByPk($arrRow['member'])) !== null)
                {
                    $name = $objMember->email;
                }
                break;
        }

        return '<div class="tl_content_left">' . ($name) . ' <span style="color:#b3b3b3; padding-left:3px">[' . \Date::parse(
                \Config::get('datimFormat'),
                trim($arrRow['dateAdded'])
            ) . ']</span></div>';
    }

    /**
     * Get available assignee types
     *
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function getTypes(\DataContainer $dc)
    {
        return \HeimrichHannot\Assignment\Assignment::getAssigneeTypes();
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

        // Set the root IDs
        if (!is_array($objUser->assignments) || empty($objUser->assignments))
        {
            $root = [0];
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
                    \Controller::log(
                        'Not enough permissions to create assignee items in assignee archive ID "' . \Input::get('pid') . '"',
                        'tl_assignee checkPermission',
                        TL_ERROR
                    );
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Input::get('pid'), $root))
                {
                    \Controller::log(
                        'Not enough permissions to ' . \Input::get('act') . ' assignee item ID "' . $id . '" to assignee archive ID "' . \Input::get('pid') . '"',
                        'tl_assignee checkPermission',
                        TL_ERROR
                    );
                    \Controller::redirect('contao/main.php?act=error');
                }
            // NO BREAK STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $objDatabase->prepare("SELECT pid FROM tl_assignee WHERE id=?")->limit(1)->execute($id);

                if ($objArchive->numRows < 1)
                {
                    \Controller::log('Invalid assignee item ID "' . $id . '"', 'tl_assignee checkPermission', TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }

                if (!in_array($objArchive->pid, $root))
                {
                    \Controller::log(
                        'Not enough permissions to ' . \Input::get('act') . ' assignee item ID "' . $id . '" of assignee archive ID "' . $objArchive->pid . '"',
                        'tl_assignee checkPermission',
                        TL_ERROR
                    );
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
                    \Controller::log('Not enough permissions to access assignee archive ID "' . $id . '"', 'tl_assignee checkPermission', TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }

                $objArchive = $objDatabase->prepare("SELECT id FROM tl_assignee WHERE pid=?")->execute($id);

                if ($objArchive->numRows < 1)
                {
                    \Controller::log('Invalid assignee archive ID "' . $id . '"', 'tl_assignee checkPermission', TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }

                $session                   = $objSession->getData();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $objSession->setData($session);
                break;

            default:
                if (strlen(\Input::get('act')))
                {
                    \Controller::log('Invalid command "' . \Input::get('act') . '"', 'tl_assignee checkPermission', TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                elseif (!in_array($id, $root))
                {
                    \Controller::log('Not enough permissions to access assignee archive ID ' . $id, 'tl_assignee checkPermission', TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $objUser = \BackendUser::getInstance();

        if (strlen(\Input::get('tid')))
        {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
            \Controller::redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$objUser->isAdmin && !$objUser->hasAccess('tl_assignee::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }

    public function toggleVisibility($intId, $blnVisible)
    {
        $objUser     = \BackendUser::getInstance();
        $objDatabase = \Database::getInstance();

        // Check permissions to publish
        if (!$objUser->isAdmin && !$objUser->hasAccess('tl_assignee::published', 'alexf'))
        {
            \Controller::log('Not enough permissions to publish/unpublish item ID "' . $intId . '"', 'tl_assignee toggleVisibility', TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new \Versions('tl_assignee', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_assignee']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_assignee']['fields']['published']['save_callback'] as $callback)
            {
                $this->import($callback[0]);
                $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
            }
        }

        // Update the database
        $objDatabase->prepare("UPDATE tl_assignee SET tstamp=" . time() . ", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
        \Controller::log(
            'A new version of record "tl_assignee.id=' . $intId . '" has been created' . $this->getParentEntries('tl_assignee', $intId),
            'tl_assignee toggleVisibility()',
            TL_GENERAL
        );
    }
}