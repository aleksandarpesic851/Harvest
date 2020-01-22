DROP TABLE IF EXISTS `age_groups`;

CREATE TABLE `age_groups` (
  `age_group` varchar(20) NOT NULL,
  PRIMARY KEY (`age_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `age_groups` */

insert  into `age_groups`(`age_group`) values 
('Adult'),
('Child'),
('Older Adult');

/*Table structure for table `intervention_types` */

DROP TABLE IF EXISTS `intervention_types`;

CREATE TABLE `intervention_types` (
  `intervention_type` varchar(50) NOT NULL,
  PRIMARY KEY (`intervention_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `intervention_types` */

insert  into `intervention_types`(`intervention_type`) values 
('Behavioral'),
('Biological'),
('BONE VOID FILLER'),
('Combination Product'),
('Device'),
('Diagnostic Test'),
('Dietary Supplement'),
('Drug'),
('Experts On Call'),
('Experts On Track'),
('Experts Revive'),
('Genetic'),
('Other'),
('Premium'),
('Procedure'),
('Radiation'),
('Thermal Therapy'),
('Tylenol');

/*Table structure for table `modifiers` */

DROP TABLE IF EXISTS `modifiers`;

CREATE TABLE `modifiers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modifier` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Data for the table `modifiers` */

insert  into `modifiers`(`id`,`modifier`) values 
(1,'NONE'),
(2,'Relapsed'),
(3,'Refractory'),
(4,'Chronic'),
(5,'Acute'),
(6,'High-risk'),
(7,'Healthy'),
(8,'Systemic'),
(9,'Resistance'),
(10,'Advanced'),
(11,'Metastases'),
(12,'Myocardial Infarction'),
(13,'Smoldering'),
(14,'Atopic'),
(15,'Progression'),
(16,'Recurrent'),
(17,'Adult'),
(18,'Child');

/*Table structure for table `phases` */

DROP TABLE IF EXISTS `phases`;

CREATE TABLE `phases` (
  `phase` varchar(50) NOT NULL,
  PRIMARY KEY (`phase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `phases` */

insert  into `phases`(`phase`) values 
('Early Phase 1'),
('Not Applicable'),
('Phase 1'),
('Phase 2'),
('Phase 3'),
('Phase 4');

/*Table structure for table `statuses` */

DROP TABLE IF EXISTS `statuses`;

CREATE TABLE `statuses` (
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `statuses` */

insert  into `statuses`(`status`) values 
('Active, not recruiting'),
('Approved for marketing'),
('Available'),
('Completed'),
('Enrolling by invitation'),
('No longer available'),
('Not yet recruiting'),
('Recruiting'),
('Suspended'),
('Temporarily not available'),
('Terminated'),
('Unknown status'),
('Withdrawn'),
('Withheld');

/*Table structure for table `study_design_types` */

DROP TABLE IF EXISTS `study_design_types`;

CREATE TABLE `study_design_types` (
  `study_design_type` varchar(20) NOT NULL,
  PRIMARY KEY (`study_design_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `study_design_types` */

insert  into `study_design_types`(`study_design_type`) values 
('Allocation'),
('Intervention Model'),
('Masking'),
('Observational Model'),
('Primary Purpose'),
('Time Perspective');

/*Table structure for table `study_types` */

DROP TABLE IF EXISTS `study_types`;

CREATE TABLE `study_types` (
  `study_type` varchar(50) NOT NULL,
  PRIMARY KEY (`study_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `study_types` */

insert  into `study_types`(`study_type`) values 
('Expanded Access'),
('Interventional'),
('Observational');

/*Table structure for table `update_history` */

DROP TABLE IF EXISTS `update_history`;

CREATE TABLE `update_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `condition_categories`;

CREATE TABLE `condition_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Table structure for table `condition_hierarchy` */

DROP TABLE IF EXISTS `condition_hierarchy`;

CREATE TABLE `condition_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `condition_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8;

/*Table structure for table `condition_hierarchy_modifier_stastics` */

DROP TABLE IF EXISTS `condition_hierarchy_modifier_stastics`;

CREATE TABLE `condition_hierarchy_modifier_stastics` (
  `modifier_name` varchar(30) DEFAULT NULL,
  `modifier_id` int(11) DEFAULT NULL,
  `hierarchy_id` int(11) DEFAULT NULL,
  `condition_id` int(11) DEFAULT NULL,
  `condition_name` varchar(100) DEFAULT NULL,
  `study_ids` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `conditions` */

DROP TABLE IF EXISTS `conditions`;

CREATE TABLE `conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `condition_name` varchar(100) DEFAULT NULL,
  `synonym` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76392 DEFAULT CHARSET=utf8;

/*Table structure for table `drug_categories` */

DROP TABLE IF EXISTS `drug_categories`;

CREATE TABLE `drug_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `drug_hierarchy` */

DROP TABLE IF EXISTS `drug_hierarchy`;

CREATE TABLE `drug_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drug_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `study_ids` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

/*Table structure for table `drugs` */

DROP TABLE IF EXISTS `drugs`;

CREATE TABLE `drugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drug_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=244904 DEFAULT CHARSET=utf8;

/*Table structure for table `studies` */

DROP TABLE IF EXISTS `studies`;

CREATE TABLE `studies` (
  `nct_id` int(11) NOT NULL,
  `title` varchar(500) DEFAULT NULL,
  `status` enum('Not yet recruiting','Recruiting','Enrolling by invitation','Active, not recruiting','Suspended','Terminated','Completed','Withdrawn','Unknown status','Available','No longer available','Temporarily not available','Approved for marketing') DEFAULT NULL,
  `status_open` tinyint(1) DEFAULT '1',
  `study_results` enum('No Results Available','Has Results') DEFAULT NULL,
  `conditions` text,
  `interventions` text,
  `outcome_measures` text,
  `sponsors` text,
  `gender` enum('All','Male','Female') DEFAULT NULL,
  `min_age` int(10) DEFAULT NULL,
  `max_age` int(10) DEFAULT NULL,
  `age_groups` set('Child','Adult','Older Adult') DEFAULT NULL,
  `phases` set('Early Phase 1','Phase 1','Phase 2','Phase 3','Phase 4','Not Applicable') DEFAULT NULL,
  `enrollment` int(11) DEFAULT '0',
  `study_types` enum('Expanded Access','Interventional','Observational') DEFAULT NULL,
  `study_designs` text,
  `start_date` date DEFAULT NULL,
  `primary_completion_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `study_first_posted` date DEFAULT NULL,
  `last_update_posted` date DEFAULT NULL,
  `results_first_posted` date DEFAULT NULL,
  `locations` text,
  PRIMARY KEY (`nct_id`),
  KEY `SEARCH_KEY` (`study_types`,`status`,`gender`,`age_groups`,`phases`,`min_age`,`max_age`,`start_date`,`primary_completion_date`,`completion_date`,`study_first_posted`,`last_update_posted`,`results_first_posted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `study_id_conditions` */

DROP TABLE IF EXISTS `study_id_conditions`;

CREATE TABLE `study_id_conditions` (
  `nct_id` int(11) DEFAULT NULL,
  `condition` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `study_id_drugs` */

DROP TABLE IF EXISTS `study_id_drugs`;

CREATE TABLE `study_id_drugs` (
  `nct_id` int(11) DEFAULT NULL,
  `drug` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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
     on ((`t1`.`id` = `t2`.`condition_id`))));

CREATE VIEW `drug_hierarchy_view` AS (
select
  `t2`.`id`          AS `id`,
  `t1`.`id`          AS `drug_id`,
  `t1`.`drug_name`   AS `drug_name`,
  `t2`.`parent_id`   AS `parent_id`,
  `t2`.`category_id` AS `category_id`,
  `t2`.`study_ids`   AS `study_ids`
from (`drugs` `t1`
   join `drug_hierarchy` `t2`
     on ((`t1`.`id` = `t2`.`drug_id`))));