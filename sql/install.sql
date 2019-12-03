CREATE TABLE `civicrm_relationship_type_setting` (
	`relationship_type_id` INT(10) UNSIGNED NOT NULL,
	`is_permission_a_b` TINYINT(4) NULL DEFAULT NULL,
	`is_permission_b_a` TINYINT(4) NULL DEFAULT NULL,
	PRIMARY KEY (`relationship_type_id`),
	CONSTRAINT `FK__civicrm_relationship_type` FOREIGN KEY (`relationship_type_id`) REFERENCES `civicrm_relationship_type` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
