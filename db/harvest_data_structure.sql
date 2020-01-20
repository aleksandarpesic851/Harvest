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
/*Table structure for table `age_groups` */

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
('G'),
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

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
