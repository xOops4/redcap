<?php

$sql = "
-- add last_enabled_on to mycap projects table
ALTER TABLE `redcap_mycap_projects` 
    ADD `last_enabled_on` datetime DEFAULT NULL COMMENT 'Time when project is enabled for MyCap by button click/copy project/xml upload';
\n";

// Get all values for each project (currently enable for MyCap + Projects enabled for MyCap in past) and transform
$project_sql = "SELECT p.project_id, p.creation_time 
				FROM redcap_mycap_projects m, redcap_projects p
				WHERE m.status = '1' AND p.project_id = m.project_id ORDER BY p.project_id";
$q = db_query($project_sql);
while ($row = db_fetch_assoc($q)) {
    $project_id = $row['project_id'];

    $log_event_table = Logging::getLogEventTable($project_id);
    $log_sql = "SELECT ts FROM " . $log_event_table . " WHERE project_id = '" . $project_id . "' AND description = 'Enable MyCap' ORDER BY ts DESC LIMIT 1;";
    $q1 = db_query($log_sql);
    $lastEnabledOn = ($q1 && db_num_rows($q1) > 0) ? db_result($q1, 0) : '';

    if ($lastEnabledOn != '') {
        $lastEnabledOn = DateTimeRC::format_ts_from_int_to_ymd($lastEnabledOn).":00";
        // Update last_enabled_on in redcap_mycap_projects table
        $sql .= "UPDATE redcap_mycap_projects SET last_enabled_on = '" . $lastEnabledOn . "' WHERE project_id = '" . $project_id . "';\n";
    } else {
        $sql .= "UPDATE redcap_mycap_projects SET last_enabled_on = '" . $row['creation_time'] . "' WHERE project_id = '" . $project_id . "';\n";
    }
}

print $sql;