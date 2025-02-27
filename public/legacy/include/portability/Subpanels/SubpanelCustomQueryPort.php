<?php
/**
 * SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.
 * Copyright (C) 2021 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Supercharged by SuiteCRM" logo. If the display of the logos is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Supercharged by SuiteCRM".
 */

class SubpanelCustomQueryPort
{

    /**
     * @param SugarBean $bean
     * @param string $subpanel
     * @return array
     */
    public function getQueries(SugarBean $bean, string $subpanel = ''): array
    {
        /* @noinspection PhpIncludeInspection */
        require_once 'include/SubPanel/SubPanelDefinitions.php';

        $spd = new SubPanelDefinitions($bean);
        $subpanel_def = $spd->load_subpanel($subpanel);

        $subpanel_list = [];
        if (method_exists($subpanel_def, 'isCollection')) {
            if ($subpanel_def->isCollection()) {
                if ($subpanel_def->load_sub_subpanels() === false) {
                    $subpanel_list = [];
                } else {
                    $subpanel_list = $subpanel_def->sub_subpanels;
                }
            } else {
                $subpanel_list[] = $subpanel_def;
            }
        } else {
            $GLOBALS['log']->fatal('Subpanel definition should be an aSubPanel');
        }


        return SugarBean::getUnionRelatedListQueries($subpanel_list, $subpanel_def, $bean, '');
    }

    /**
     * @param string $query
     * @return array
     */
    public function fetchRow(string $query): array
    {
        $db = DBManagerFactory::getInstance('listviews');
        $result = $db->query($query, true, "SubpanelCustomQueryPort: Error executing custom query");
        $rows = $db->fetchByAssoc($result);
        if (empty($rows)) {
            return [];
        }

        return $rows;
    }

    /**
     * @param string $query
     * @return array
     */
    public function fetchAll(string $query): array
    {
        $db = DBManagerFactory::getInstance('listviews');
        $result = $db->query($query, true, "SubpanelCustomQueryPort: Error executing custom query");

        $rows = [];

        while (($row = $db->fetchByAssoc($result))) {
            $rows[] = $row;
        }

        return $rows;
    }

}
