<?php

namespace HeimrichHannot\Assignment;

class AssignmentDataModel extends \Model
{

    protected static $strTable = 'tl_assignment_data';


    /**
     * Find published assignment data by various criteria
     *
     * @param mixed $strColumn  The property name
     * @param mixed $varValue   The property value
     * @param array $arrOptions An optional options array
     *
     * @return static|\Model\Collection|AssignmentDataModel[]|AssignmentDataModel|null A model, model collection or null if the result is empty
     */
    public static function findPublishedBy($strColumn, $varValue, array $arrOptions = [])
    {
        $t = static::$strTable;

        if (!BE_USER_LOGGED_IN)
        {
            $time         = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
        }

        return static::findBy($strColumn, $varValue, $arrOptions);
    }
}