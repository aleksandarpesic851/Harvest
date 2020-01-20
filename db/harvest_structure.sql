/*
SQLyog Ultimate
MySQL - 10.1.38-MariaDB : Database - harvest
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `condition_categories` */

DROP TABLE IF EXISTS `condition_categories`;

CREATE TABLE `condition_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Table structure for table `condition_hierarchy` */

DROP TABLE IF EXISTS `condition_hierarchy`;

CREATE TABLE `condition_hierarchy` (
  `condition_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `study_ids` text,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8;

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
  `condition_name` varchar(100) NOT NULL,
  `synonym` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`,`condition_name`)
) ENGINE=InnoDB AUTO_INCREMENT=76274 DEFAULT CHARSET=utf8;

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

/*Table structure for table `update_history` */

DROP TABLE IF EXISTS `update_history`;

CREATE TABLE `update_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated_at` datetime DEFAULT NULL,
  `test` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
