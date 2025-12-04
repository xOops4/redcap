-- SQL to add new fields
ALTER TABLE `redcap_form_display_logic_conditions` ADD `apply_to_data_entry` tinyint(1) NOT NULL DEFAULT '1' AFTER `control_condition`;
ALTER TABLE `redcap_form_display_logic_conditions` ADD `apply_to_survey` tinyint(1) NOT NULL DEFAULT '0' AFTER `apply_to_data_entry`;
ALTER TABLE `redcap_form_display_logic_conditions` ADD `apply_to_mycap` tinyint(1) NOT NULL DEFAULT '0' AFTER `apply_to_survey`;

-- This query updates the 'surveys_enabled' in the 'redcap_form_display_logic_conditions' table for each condition
UPDATE redcap_form_display_logic_conditions AS cond
INNER JOIN redcap_projects AS projects ON cond.project_id = projects.project_id
SET cond.apply_to_survey = '1'
WHERE projects.surveys_enabled = '1' AND projects.form_activation_survey_autocontinue = '1';

-- This query updates the 'mycap_enabled' in the 'redcap_form_display_logic_conditions' table for each condition
UPDATE redcap_form_display_logic_conditions AS cond
    INNER JOIN redcap_projects AS projects ON cond.project_id = projects.project_id
    SET cond.apply_to_mycap = '1'
WHERE projects.mycap_enabled = '1' AND projects.form_activation_mycap_support = '1';

-- Drop 2 columns from redcap_projects as we are not including these in optional settings on FDL popup
ALTER TABLE `redcap_projects` DROP `form_activation_survey_autocontinue`, DROP `form_activation_mycap_support`;