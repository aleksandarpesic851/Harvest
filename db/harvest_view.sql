CREATE VIEW `condition_hierarchy_view` AS (
select
  `t2`.`id`             AS `id`,
  `t1`.`id`             AS `condition_id`,
  `t1`.`condition_name` AS `condition_name`,
  `t1`.`synonym`        AS `synonym`,
  `t2`.`parent_id`      AS `parent_id`,
  `t2`.`category_id`    AS `category_id`
from (`conditions` `t1`
   join `condition_hierarchy` `t2`
     on ((`t1`.`id` = `t2`.`condition_id`))))