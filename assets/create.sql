SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `fanpages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `fanpages` (
  `fanpage_id` BIGINT(100) NOT NULL ,
  `fanpage_name` VARCHAR(255) NOT NULL ,
  `fanpage_category` VARCHAR(45) NOT NULL ,
  `latest_timestamp` BIGINT(100) NULL ,
  `access_token` VARCHAR(255) NOT NULL ,
  `tab_id` VARCHAR(255) NULL ,
  `active` TINYINT(1) NOT NULL DEFAULT FALSE ,
  `installed` TINYINT(1) NULL DEFAULT FALSE ,
  PRIMARY KEY (`fanpage_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `albums`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `albums` (
  `album_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `user_category` VARCHAR(255) NULL DEFAULT NULL COMMENT '								' ,
  `album_name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `location` TEXT NULL DEFAULT NULL ,
  `link` TEXT NULL DEFAULT NULL ,
  `cover_photo_id` BIGINT(100) NOT NULL ,
  `count` INT(10) NOT NULL ,
  `type` TEXT NULL DEFAULT NULL ,
  `created_time` BIGINT(100) NOT NULL ,
  `updated_time` BIGINT(100) NULL DEFAULT NULL ,
  PRIMARY KEY (`album_id`) ,
  INDEX `ALBUMS_FANPAGES_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `ALBUMS_FANPAGES_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `comments`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `comments` (
  `comment_id` VARCHAR(200) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `post_id` VARCHAR(100) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `user_category` VARCHAR(255) NULL DEFAULT NULL ,
  `message` TEXT NOT NULL ,
  `created_time` BIGINT(100) NOT NULL ,
  `likes_count` FLOAT NULL DEFAULT NULL ,
  `type` TINYINT(4) NULL DEFAULT NULL ,
  PRIMARY KEY (`comment_id`) ,
  INDEX `COMMENTS_FANPAGES_ID_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `COMMENTS_FANPAGES_ID_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fans`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `fans` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `name` VARCHAR(200) NOT NULL ,
  `first_name` VARCHAR(100) NOT NULL ,
  `last_name` VARCHAR(100) NOT NULL ,
  `gender` TEXT NOT NULL ,
  `locale` VARCHAR(10) NOT NULL ,
  `lang` VARCHAR(5) NOT NULL ,
  `country` VARCHAR(5) NOT NULL ,
  PRIMARY KEY (`facebook_user_id`) ,
  INDEX `FAN_FANPAGE_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `FAN_FANPAGE_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Demographic`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Demographic` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  `F` BIGINT(100) NOT NULL ,
  `M` BIGINT(100) NOT NULL ,
  `U` BIGINT(100) NOT NULL ,
  `13_17` BIGINT(100) NOT NULL ,
  `18_24` BIGINT(100) NOT NULL ,
  `25_34` BIGINT(100) NOT NULL ,
  `35_44` BIGINT(100) NOT NULL ,
  `45_54` BIGINT(100) NOT NULL ,
  `55_64` BIGINT(100) NOT NULL ,
  `65_` BIGINT(100) NOT NULL ,
  `F_13_17` BIGINT(100) NOT NULL ,
  `F_18_24` BIGINT(100) NOT NULL ,
  `F_25_34` BIGINT(100) NOT NULL ,
  `F_35_44` BIGINT(100) NOT NULL ,
  `F_45_54` BIGINT(100) NOT NULL ,
  `F_55_64` BIGINT(100) NOT NULL ,
  `F_65_` BIGINT(100) NOT NULL ,
  `M_13_17` BIGINT(100) NOT NULL ,
  `M_18_24` BIGINT(100) NOT NULL ,
  `M_25_34` BIGINT(100) NOT NULL ,
  `M_35_44` BIGINT(100) NOT NULL ,
  `M_45_54` BIGINT(100) NOT NULL ,
  `M_55_64` BIGINT(100) NOT NULL ,
  `M_65_` BIGINT(100) NOT NULL ,
  `U_13_17` BIGINT(100) NOT NULL ,
  `U_18_24` BIGINT(100) NOT NULL ,
  `U_25_34` BIGINT(100) NOT NULL ,
  `U_35_44` BIGINT(100) NOT NULL ,
  `U_45_54` BIGINT(100) NOT NULL ,
  `U_55_64` BIGINT(100) NOT NULL ,
  `U_65_` BIGINT(100) NOT NULL ,
  `U_UNKNOWN` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Impressions_Content`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Impressions_Content` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Impressions_Page`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Impressions_Page` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Referral_Src`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Referral_Src` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `suggestions` BIGINT(100) NOT NULL ,
  `other` BIGINT(100) NOT NULL ,
  `profile` BIGINT(100) NOT NULL ,
  `like_widget` BIGINT(100) NOT NULL ,
  `mobile` BIGINT(100) NOT NULL ,
  `composer` BIGINT(100) NOT NULL ,
  `search` BIGINT(100) NOT NULL ,
  `profile_connect` BIGINT(100) NOT NULL ,
  `network_ego` BIGINT(100) NOT NULL ,
  `wap` BIGINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Traffic_Content`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Traffic_Content` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Traffic_Page_External`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Traffic_Page_External` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Traffic_Page_Internal`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Traffic_Page_Internal` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Traffic_Src`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Traffic_Src` (
  `insights_id` BIGINT(100) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `location` VARCHAR(100) NOT NULL ,
  `value` BIGINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  `flag` INT(10) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Insights_Traffic_Views`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Insights_Traffic_Views` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  `type` INT(10) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `likes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `likes` (
  `likes_id` VARCHAR(200) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `post_id` VARCHAR(100) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `post_type` TINYINT(10) NOT NULL ,
  PRIMARY KEY (`likes_id`) ,
  INDEX `LIKES_FANPAGES_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `LIKES_FANPAGES_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `photos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `photos` (
  `photo_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `album_id` BIGINT(100) NOT NULL ,
  `faceboook_user_id` BIGINT(100) NOT NULL ,
  `user_category` VARCHAR(255) NULL DEFAULT NULL ,
  `caption` TEXT NULL DEFAULT NULL ,
  `picture` TEXT NOT NULL ,
  `source` TEXT NOT NULL ,
  `height` INT(50) NULL DEFAULT NULL ,
  `width` INT(50) NULL DEFAULT NULL ,
  `link` TEXT NULL DEFAULT NULL ,
  `position` INT(50) NULL DEFAULT NULL ,
  `created_time` BIGINT(100) NOT NULL ,
  `updated_time` BIGINT(100) NULL DEFAULT NULL ,
  PRIMARY KEY (`photo_id`) ,
  INDEX `PHOTOS_FANPAGES_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `PHOTOS_FANPAGES_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `posts`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `posts` (
  `post_id` VARCHAR(255) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `user_category` VARCHAR(255) NULL DEFAULT NULL ,
  `message` TEXT NOT NULL ,
  `privacy_descr` VARCHAR(25) NULL DEFAULT NULL ,
  `privacy_value` VARCHAR(25) NULL DEFAULT NULL ,
  `type` VARCHAR(25) NULL DEFAULT NULL ,
  `created_time` BIGINT(100) NULL DEFAULT NULL ,
  `updated_time` BIGINT(100) NULL DEFAULT NULL ,
  `application_name` VARCHAR(25) NULL DEFAULT NULL ,
  `application_id` BIGINT(100) NULL DEFAULT NULL ,
  `comments_count` FLOAT NULL DEFAULT NULL ,
  `likes_count` FLOAT NULL DEFAULT NULL ,
  PRIMARY KEY (`post_id`) ,
  INDEX `POSTS_FANPAGES_ID_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `POSTS_FANPAGES_ID_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `active_guests`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `active_guests` (
  `ip` VARCHAR(15) NOT NULL ,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`ip`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `active_users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `active_users` (
  `user_id` VARCHAR(32) NOT NULL ,
  `timestamp` VARCHAR(45) NOT NULL DEFAULT 'CURRENT_TIMESTAMP' ,
  PRIMARY KEY (`user_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `banned_users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `banned_users` (
  `user_id` VARCHAR(32) NOT NULL ,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`user_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `facebook_users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facebook_users` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `facebook_user_name` VARCHAR(32) NOT NULL ,
  `facebook_user_email` VARCHAR(255) NOT NULL ,
  `facebook_user_gender` ENUM('male','female') NOT NULL ,
  `updated_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `access_token` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`facebook_user_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank_users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `fancrank_users` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `fancrank_user_email` VARCHAR(255) NOT NULL ,
  `updated_time` BIGINT(100) NOT NULL ,
  `access_token` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`facebook_user_id`) ,
  INDEX `FANCRANK_USER_FAN_FK` (`facebook_user_id` ASC) ,
  CONSTRAINT `FANCRANK_USER_FAN_FK`
    FOREIGN KEY (`facebook_user_id` )
    REFERENCES `fans` (`facebook_user_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `users` (
  `user_id` BIGINT(100) NOT NULL ,
  `user_handle` VARCHAR(45) NOT NULL ,
  `user_first_name` VARCHAR(45) NOT NULL ,
  `user_last_name` VARCHAR(45) NOT NULL ,
  `user_avatar` VARCHAR(255) NOT NULL ,
  `user_email` VARCHAR(255) NOT NULL ,
  `user_access_token` VARCHAR(255) NOT NULL ,
  `user_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`user_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fanpage_admins`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `fanpage_admins` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`facebook_user_id`, `fanpage_id`) ,
  INDEX `FANPAGE_ADMIN_PAGE_FK` (`fanpage_id` ASC) ,
  INDEX `FANAPAGE_ADMIN_USER_FK` (`facebook_user_id` ASC) ,
  CONSTRAINT `FANAPAGE_ADMIN_USER_FK`
    FOREIGN KEY (`facebook_user_id` )
    REFERENCES `users` (`user_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FANPAGE_ADMIN_PAGE_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `queue`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `queue` (
  `queue_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `queue_name` VARCHAR(100) NULL ,
  `timeout` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '30' ,
  PRIMARY KEY (`queue_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `message`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `message` (
  `message_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `queue_id` INT(10) UNSIGNED NOT NULL ,
  `body` VARCHAR(1000) NOT NULL ,
  `timeout` DECIMAL(14,4) UNSIGNED NULL ,
  `created` INT(10) UNSIGNED NOT NULL ,
  `handle` CHAR(32) NULL ,
  `md5` CHAR(32) NOT NULL ,
  PRIMARY KEY (`message_id`) ,
  INDEX `MESSAGE_QUEUE_FK` (`queue_id` ASC) ,
  UNIQUE INDEX `handle_UNIQUE` (`handle` ASC) ,
  UNIQUE INDEX `md5_UNIQUE` (`md5` ASC) ,
  CONSTRAINT `MESSAGE_QUEUE_FK`
    FOREIGN KEY (`queue_id` )
    REFERENCES `queue` (`queue_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `posts_media`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `posts_media` (
  `post_id` VARCHAR(255) NOT NULL ,
  `post_type` VARCHAR(45) NULL ,
  `post_picture` VARCHAR(255) NULL ,
  `post_link` VARCHAR(255) NULL ,
  `post_source` VARCHAR(255) NULL ,
  `post_name` VARCHAR(255) NULL ,
  `post_caption` VARCHAR(255) NULL ,
  `post_description` VARCHAR(255) NULL ,
  `post_icon` VARCHAR(45) NULL ,
  PRIMARY KEY (`post_id`) ,
  INDEX `fk_posts_media_1` (`post_id` ASC) ,
  CONSTRAINT `fk_posts_media_1`
    FOREIGN KEY (`post_id` )
    REFERENCES `posts` (`post_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
