<?php

namespace HeimrichHannot\Assignment;

class AssigneeModel extends \Model
{

    protected static $strTable = 'tl_assignee';

    /**
     * Find published assignees by their pids
     *
     * @param array $arrIds     The parent ids
     * @param array $arrOptions An optional options array
     *
     * @return static|\Model\Collection|AssignmentDataModel[]|AssignmentDataModel|null A model, model collection or null if the result is empty
     */
    public static function findPublishedByPids(array $arrIds = [], array $arrOptions = [])
    {
        $t = static::$strTable;

        $arrColumns = ["$t.pid IN(" . implode(',', array_map('intval', $arrIds)) . ")"];

        if (!BE_USER_LOGGED_IN)
        {
            $time         = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
        }

        return static::findBy($arrColumns, null, $arrOptions);
    }
}