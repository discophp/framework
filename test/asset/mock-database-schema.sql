CREATE  TABLE `discophp_test_person` (
      `person_id` INT NOT NULL AUTO_INCREMENT ,
      `name` VARCHAR(120) NOT NULL ,
      `age` INT NULL ,
      PRIMARY KEY (`person_id`) 
);

CREATE  TABLE `discophp_test_person_email` (
      `email_id` INT NOT NULL AUTO_INCREMENT ,
      `person_id` INT NOT NULL ,
      `email` VARCHAR(180) NOT NULL ,
      PRIMARY KEY (`email_id`, `person_id`) 
);
