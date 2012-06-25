SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `fancrank` ;
CREATE SCHEMA IF NOT EXISTS `fancrank` DEFAULT CHARACTER SET utf8 ;
USE `fancrank` ;

-- -----------------------------------------------------
-- Table `fancrank`.`fanpages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`fanpages` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`fanpages` (
  `fanpage_id` BIGINT(100) NOT NULL ,
  `fanpage_name` VARCHAR(255) NOT NULL ,
  `fanpage_username` VARCHAR(255) NULL ,
  `fanpage_category` VARCHAR(255) NOT NULL ,
  `fanpage_tab_id` VARCHAR(255) NULL ,
  `latest_timestamp` TIMESTAMP NULL ,
  `access_token` VARCHAR(1000) NOT NULL ,
  `active` TINYINT(1) NOT NULL DEFAULT FALSE ,
  `installed` TINYINT(1) NOT NULL DEFAULT FALSE ,
  `fanpage_about` TEXT NULL ,
  `fanpage_genre` VARCHAR(255) NULL ,
  `has_added_app` TINYINT(1) NULL ,
  `fanpage_likes` BIGINT(100) NULL ,
  `fanpage_is_published` TINYINT(1) NULL DEFAULT FALSE ,
  `fanpage_can_post` TINYINT(1) NULL ,
  PRIMARY KEY (`fanpage_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`albums`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`albums` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`albums` (
  `album_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `facebook_user_id` BIGINT(100) NULL ,
  `album_name` VARCHAR(255) NULL ,
  `album_description` TEXT NULL DEFAULT NULL ,
  `album_location` TEXT NULL DEFAULT NULL ,
  `album_link` TEXT NULL DEFAULT NULL ,
  `album_cover_photo_id` BIGINT(100) NULL ,
  `album_photo_count` BIGINT(100) NULL ,
  `album_type` TEXT NULL DEFAULT NULL ,
  `updated_time` TIMESTAMP NULL DEFAULT NULL ,
  `created_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`album_id`) ,
  INDEX `ALBUMS_FANPAGES_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `ALBUMS_FANPAGES_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fancrank`.`fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`comments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`comments` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`comments` (
  `comment_id` VARCHAR(255) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `comment_post_id` VARCHAR(255) NOT NULL ,
  `comment_message` TEXT NOT NULL ,
  `comment_likes_count` BIGINT(100) NULL DEFAULT NULL ,
  `comment_type` VARCHAR(25) NULL DEFAULT NULL ,
  `created_time` TIMESTAMP NOT NULL ,
  PRIMARY KEY (`comment_id`) ,
  INDEX `COMMENTS_FANPAGES_ID_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `COMMENTS_FANPAGES_ID_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fancrank`.`fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`facebook_users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`facebook_users` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`facebook_users` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `facebook_user_name` VARCHAR(255) NULL ,
  `facebook_user_email` VARCHAR(255) NULL ,
  `facebook_user_gender` VARCHAR(15) NULL ,
  `facebook_user_access_token` VARCHAR(1000) NULL ,
  `updated_time` TIMESTAMP NULL ,
  `created_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `hometown` VARCHAR(255) NULL ,
  `current_location` VARCHAR(255) NULL ,
  `bio` TEXT NULL ,
  `facebook_user_locale` VARCHAR(45) NULL ,
  `facebook_user_first_name` VARCHAR(255) NULL ,
  `facebook_user_last_name` VARCHAR(255) NULL ,
  `facebook_user_avatar` VARCHAR(1000) NULL ,
  `facebook_user_lang` VARCHAR(255) NULL ,
  `facebook_user_birthday` DATETIME NULL ,
  PRIMARY KEY (`facebook_user_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`fans`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`fans` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`fans` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `fan_name` VARCHAR(255) NULL ,
  `fan_first_name` VARCHAR(255) NULL ,
  `fan_last_name` VARCHAR(255) NULL ,
  `fan_user_avatar` VARCHAR(1000) NULL ,
  `fan_gender` VARCHAR(15) NULL ,
  `fan_locale` VARCHAR(255) NULL ,
  `fan_lang` VARCHAR(25) NULL ,
  `fan_country` VARCHAR(225) NULL ,
  `fan_location` VARCHAR(255) NULL ,
  PRIMARY KEY (`fanpage_id`, `facebook_user_id`) ,
  INDEX `FAN_FANPAGE_FK` (`fanpage_id` ASC) ,
  INDEX `FAN_FACEBOOK_USER_FK` (`facebook_user_id` ASC) ,
  CONSTRAINT `FAN_FANPAGE_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fancrank`.`fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FAN_FACEBOOK_USER_FK`
    FOREIGN KEY (`facebook_user_id` )
    REFERENCES `fancrank`.`facebook_users` (`facebook_user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`Insights_Demographic`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Demographic` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Demographic` (
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
-- Table `fancrank`.`Insights_Impressions_Content`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Impressions_Content` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Impressions_Content` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`Insights_Impressions_Page`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Impressions_Page` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Impressions_Page` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`Insights_Referral_Src`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Referral_Src` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Referral_Src` (
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
-- Table `fancrank`.`Insights_Traffic_Content`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Traffic_Content` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Traffic_Content` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`Insights_Traffic_Page_External`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Traffic_Page_External` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Traffic_Page_External` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`Insights_Traffic_Page_Internal`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Traffic_Page_Internal` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Traffic_Page_Internal` (
  `insights_id` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `period` VARCHAR(25) NOT NULL ,
  `value` MEDIUMINT(100) NOT NULL ,
  `time` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`insights_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`Insights_Traffic_Src`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Traffic_Src` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Traffic_Src` (
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
-- Table `fancrank`.`Insights_Traffic_Views`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`Insights_Traffic_Views` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`Insights_Traffic_Views` (
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
-- Table `fancrank`.`likes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`likes` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`likes` (
  `fanpage_id` BIGINT(100) NOT NULL ,
  `post_id` VARCHAR(100) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `post_type` VARCHAR(10) NOT NULL ,
  INDEX `LIKES_FANPAGES_FK` (`fanpage_id` ASC) ,
  PRIMARY KEY (`fanpage_id`, `post_id`, `facebook_user_id`) ,
  CONSTRAINT `LIKES_FANPAGES_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fancrank`.`fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`photos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`photos` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`photos` (
  `photo_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `photo_album_id` BIGINT(100) NULL ,
  `photo_caption` TEXT NULL DEFAULT NULL ,
  `photo_picture` TEXT NULL ,
  `photo_source` TEXT NULL ,
  `photo_height` INT(10) NULL DEFAULT NULL ,
  `photo_width` INT(10) NULL DEFAULT NULL ,
  `photo_link` TEXT NULL DEFAULT NULL ,
  `photo_position` INT(10) NULL DEFAULT NULL ,
  `updated_time` TIMESTAMP NULL ,
  `created_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `user_category` VARCHAR(255) NULL DEFAULT NULL ,
  PRIMARY KEY (`photo_id`) ,
  INDEX `PHOTOS_FANPAGES_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `PHOTOS_FANPAGES_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fancrank`.`fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`posts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`posts` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`posts` (
  `post_id` VARCHAR(255) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  `post_user_category` VARCHAR(255) NULL DEFAULT NULL ,
  `post_message` TEXT NULL ,
  `post_privacy_descr` TEXT NULL DEFAULT NULL ,
  `post_privacy_value` VARCHAR(2000) NULL DEFAULT NULL ,
  `post_type` VARCHAR(255) NULL DEFAULT NULL ,
  `post_application_name` VARCHAR(100) NULL DEFAULT NULL ,
  `post_application_id` BIGINT(100) NULL DEFAULT NULL ,
  `post_comments_count` BIGINT(100) NULL DEFAULT NULL ,
  `post_likes_count` BIGINT(100) NULL DEFAULT NULL ,
  `updated_time` TIMESTAMP NULL ,
  `created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `postscol` VARCHAR(45) NULL ,
  PRIMARY KEY (`post_id`) ,
  INDEX `POSTS_FANPAGES_ID_FK` (`fanpage_id` ASC) ,
  CONSTRAINT `POSTS_FANPAGES_ID_FK`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fancrank`.`fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`fancrank_users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`fancrank_users` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`fancrank_users` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `fancrank_user_email` VARCHAR(255) NOT NULL ,
  `access_token` VARCHAR(1000) NOT NULL ,
  `updated_time` BIGINT(100) NULL ,
  `created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`facebook_user_id`) ,
  INDEX `FANCRANK_USER_FAN_FK` (`facebook_user_id` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`fanpage_admins`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`fanpage_admins` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`fanpage_admins` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `fanpage_id` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`facebook_user_id`, `fanpage_id`) ,
  INDEX `FANAPAGE_ADMIN_USER_FK` (`facebook_user_id` ASC) ,
  INDEX `fk_fanpage_admins_1` (`facebook_user_id` ASC) ,
  INDEX `fk_fanpage` (`fanpage_id` ASC) ,
  CONSTRAINT `fk_fanpage_admins_1`
    FOREIGN KEY (`facebook_user_id` )
    REFERENCES `fancrank`.`facebook_users` (`facebook_user_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_fanpage`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fancrank`.`fanpages` (`fanpage_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`users` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`users` (
  `user_id` BIGINT(100) NOT NULL ,
  `user_handle` VARCHAR(100) NOT NULL ,
  `user_first_name` VARCHAR(100) NOT NULL ,
  `user_last_name` VARCHAR(100) NOT NULL ,
  `user_avatar` VARCHAR(255) NOT NULL ,
  `user_email` VARCHAR(255) NOT NULL ,
  `user_access_token` VARCHAR(255) NOT NULL ,
  `user_gender` VARCHAR(45) NULL ,
  `user_locale` VARCHAR(100) NULL ,
  `user_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`user_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fancrank`.`queue`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`queue` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`queue` (
  `queue_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `queue_name` VARCHAR(100) NULL ,
  `timeout` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '30' ,
  PRIMARY KEY (`queue_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fancrank`.`message`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`message` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`message` (
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
    REFERENCES `fancrank`.`queue` (`queue_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fancrank`.`posts_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`posts_media` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`posts_media` (
  `post_id` VARCHAR(255) NOT NULL ,
  `post_type` VARCHAR(255) NULL ,
  `post_picture` VARCHAR(1000) NULL ,
  `post_link` VARCHAR(1000) NULL ,
  `post_source` VARCHAR(1000) NULL ,
  `post_name` VARCHAR(1000) NULL ,
  `post_caption` TEXT NULL ,
  `post_description` TEXT NULL ,
  `post_icon` VARCHAR(1000) NULL ,
  PRIMARY KEY (`post_id`) ,
  INDEX `fk_posts_media_1` (`post_id` ASC) ,
  CONSTRAINT `fk_posts_media_1`
    FOREIGN KEY (`post_id` )
    REFERENCES `fancrank`.`posts` (`post_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fancrank`.`fancrank_user_likes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`fancrank_user_likes` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`fancrank_user_likes` (
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `like_id` BIGINT(100) NOT NULL ,
  `like_category` VARCHAR(255) NOT NULL ,
  `like_name` VARCHAR(255) NOT NULL ,
  `created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`facebook_user_id`, `like_id`) ,
  INDEX `fk_fancrank_user_likes_facebook_user_id` (`facebook_user_id` ASC) ,
  UNIQUE INDEX `created_time_UNIQUE` (`created_time` ASC) ,
  CONSTRAINT `fk_fancrank_user_likes_facebook_user_id`
    FOREIGN KEY (`facebook_user_id` )
    REFERENCES `fancrank`.`fancrank_users` (`facebook_user_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fancrank`.`tags`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`tags` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`tags` (
  `fanpage_id` BIGINT(100) NOT NULL ,
  `facebook_user_id` BIGINT(100) NULL ,
  `facebook_user_name` VARCHAR(255) NOT NULL ,
  `photo_id` BIGINT(100) NOT NULL ,
  `tag_position_x` INT(100) NULL ,
  `tag_position_y` INT(100) NULL ,
  `created_time` TIMESTAMP NULL ,
  PRIMARY KEY (`fanpage_id`, `facebook_user_id`, `facebook_user_name`, `photo_id`) ,
  INDEX `TAGS_PHOTOS_FK` (`photo_id` ASC) ,
  CONSTRAINT `TAGS_PHOTOS_FK`
    FOREIGN KEY (`photo_id` )
    REFERENCES `fancrank`.`photos` (`photo_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fancrank`.`rankings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fancrank`.`rankings` ;

CREATE  TABLE IF NOT EXISTS `fancrank`.`rankings` (
  `fanpage_id` BIGINT(100) NOT NULL ,
  `type` VARCHAR(45) NOT NULL ,
  `facebook_user_id` BIGINT(100) NOT NULL ,
  `rank` BIGINT(100) NOT NULL ,
  `count` BIGINT(100) NOT NULL ,
  PRIMARY KEY (`fanpage_id`, `type`, `facebook_user_id`) ,
  INDEX `fk_rankings_1` (`fanpage_id` ASC) ,
  CONSTRAINT `fk_rankings_1`
    FOREIGN KEY (`fanpage_id` )
    REFERENCES `fancrank`.`fanpages` (`fanpage_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
