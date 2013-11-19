SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `sites`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sites` ;

CREATE  TABLE IF NOT EXISTS `sites` (
  `site_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `site_name` VARCHAR(255) NULL ,
  PRIMARY KEY (`site_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `modules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `modules` ;

CREATE  TABLE IF NOT EXISTS `modules` (
  `module_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `module_name` VARCHAR(45) NOT NULL ,
  `module_has_menu` TINYINT(1) NOT NULL DEFAULT 1 ,
  `module_menu_order` INT NULL DEFAULT NULL ,
  `module_is_default` TINYINT(1) NULL DEFAULT 0 COMMENT '1 => default module, required for  admin panel to work.\n\n0 => add on module, can be deleted.' ,
  `module_created` DATETIME NULL DEFAULT NULL ,
  `module_is_enabled` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`module_id`) ,
  UNIQUE INDEX `module_name_UNIQUE` (`module_name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `templates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `templates` ;

CREATE  TABLE IF NOT EXISTS `templates` (
  `temp_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `temp_module_name` VARCHAR(45) NULL DEFAULT NULL ,
  `temp_name` VARCHAR(255) NOT NULL COMMENT 'temp_name, temp_type & site_id form a composite unique key' ,
  `temp_type` VARCHAR(45) NOT NULL COMMENT 'Will contain keywords\n\'page\', \'includes\' Or \'module\' depending on type of template' ,
  `temp_head` LONGTEXT NULL COMMENT 'Will contain any js, css, scripts that may be required in the  <head></head> section of the html when this particular template is used. Mostly required for module templates.' ,
  `temp_html` LONGTEXT NULL DEFAULT NULL ,
  `temp_created` DATETIME NOT NULL ,
  `temp_modified` DATETIME NULL DEFAULT NULL ,
  `temp_description` TEXT NULL DEFAULT NULL ,
  `temp_is_visible` TINYINT(1) NOT NULL DEFAULT 1 ,
  `temp_site_id` BIGINT UNSIGNED NULL DEFAULT NULL ,
  PRIMARY KEY (`temp_id`) ,
  UNIQUE INDEX `temp_name_UNIQUE` (`temp_site_id` ASC, `temp_type` ASC, `temp_name` ASC, `temp_module_name` ASC) ,
  INDEX `fk_site_templates` (`temp_site_id` ASC) ,
  INDEX `fk_modules_templates` (`temp_module_name` ASC) ,
  CONSTRAINT `fk_site_templates`
    FOREIGN KEY (`temp_site_id` )
    REFERENCES `sites` (`site_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_modules_templates`
    FOREIGN KEY (`temp_module_name` )
    REFERENCES `modules` (`module_name` )
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pages` ;

CREATE  TABLE IF NOT EXISTS `pages` (
  `page_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `page_temp_id` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `page_name` VARCHAR(255) NOT NULL ,
  `page_slug` VARCHAR(255) NOT NULL ,
  `page_redirect` VARCHAR(255) NULL DEFAULT NULL ,
  `page_html` LONGTEXT NULL DEFAULT NULL ,
  `page_title` VARCHAR(255) NULL DEFAULT NULL ,
  `page_description` VARCHAR(255) NULL DEFAULT NULL ,
  `page_keywords` VARCHAR(255) NULL DEFAULT NULL ,
  `page_created` DATETIME NOT NULL ,
  `page_modified` DATETIME NULL ,
  `page_is_visible` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`page_id`) ,
  INDEX `fk_templates_pages` (`page_temp_id` ASC) ,
  UNIQUE INDEX `page_slug_UNIQUE` (`page_slug` ASC) ,
  CONSTRAINT `fk_templates_pages`
    FOREIGN KEY (`page_temp_id` )
    REFERENCES `templates` (`temp_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `tags`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tags` ;

CREATE  TABLE IF NOT EXISTS `tags` (
  `tag_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `tag_temp_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'tag_temp_id -- contains the template FOR the particular tag, NOT in which template this tag exists.' ,
  `tag_module_name` VARCHAR(45) NULL DEFAULT NULL ,
  `tag_keyword` TEXT NOT NULL COMMENT 'tag_keyword holds the final parse tag that will be stored in the template. It contains the tag component and the id component of the parseTag. Eg. \'content:article:10\'. In case only template is being created, then will store same as tag_name.' ,
  `tag_name` TEXT NOT NULL COMMENT 'tag_name will store the default tag name used while extracting from html. It does not contain the id component that would be added later. this is NOT a unique key. Eg. tag_name = \'content:article\'' ,
  `tag_data_id` VARCHAR(255) NULL DEFAULT NULL ,
  `tag_description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`tag_id`) ,
  INDEX `fk_modules_tags` (`tag_module_name` ASC) ,
  INDEX `fk_templates_tags` (`tag_temp_id` ASC) ,
  CONSTRAINT `fk_modules_tags`
    FOREIGN KEY (`tag_module_name` )
    REFERENCES `modules` (`module_name` )
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_templates_tags`
    FOREIGN KEY (`tag_temp_id` )
    REFERENCES `templates` (`temp_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Holds List of Tags\nColumn descriptions\n- keyword - specific ' /* comment truncated */;


-- -----------------------------------------------------
-- Table `resources`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `resources` ;

CREATE  TABLE IF NOT EXISTS `resources` (
  `resource_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `resource_name` VARCHAR(255) NOT NULL ,
  `resource_filetype` VARCHAR(10) NOT NULL ,
  `resource_uri` VARCHAR(255) NOT NULL COMMENT 'relative uri to access resources Eg. {site:root}/path/to/file.css\nor http://externalsite.com/file.css\n\ncannot be NULL\n' ,
  `resource_relative_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'this holds value from root folder ( i.e. folder containing index.php ).\n\nvalue can be NULL for external files' ,
  `resource_full_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'this is the filesystem path of the file starting from the server drive E.g c:\\some\\dir\\path\n\ncan be NULL if resource is an external file.' ,
  `resource_modified` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`resource_id`) ,
  UNIQUE INDEX `resource_path_UNIQUE` (`resource_full_path` ASC) ,
  UNIQUE INDEX `resource_uri_UNIQUE` (`resource_uri` ASC) ,
  UNIQUE INDEX `resource_relative_path_UNIQUE` (`resource_relative_path` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `links`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `links` ;

CREATE  TABLE IF NOT EXISTS `links` (
  `link_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `link_type` VARCHAR(45) NULL ,
  `link_module` VARCHAR(255) NULL COMMENT 'foreign key to module name' ,
  `link_tip` VARCHAR(255) NULL DEFAULT NULL COMMENT 'holds tool tip for link' ,
  `link_name` VARCHAR(255) NOT NULL COMMENT 'display name of link' ,
  `link_uri` VARCHAR(255) NOT NULL COMMENT 'can be relative (eg. /about, about/something ) \nOR\nabsolute ( http:// . . . , www. . .  )' ,
  `link_redirect` VARCHAR(255) NULL DEFAULT NULL COMMENT 'target link if current link should do a 404 redirect to another link\nrelative OR absolute' ,
  `lnk_is_active` TINYINT(1) NOT NULL DEFAULT 1 ,
  `link_slug` VARCHAR(45) NOT NULL COMMENT 'unique' ,
  PRIMARY KEY (`link_id`) ,
  UNIQUE INDEX `link_tagname_UNIQUE` (`link_name` ASC) ,
  UNIQUE INDEX `link_uri_UNIQUE` (`link_uri` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `users_auth`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users_auth` ;

CREATE  TABLE IF NOT EXISTS `users_auth` (
  `user_auth_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `user_auth_username` VARCHAR(20) NOT NULL ,
  `user_auth_email` VARCHAR(45) NOT NULL ,
  `user_auth_password` VARCHAR(128) NOT NULL ,
  `user_auth_act_key` VARCHAR(128) NULL DEFAULT NULL ,
  `user_auth_act_key_created` DATETIME NULL DEFAULT NULL COMMENT 'used to keep track of validity of activation key, keys become invalid after a certain amount of time ( php logic )' ,
  `user_auth_sec_question` VARCHAR(45) NULL DEFAULT NULL ,
  `user_auth_sec_answer` VARCHAR(45) NULL DEFAULT NULL ,
  `user_auth_created` DATETIME NOT NULL ,
  `user_auth_last_login` DATETIME NULL ,
  `user_auth_is_active` TINYINT(1) NULL DEFAULT 0 ,
  `user_auth_is_deleted` TINYINT(1) NULL DEFAULT 0 ,
  `user_auth_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'this contains a Readable name ( display name )' ,
  `user_auth_name_slug` VARCHAR(255) NULL COMMENT 'contains the user_auth_name in slug format' ,
  PRIMARY KEY (`user_auth_id`) ,
  UNIQUE INDEX `user_auth_username_UNIQUE` (`user_auth_username` ASC) ,
  UNIQUE INDEX `user_auth_name_slug_UNIQUE` (`user_auth_name_slug` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `temp_revisions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `temp_revisions` ;

CREATE  TABLE IF NOT EXISTS `temp_revisions` (
  `temp_revision_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `temp_revision_temp_id` BIGINT UNSIGNED NOT NULL ,
  `temp_revision_module_name` VARCHAR(45) NULL DEFAULT NULL ,
  `temp_revision_name` VARCHAR(255) NOT NULL ,
  `temp_revision_type` VARCHAR(45) NOT NULL ,
  `temp_revision_head` LONGTEXT NULL DEFAULT NULL ,
  `temp_revision_html` LONGTEXT NULL DEFAULT NULL ,
  `temp_revision_created` DATETIME NOT NULL ,
  `temp_revision_modified` DATETIME NULL DEFAULT NULL ,
  `temp_revision_is_visible` TINYINT(1) NOT NULL DEFAULT 1 ,
  `temp_revision_description` TEXT NULL DEFAULT NULL ,
  `temp_revision_is_edit` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = edit, 0 = insert' ,
  PRIMARY KEY (`temp_revision_id`) ,
  INDEX `fk_templates_revisions` (`temp_revision_temp_id` ASC) ,
  CONSTRAINT `fk_templates_revisions`
    FOREIGN KEY (`temp_revision_temp_id` )
    REFERENCES `templates` (`temp_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `page_revisions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `page_revisions` ;

CREATE  TABLE IF NOT EXISTS `page_revisions` (
  `page_revision_id` BIGINT NOT NULL AUTO_INCREMENT ,
  `page_revision_page_id` BIGINT UNSIGNED NULL ,
  `page_revision_temp_id` BIGINT UNSIGNED NULL ,
  `page_revision_name` VARCHAR(255) NOT NULL ,
  `page_revision_slug` VARCHAR(255) NOT NULL ,
  `page_revision_html` LONGTEXT NULL ,
  `page_revision_title` VARCHAR(255) NULL ,
  `page_revision_description` VARCHAR(255) NULL ,
  `page_revision_keywords` VARCHAR(255) NULL ,
  `page_revision_created` DATETIME NOT NULL ,
  `page_revision_modified` DATETIME NULL ,
  `page_revision_is_visible` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`page_revision_id`) ,
  INDEX `fk_pages_revisions` (`page_revision_page_id` ASC) ,
  CONSTRAINT `fk_pages_revisions`
    FOREIGN KEY (`page_revision_page_id` )
    REFERENCES `pages` (`page_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `blocks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `blocks` ;

CREATE  TABLE IF NOT EXISTS `blocks` (
  `block_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `block_temp_id` BIGINT UNSIGNED NOT NULL COMMENT 'block_temp_id -- indicates the template inside which the current block resides' ,
  `block_name` VARCHAR(255) NOT NULL COMMENT 'holds names like Eg. block:1, block:header' ,
  `block_tag_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Required for content which is inside an includes template. Can also be used later to revert to default content in case particular content is deleted.' ,
  `block_default_html` TEXT NULL DEFAULT NULL COMMENT 'holds original html that was present inside extract tag during  importing.' ,
  INDEX `fk_templates_blocks` (`block_temp_id` ASC) ,
  INDEX `fk_tags_blocks` (`block_tag_id` ASC) ,
  PRIMARY KEY (`block_temp_id`, `block_name`) ,
  UNIQUE INDEX `block_id_UNIQUE` (`block_id` ASC) ,
  CONSTRAINT `fk_templates_blocks`
    FOREIGN KEY (`block_temp_id` )
    REFERENCES `templates` (`temp_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tags_blocks`
    FOREIGN KEY (`block_tag_id` )
    REFERENCES `tags` (`tag_id` )
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `page_blocks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `page_blocks` ;

CREATE  TABLE IF NOT EXISTS `page_blocks` (
  `page_blocks_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `page_blocks_page_id` BIGINT UNSIGNED NOT NULL ,
  `page_blocks_temp_id` BIGINT UNSIGNED NOT NULL ,
  `page_blocks_block_id` BIGINT UNSIGNED NOT NULL ,
  `page_blocks_tag_id` BIGINT UNSIGNED NULL ,
  PRIMARY KEY (`page_blocks_page_id`, `page_blocks_temp_id`, `page_blocks_block_id`) ,
  INDEX `fk_pages_page_blocks` (`page_blocks_page_id` ASC) ,
  INDEX `fk_templates_page_blocks` (`page_blocks_temp_id` ASC) ,
  INDEX `fk_blocks_page_blocks` (`page_blocks_block_id` ASC) ,
  INDEX `fk_tags_page_blocks` (`page_blocks_tag_id` ASC) ,
  UNIQUE INDEX `page_blocks_id_UNIQUE` (`page_blocks_id` ASC) ,
  CONSTRAINT `fk_pages_page_blocks`
    FOREIGN KEY (`page_blocks_page_id` )
    REFERENCES `pages` (`page_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_templates_page_blocks`
    FOREIGN KEY (`page_blocks_temp_id` )
    REFERENCES `templates` (`temp_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_blocks_page_blocks`
    FOREIGN KEY (`page_blocks_block_id` )
    REFERENCES `blocks` (`block_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tags_page_blocks`
    FOREIGN KEY (`page_blocks_tag_id` )
    REFERENCES `tags` (`tag_id` )
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `settings` ;

CREATE  TABLE IF NOT EXISTS `settings` (
  `set_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `set_category` VARCHAR(45) NULL DEFAULT 'general' COMMENT 'for simple classification of site related settings. Eg. email, general . . etc\n' ,
  `set_name` VARCHAR(255) NOT NULL COMMENT 'Human readable name for setting, this will be displayed on front end' ,
  `set_key` VARCHAR(255) NOT NULL ,
  `set_value` TEXT NULL DEFAULT NULL ,
  `set_options` TEXT NULL DEFAULT NULL COMMENT 'string options seperated by pipe ( | )' ,
  `set_description` TEXT NULL DEFAULT NULL COMMENT 'description for setting, will be displayed on front end.' ,
  `set_data_type` ENUM( 'string', 'bool', 'array' ) NULL COMMENT 'value either \'string\' OR \'list\'' ,
  PRIMARY KEY (`set_id`) ,
  UNIQUE INDEX `UNIQUE_category_key` (`set_category` ASC, `set_key` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `ci_sessions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ci_sessions` ;

CREATE  TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` VARCHAR(40) NOT NULL DEFAULT '0' ,
  `ip_address` VARCHAR(45) NOT NULL DEFAULT '0' ,
  `user_agent` VARCHAR(120) NOT NULL DEFAULT '' ,
  `last_activity` INT(10) NOT NULL DEFAULT 0 ,
  `user_data` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`session_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `menus_admin`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `menus_admin` ;

CREATE  TABLE IF NOT EXISTS `menus_admin` (
  `menu_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `menu_parent_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Foreign key refering to menu_name.' ,
  `menu_module_id` BIGINT UNSIGNED NULL ,
  `menu_category` VARCHAR(50) NULL DEFAULT NULL ,
  `menu_title` VARCHAR(100) NOT NULL ,
  `menu_href` VARCHAR(250) NULL DEFAULT NULL ,
  `menu_target` VARCHAR(250) NULL DEFAULT '_self' COMMENT 'target attribute for anchor tag' ,
  `menu_html` TEXT NULL ,
  `menu_description` TEXT NULL DEFAULT NULL ,
  `menu_order` INT(3) NOT NULL DEFAULT 1 ,
  `menu_is_visible` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`menu_id`) ,
  UNIQUE INDEX `UNIQUE_category_name` (`menu_category` ASC, `menu_title` ASC) ,
  INDEX `fk_menus_menus` (`menu_parent_id` ASC) ,
  CONSTRAINT `fk_menus_menus`
    FOREIGN KEY (`menu_parent_id` )
    REFERENCES `menus_admin` (`menu_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `gallery_themes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gallery_themes` ;

CREATE  TABLE IF NOT EXISTS `gallery_themes` (
  `gallery_theme_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `gallery_theme_name` VARCHAR(255) NOT NULL ,
  `gallery_theme_resource_uri` VARCHAR(255) NULL DEFAULT NULL COMMENT 'relative to site root\n' ,
  `gallery_theme_scripts` TEXT NULL DEFAULT NULL ,
  `gallery_theme_version` VARCHAR(10) NULL DEFAULT NULL ,
  PRIMARY KEY (`gallery_theme_id`) ,
  UNIQUE INDEX `gallery_theme_name_UNIQUE` (`gallery_theme_name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `gallery_templates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gallery_templates` ;

CREATE  TABLE IF NOT EXISTS `gallery_templates` (
  `gallery_template_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `gallery_template_theme_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'stores name of theme for current template. part of composite unique key ( theme, type, name )' ,
  `gallery_template_type` VARCHAR(100) NOT NULL COMMENT 'holds predifined values of ( categories, category, image ). part of composite unique key ( theme, type, name )' ,
  `gallery_template_name` VARCHAR(100) NULL DEFAULT NULL COMMENT 'contains specific name of template. part of composite unique key ( theme, type, name )' ,
  `gallery_template_scripts` TEXT NULL DEFAULT NULL COMMENT 'This is incase template requires specific js or css inline to execute.' ,
  `gallery_template_html` TEXT NULL DEFAULT NULL ,
  `gallery_template_created` DATETIME NULL DEFAULT NULL ,
  `gallery_template_modified` DATETIME NULL DEFAULT NULL ,
  `gallery_template_is_visible` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`gallery_template_id`) ,
  INDEX `fk_gallery-themes_gallery-templates` (`gallery_template_theme_name` ASC) ,
  UNIQUE INDEX `UNIQUE_theme_type_template` (`gallery_template_theme_name` ASC, `gallery_template_type` ASC, `gallery_template_name` ASC) ,
  CONSTRAINT `fk_gallery-themes_gallery-templates`
    FOREIGN KEY (`gallery_template_theme_name` )
    REFERENCES `gallery_themes` (`gallery_theme_name` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `gallery_items`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gallery_items` ;

CREATE  TABLE IF NOT EXISTS `gallery_items` (
  `gallery_item_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `gallery_item_parent_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'for images -- contains id of category or album. NULL if current item is a category.' ,
  `gallery_item_cover_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'for categories -- holds id of image to use as cover. If image -- value = NULL' ,
  `gallery_item_type` ENUM( 'category', 'image' ) NOT NULL COMMENT 'enum data type. options ( category, image )' ,
  `gallery_item_name` VARCHAR(255) NULL DEFAULT NULL ,
  `gallery_item_name_url` TEXT NULL DEFAULT NULL COMMENT 'this is to hold href values, incase the category/image needs to be a link to another page.' ,
  `gallery_item_desc` TEXT NULL DEFAULT NULL ,
  `gallery_item_alt` TEXT NULL ,
  `gallery_item_uri` TEXT NULL DEFAULT NULL ,
  `gallery_item_uri_thumb` TEXT NULL DEFAULT NULL ,
  `gallery_item_order` INT NULL DEFAULT 1 ,
  `gallery_item_created` DATETIME NULL DEFAULT NULL ,
  `gallery_item_modified` DATETIME NULL DEFAULT NULL ,
  `gallery_item_is_visible` TINYINT(1) NULL DEFAULT 1 ,
  PRIMARY KEY (`gallery_item_id`) ,
  INDEX `fk_items-id_items-categ-id` (`gallery_item_parent_id` ASC) ,
  INDEX `fk_items-id_items-cover-id` (`gallery_item_cover_id` ASC) ,
  CONSTRAINT `fk_items-id_items-categ-id`
    FOREIGN KEY (`gallery_item_parent_id` )
    REFERENCES `gallery_items` (`gallery_item_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_items-id_items-cover-id`
    FOREIGN KEY (`gallery_item_cover_id` )
    REFERENCES `gallery_items` (`gallery_item_id` )
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `gallery_resources`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gallery_resources` ;

CREATE  TABLE IF NOT EXISTS `gallery_resources` (
  `gallery_resource_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `gallery_resource_name` VARCHAR(255) NULL ,
  `gallery_resource_filetype` VARCHAR(10) NULL ,
  `gallery_resource_uri` TEXT NULL ,
  `gallery_resource_theme_name` VARCHAR(255) NULL ,
  PRIMARY KEY (`gallery_resource_id`) ,
  INDEX `fk_gallery-themes_gallery-resources` (`gallery_resource_theme_name` ASC) ,
  CONSTRAINT `fk_gallery-themes_gallery-resources`
    FOREIGN KEY (`gallery_resource_theme_name` )
    REFERENCES `gallery_themes` (`gallery_theme_name` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `events`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `events` ;

CREATE  TABLE IF NOT EXISTS `events` (
  `event_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `event_name` VARCHAR(255) NOT NULL COMMENT 'Title name of Event ( human readable )' ,
  `event_slug` VARCHAR(255) NOT NULL COMMENT 'slug form of name' ,
  `event_description` TEXT NULL DEFAULT NULL COMMENT 'plain text OR html description of event' ,
  `event_venue` VARCHAR(255) NULL DEFAULT NULL ,
  `event_start` DATETIME NOT NULL COMMENT 'start datetime value at GMT' ,
  `event_end` DATETIME NULL DEFAULT NULL COMMENT 'end datetime value at GMT' ,
  PRIMARY KEY (`event_id`) ,
  UNIQUE INDEX `event_slug_UNIQUE` (`event_slug` ASC, `event_start` ASC, `event_venue` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Contains events for events module';


-- -----------------------------------------------------
-- Table `videos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `videos` ;

CREATE  TABLE IF NOT EXISTS `videos` (
  `video_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `video_ref_id` VARCHAR(255) NULL COMMENT 'id for external site ( Eg. youtube video id )' ,
  `video_title` VARCHAR(255) NULL COMMENT 'Title or name of video' ,
  `video_description` TEXT NULL COMMENT 'description of video, html OR plain text' ,
  `video_image_url` TEXT NULL COMMENT 'url of display or cover image' ,
  `video_script` TEXT NULL COMMENT 'video url or iframe scripts' ,
  `video_order` INT NULL COMMENT 'order in which videos will be organized' ,
  `video_is_visible` TINYINT(1) NULL COMMENT 'if video should be displayed or not on front end' ,
  PRIMARY KEY (`video_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `discography_categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `discography_categories` ;

CREATE  TABLE IF NOT EXISTS `discography_categories` (
  `discography_categ_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `discography_categ_name` VARCHAR(255) NOT NULL ,
  `discography_categ_slug` VARCHAR(255) NOT NULL ,
  `discography_categ_buy_url` TEXT NULL DEFAULT NULL COMMENT 'any internal or external link' ,
  `discography_categ_download_url` TEXT NULL DEFAULT NULL COMMENT 'any internal or external link' ,
  `discography_categ_image_url` TEXT NULL DEFAULT NULL COMMENT 'any internal or external image url' ,
  `discography_categ_description` TEXT NULL DEFAULT NULL COMMENT 'description' ,
  `discography_categ_is_visible` TINYINT(1) NULL DEFAULT 1 COMMENT 'bool 1 or 0' ,
  `discography_categ_created` DATETIME NULL COMMENT 'creation date yyyy-mm-dd hh:mm:ss' ,
  `discography_categ_order` INT NULL COMMENT 'order of category' ,
  PRIMARY KEY (`discography_categ_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `discography_items`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `discography_items` ;

CREATE  TABLE IF NOT EXISTS `discography_items` (
  `discography_item_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `discography_item_parent_id` BIGINT UNSIGNED NOT NULL COMMENT 'foreign_key ( discography_categ_id )' ,
  `discography_item_name` VARCHAR(255) NOT NULL ,
  `discography_item_slug` VARCHAR(255) NOT NULL ,
  `discography_item_description` LONGTEXT NULL DEFAULT NULL ,
  `discography_item_order` INT NULL ,
  `discography_item_is_visible` TINYINT(1) NULL DEFAULT 1 ,
  PRIMARY KEY (`discography_item_id`) ,
  INDEX `fk_discography_categs_items` (`discography_item_parent_id` ASC) ,
  CONSTRAINT `fk_discography_categs_items`
    FOREIGN KEY (`discography_item_parent_id` )
    REFERENCES `discography_categories` (`discography_categ_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `contents`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `contents` ;

CREATE  TABLE IF NOT EXISTS `contents` (
  `content_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `content_type` VARCHAR(45) NULL DEFAULT NULL ,
  `content_uid` VARCHAR(255) NULL DEFAULT NULL COMMENT 'reserved for future use, incase content needs to be refered using string instead of ids' ,
  `content_data` LONGTEXT NULL DEFAULT NULL ,
  `content_created` DATETIME NOT NULL ,
  `content_modified` DATETIME NULL ,
  `content_is_visible` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`content_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `comments_meta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `comments_meta` ;

CREATE  TABLE IF NOT EXISTS `comments_meta` (
  `comm_meta_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key' ,
  `comm_meta_module_name` VARCHAR(45) NULL DEFAULT NULL COMMENT 'module name for comments. foreign key to modules table.' ,
  `comm_meta_sub_type` VARCHAR(255) NULL DEFAULT NULL COMMENT 'sub categorization, if required by module.' ,
  `comm_meta_ref_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'id for specific data type. this can usually be the primary key or unique key of the data type.' ,
  `comm_meta_template` VARCHAR(255) NULL DEFAULT NULL COMMENT 'template name of view, for afternate view, null implies default template.' ,
  `comm_meta_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Name to identify comment, not important except to understand what the comment is for.' ,
  `comm_meta_is_visible` TINYINT(1) NULL DEFAULT 1 COMMENT 'to hide comments.' ,
  `comm_meta_description` TEXT NULL DEFAULT NULL COMMENT 'Description for particular comments, not needed, can be used for future use.' ,
  `comm_meta_is_override` TINYINT(1) NULL DEFAULT 0 COMMENT 'to decide whether to use the html or display comments from comments table.' ,
  `comm_meta_html` TEXT NULL DEFAULT NULL COMMENT 'Overriding html, this will display instead of regular comenting system. Usually is this is present, then the underlying coments should not display' ,
  PRIMARY KEY (`comm_meta_id`) ,
  UNIQUE INDEX `Comments__module_subtype_refid_UNIQUE` (`comm_meta_module_name` ASC, `comm_meta_sub_type` ASC, `comm_meta_ref_id` ASC) ,
  INDEX `fk_comments_module` (`comm_meta_module_name` ASC) ,
  CONSTRAINT `fk_comments_module`
    FOREIGN KEY (`comm_meta_module_name` )
    REFERENCES `modules` (`module_name` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'used to associate any a list of comments for any type of dat' /* comment truncated */;


-- -----------------------------------------------------
-- Table `comments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `comments` ;

CREATE  TABLE IF NOT EXISTS `comments` (
  `comm_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `comm_meta_id` BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to comments_meta table' ,
  `comm_parent_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'foreign key to self table, for heirarchy of comments' ,
  `comm_user_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'foreign key to users table. \nNOTE: if this is specified, then DO not use name, email, url, image from this table, use from users table.' ,
  `comm_name` VARCHAR(255) NULL DEFAULT 'Anonymous user' COMMENT 'user display name' ,
  `comm_email` VARCHAR(255) NULL DEFAULT NULL COMMENT 'email address' ,
  `comm_url` VARCHAR(255) NULL DEFAULT NULL COMMENT 'website url, ( if required )' ,
  `comm_image` VARCHAR(255) NULL DEFAULT NULL COMMENT 'may contain user image, if user is not registered., NULL implies use default' ,
  `comm_message` TEXT NOT NULL COMMENT 'users message or comment' ,
  `comm_created` DATETIME NOT NULL COMMENT 'gmt time of comment creation' ,
  `comm_is_visible` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'if this comment should be display or not' ,
  `comm_ip_address` VARCHAR(45) NULL DEFAULT NULL COMMENT 'ip address, can support ipv6 strings as well' ,
  `comm_status` VARCHAR(45) NOT NULL DEFAULT 'new' COMMENT 'values (  new, pending, approved, blocked  )' ,
  PRIMARY KEY (`comm_id`) ,
  INDEX `fk_comments_comm-meta` (`comm_meta_id` ASC) ,
  INDEX `fk_comments_comments` (`comm_parent_id` ASC) ,
  INDEX `fk_comments_user` (`comm_user_id` ASC) ,
  CONSTRAINT `fk_comments_comm-meta`
    FOREIGN KEY (`comm_meta_id` )
    REFERENCES `comments_meta` (`comm_meta_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_comments`
    FOREIGN KEY (`comm_parent_id` )
    REFERENCES `comments` (`comm_id` )
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user`
    FOREIGN KEY (`comm_user_id` )
    REFERENCES `users_auth` (`user_auth_id` )
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Actualy comments table,\nthis table connects 	a user and his/' /* comment truncated */;


-- -----------------------------------------------------
-- Table `blog_categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `blog_categories` ;

CREATE  TABLE IF NOT EXISTS `blog_categories` (
  `blog_categ_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `blog_categ_parent_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT '(for future use) - In case if categ heirarchy is needed' ,
  `blog_categ_name` VARCHAR(255) NOT NULL ,
  `blog_categ_slug` VARCHAR(100) NOT NULL COMMENT 'unique key' ,
  `blog_categ_description` LONGTEXT NULL DEFAULT NULL ,
  `blog_categ_created` DATETIME NULL DEFAULT NULL ,
  `blog_categ_modified` DATETIME NULL DEFAULT NULL ,
  `blog_categ_is_visible` TINYINT(1) NULL DEFAULT 1 ,
  `blog_categ_is_comments` TINYINT(1) NULL DEFAULT 1 ,
  `blog_categ_order` BIGINT UNSIGNED NULL DEFAULT 3 COMMENT 'Order starts from default 3, so that it does not come before special categories \'uncategorized\' and \'featured\'' ,
  `blog_categ_is_special` TINYINT(1) NULL DEFAULT 0 COMMENT '1 = special categ, cannot be deleted or modified.\n0 = user created categ, can be deleted or modified' ,
  PRIMARY KEY (`blog_categ_id`) ,
  UNIQUE INDEX `blog_categ_slug_UNIQUE` (`blog_categ_slug` ASC) ,
  INDEX `fk-blog-categ_blog-categ` (`blog_categ_parent_id` ASC) ,
  CONSTRAINT `fk-blog-categ_blog-categ`
    FOREIGN KEY (`blog_categ_parent_id` )
    REFERENCES `blog_categories` (`blog_categ_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `blog_posts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `blog_posts` ;

CREATE  TABLE IF NOT EXISTS `blog_posts` (
  `blog_post_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `blog_post_author_username` VARCHAR(50) NULL DEFAULT NULL ,
  `blog_post_title` VARCHAR(255) NOT NULL ,
  `blog_post_slug` VARCHAR(255) NOT NULL ,
  `blog_post_body` LONGTEXT NULL DEFAULT NULL ,
  `blog_post_created` DATETIME NULL DEFAULT NULL ,
  `blog_post_modified` DATETIME NULL DEFAULT NULL ,
  `blog_post_status` VARCHAR(50) NULL DEFAULT NULL COMMENT 'contains values ( \'draft\', \'published\' )' ,
  `blog_post_is_comments` TINYINT(1) NULL DEFAULT 1 ,
  PRIMARY KEY (`blog_post_id`) ,
  UNIQUE INDEX `blog_post_slug_UNIQUE` (`blog_post_slug` ASC) ,
  UNIQUE INDEX `blog_post_title_UNIQUE` (`blog_post_title` ASC) ,
  INDEX `fk_blog_post_author` (`blog_post_author_username` ASC) ,
  INDEX `index_blog_post_created` (`blog_post_created` ASC) ,
  CONSTRAINT `fk_blog_post_author`
    FOREIGN KEY (`blog_post_author_username` )
    REFERENCES `users_auth` (`user_auth_username` )
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `blog_tags`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `blog_tags` ;

CREATE  TABLE IF NOT EXISTS `blog_tags` (
  `blog_tag_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `blog_tag_name` VARCHAR(50) NULL ,
  `blog_tag_slug` VARCHAR(50) NULL ,
  `blog_tag_description` LONGTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`blog_tag_id`) ,
  UNIQUE INDEX `blog_tag_slug_UNIQUE` (`blog_tag_slug` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `blog_post_tags`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `blog_post_tags` ;

CREATE  TABLE IF NOT EXISTS `blog_post_tags` (
  `blog_post_tag_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `blog_post_tag_post_id` BIGINT UNSIGNED NULL ,
  `blog_post_tag_tag_slug` VARCHAR(50) NULL ,
  PRIMARY KEY (`blog_post_tag_id`) ,
  INDEX `fk_blog_post-tags_posts` (`blog_post_tag_post_id` ASC) ,
  INDEX `fk_blog_post-tags_tags` (`blog_post_tag_tag_slug` ASC) ,
  CONSTRAINT `fk_blog_post-tags_posts`
    FOREIGN KEY (`blog_post_tag_post_id` )
    REFERENCES `blog_posts` (`blog_post_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_blog_post-tags_tags`
    FOREIGN KEY (`blog_post_tag_tag_slug` )
    REFERENCES `blog_tags` (`blog_tag_slug` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `blog_post_categs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `blog_post_categs` ;

CREATE  TABLE IF NOT EXISTS `blog_post_categs` (
  `blog_post_categ_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `blog_post_categ_categ_slug` VARCHAR(100) NULL ,
  `blog_post_categ_post_id` BIGINT UNSIGNED NULL ,
  PRIMARY KEY (`blog_post_categ_id`) ,
  INDEX `fk_blog_post-categ_categs` (`blog_post_categ_categ_slug` ASC) ,
  INDEX `fk_blog_post-categ_post_id` (`blog_post_categ_post_id` ASC) ,
  UNIQUE INDEX `UNIQUE_blog-post-categs_slug_post-id` (`blog_post_categ_categ_slug` ASC, `blog_post_categ_post_id` ASC) ,
  CONSTRAINT `fk_blog_post-categ_categs`
    FOREIGN KEY (`blog_post_categ_categ_slug` )
    REFERENCES `blog_categories` (`blog_categ_slug` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_blog_post-categ_post_id`
    FOREIGN KEY (`blog_post_categ_post_id` )
    REFERENCES `blog_posts` (`blog_post_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `blog_post_meta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `blog_post_meta` ;

CREATE  TABLE IF NOT EXISTS `blog_post_meta` (
  `blog_post_meta_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `blog_post_meta_post_id` BIGINT UNSIGNED NULL ,
  `blog_post_meta_key` VARCHAR(45) NOT NULL ,
  `blog_post_meta_value` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`blog_post_meta_id`) ,
  INDEX `fk_blog_post-meta_posts` (`blog_post_meta_post_id` ASC) ,
  UNIQUE INDEX `blog_posts_meta_key_UNIQUE` (`blog_post_meta_post_id` ASC, `blog_post_meta_key` ASC) ,
  CONSTRAINT `fk_blog_post-meta_posts`
    FOREIGN KEY (`blog_post_meta_post_id` )
    REFERENCES `blog_posts` (`blog_post_id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'This table will hold any key-value pair that may be required' /* comment truncated */;


-- -----------------------------------------------------
-- Table `blog_post_revisions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `blog_post_revisions` ;

CREATE  TABLE IF NOT EXISTS `blog_post_revisions` (
  `blog_post_rev` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  PRIMARY KEY (`blog_post_rev`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `users_meta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users_meta` ;

CREATE  TABLE IF NOT EXISTS `users_meta` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(20) NOT NULL ,
  `key` VARCHAR(255) NULL ,
  `value` LONGTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `UNIQUE_user-meta_username-key` (`username` ASC, `key` ASC) ,
  INDEX `fk_users-auth_users-meta` (`username` ASC) ,
  CONSTRAINT `fk_users-auth_users-meta`
    FOREIGN KEY (`username` )
    REFERENCES `users_auth` (`user_auth_username` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'this table contains additional information of users, accordi' /* comment truncated */;


DELIMITER $$

DROP TRIGGER IF EXISTS `trig_page_revision_update` $$


CREATE TRIGGER trig_page_revision_update AFTER UPDATE 
ON `pages` FOR EACH ROW 
INSERT INTO `page_revisions` ( 
`page_revision_id`,
`page_revision_page_id`, 
`page_revision_temp_id`, 
`page_revision_name`, 
`page_revision_slug`, 
`page_revision_html`, 
`page_revision_title`, 
`page_revision_description`,
`page_revision_keywords`, 
`page_revision_created`, 
`page_revision_modified`, 
`page_revision_is_visible`
) 
VALUES (
NULL,
NEW.`page_id`, 
NEW.`page_temp_id`, 
NEW.`page_name`, 
NEW.`page_slug`, 
NEW.`page_html`, 
NEW.`page_title`, 
NEW.`page_description`,
NEW.`page_keywords`, 
NEW.`page_created`, 
NEW.`page_modified`, 
NEW.`page_is_visible`
);

$$


DROP TRIGGER IF EXISTS `trig_page_revision_insert` $$


CREATE TRIGGER trig_page_revision_insert AFTER INSERT 
ON `pages` FOR EACH ROW 
INSERT INTO `page_revisions` ( 
`page_revision_id`,
`page_revision_page_id`, 
`page_revision_temp_id`, 
`page_revision_name`, 
`page_revision_slug`,
`page_revision_html`, 
`page_revision_title`, 
`page_revision_description`,
`page_revision_keywords`, 
`page_revision_created`, 
`page_revision_modified`, 
`page_revision_is_visible`
) 
VALUES (
NULL,
NEW.`page_id`, 
NEW.`page_temp_id`, 
NEW.`page_name`, 
NEW.`page_slug`,
NEW.`page_html`, 
NEW.`page_title`, 
NEW.`page_description`,
NEW.`page_keywords`, 
NEW.`page_created`, 
NEW.`page_modified`, 
NEW.`page_is_visible`
);

$$


DELIMITER ;

DELIMITER $$

DROP TRIGGER IF EXISTS `trig_temp_revision_update` $$


CREATE TRIGGER trig_temp_revision_update AFTER UPDATE 
ON `templates` FOR EACH ROW 
INSERT INTO `temp_revisions` (
`temp_revision_id`,
`temp_revision_temp_id`,
`temp_revision_module_name`,
`temp_revision_name`, 
`temp_revision_type`, 
`temp_revision_head`,
`temp_revision_html`, 
`temp_revision_created`,
`temp_revision_modified`,
`temp_revision_is_visible`, 
`temp_revision_description`,
`temp_revision_is_edit`
) 
VALUES (
NULL, 
NEW.temp_id, 
NEW.temp_module_name,
NEW.temp_name, 
NEW.temp_type, 
NEW.temp_head,
NEW.temp_html, 
NEW.temp_created, 
NEW.temp_modified, 
NEW.temp_is_visible, 
NEW.temp_description, 
1
);

$$


DROP TRIGGER IF EXISTS `trig_temp_revision_insert` $$


CREATE TRIGGER trig_temp_revision_insert AFTER INSERT 
ON `templates` FOR EACH ROW 
INSERT INTO `temp_revisions` (
`temp_revision_id`,
`temp_revision_temp_id`,
`temp_revision_module_name`,
`temp_revision_name`, 
`temp_revision_type`, 
`temp_revision_head`,
`temp_revision_html`, 
`temp_revision_created`,
`temp_revision_modified`,
`temp_revision_is_visible`, 
`temp_revision_description`,
`temp_revision_is_edit`
) 
VALUES (
NULL, 
NEW.temp_id, 
NEW.temp_module_name,
NEW.temp_name, 
NEW.temp_type, 
NEW.temp_head,
NEW.temp_html, 
NEW.temp_created, 
NEW.temp_modified, 
NEW.temp_is_visible, 
NEW.temp_description, 
0 
);

$$


DELIMITER ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `sites`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `sites` (`site_id`, `site_name`) VALUES (NULL, 'default');

COMMIT;

-- -----------------------------------------------------
-- Data for table `modules`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'import', 1, 4, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'page', 1, 1, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'includes', 0, NULL, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'site', 0, NULL, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'content', 1, 2, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'template', 1, 3, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'gallery', 1, 5, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'resource', 1, 6, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'events', 1, 7, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'videos', 1, 8, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'discography', 1, 9, NULL, NULL, 1);
INSERT INTO `modules` (`module_id`, `module_name`, `module_has_menu`, `module_menu_order`, `module_is_default`, `module_created`, `module_is_enabled`) VALUES (NULL, 'blog', 1, 10, NULL, NULL, 1);

COMMIT;

-- -----------------------------------------------------
-- Data for table `users_auth`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `users_auth` (`user_auth_id`, `user_auth_username`, `user_auth_email`, `user_auth_password`, `user_auth_act_key`, `user_auth_act_key_created`, `user_auth_sec_question`, `user_auth_sec_answer`, `user_auth_created`, `user_auth_last_login`, `user_auth_is_active`, `user_auth_is_deleted`, `user_auth_name`, `user_auth_name_slug`) VALUES (1, 'admin', 'encubetech@gmail.com', 'c7ad44cbad762a5da0a452f9e854fdc1e0e7a52a38015f23f3eab1d80b931dd472634dfac71cd34ebc35d16ab7fb8a90c81f975113d6c7538dc69dd8de9077ec', NULL, NULL, 'What is the name of the company ?', 'encubetech', '2011-08-22 08:31:45', NULL, 1, 0, 'Admin', 'admin');

COMMIT;

-- -----------------------------------------------------
-- Data for table `settings`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'datetime', 'Timezone', 'timezone', 'UTC', NULL, 'Local time zone', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'general', 'Site name', 'site_name', 'Site Name', NULL, 'Name of site', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'general', 'Home page', 'home_page', 'index', NULL, 'Default page for root domain', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'From name', 'from_name', 'Admin', NULL, 'Name of sender for outgoing mails from system', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'Bcc', 'bcc', NULL, NULL, 'Set email addresses that will get a copy of every mail sent by system', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'Host', 'smtp_host', NULL, NULL, NULL, 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'Port', 'smtp_port', NULL, NULL, NULL, 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'Username', 'smtp_username', NULL, NULL, NULL, 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'Password', 'smtp_password', NULL, NULL, NULL, 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'Charset', 'smtp_charset', 'UTF-8', NULL, NULL, 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'SMTP', 'smtp', '0', NULL, NULL, 'bool');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'general', '404 Page Not Found', 'page_404', '404', NULL, 'The Page to be displayed in case a url for the site does not exist', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'Contact email', 'contact_email', NULL, NULL, 'All emails from users, forms, notification, etc will be sent to this address', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'datetime', 'Use DST', 'dst_used', '0', NULL, 'To use daylight savings time', 'bool');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'datetime', 'DST Offset', 'dst_offset', '1', NULL, 'Default offset ( in hours ) to use during dst', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'datetime', 'Date Format', 'format_date', 'Y-m-d', NULL, 'General format used for displaying dates', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'datetime', 'Time format', 'format_time', 'g:i A', NULL, 'General format used for displaying time', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'general', 'Analytics Code', 'analytics', NULL, NULL, 'enter code for site analytics', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'email', 'Server Email', 'server_email', NULL, NULL, 'Email address to use while sending mails to users', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Uri Prefix', 'uri_prefix', 'blog', NULL, 'Default prefix to identify that url is of type blog', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Permalink Structure', 'permalink', '/%category%/%post%/', NULL, 'Permalink structure for post type urls', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Excerpt Word Count', 'excerpt_word_count', '100', NULL, 'Default word count for excerpts', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Default Header', 'default_header', NULL, NULL, 'Default header for posts', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Default Footer', 'default_footer', NULL, NULL, 'Default footer for posts', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Default Ad Script', 'ad_script', NULL, NULL, 'Default ad script, use {ad} in posts to display ad anywhere in post', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Allow Comments', 'allow_comments', '1', NULL, 'universally enable or disable commenting for blog', 'bool');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Limit', 'limit', '10', NULL, 'Number of posts or items to display at one time', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'blog', 'Default View', 'default_view', 'category/featured', NULL, 'Default blog view', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'installation', 'Site', 'site_installed', NULL, NULL, 'Shows if site has been installed or not', 'bool');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'url', 'Module Url Prefix', 'module_url_prefixes', NULL, NULL, 'List of url prefixes for modules that support unique Urls', 'array');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'gallery', 'Current Theme', 'current_theme', 'default', NULL, 'Current Gallery theme to use for display of images', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'gallery', 'Thumbnail Width', 'thumbnail_width', '80', NULL, 'Default thumnail width to use in pixels', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'gallery', 'Thumbnail Height', 'thumbnail_height', '60', NULL, 'Default thumnail height to use in pixels', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'gallery', 'Default Category', 'default_category', 'uncategorized', NULL, 'Default category for image uploads', 'string');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'gallery', 'Use Themes', 'use_theme', '0', NULL, 'Use Gallery themes in website', 'bool');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'general', 'Enable Comments', 'enable_comments', '1', NULL, 'Allow site wide comments', 'bool');
INSERT INTO `settings` (`set_id`, `set_category`, `set_name`, `set_key`, `set_value`, `set_options`, `set_description`, `set_data_type`) VALUES (NULL, 'general', 'Comments Username', 'comments_username', NULL, NULL, 'Username for comments', 'string');

COMMIT;

-- -----------------------------------------------------
-- Data for table `gallery_themes`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `gallery_themes` (`gallery_theme_id`, `gallery_theme_name`, `gallery_theme_resource_uri`, `gallery_theme_scripts`, `gallery_theme_version`) VALUES (NULL, 'default', NULL, NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `gallery_templates`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `gallery_templates` (`gallery_template_id`, `gallery_template_theme_name`, `gallery_template_type`, `gallery_template_name`, `gallery_template_scripts`, `gallery_template_html`, `gallery_template_created`, `gallery_template_modified`, `gallery_template_is_visible`) VALUES (NULL, 'default', 'categories', NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `gallery_templates` (`gallery_template_id`, `gallery_template_theme_name`, `gallery_template_type`, `gallery_template_name`, `gallery_template_scripts`, `gallery_template_html`, `gallery_template_created`, `gallery_template_modified`, `gallery_template_is_visible`) VALUES (NULL, 'default', 'category', NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `gallery_templates` (`gallery_template_id`, `gallery_template_theme_name`, `gallery_template_type`, `gallery_template_name`, `gallery_template_scripts`, `gallery_template_html`, `gallery_template_created`, `gallery_template_modified`, `gallery_template_is_visible`) VALUES (NULL, 'default', 'image', NULL, NULL, NULL, NULL, NULL, 1);

COMMIT;

-- -----------------------------------------------------
-- Data for table `gallery_items`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `gallery_items` (`gallery_item_id`, `gallery_item_parent_id`, `gallery_item_cover_id`, `gallery_item_type`, `gallery_item_name`, `gallery_item_name_url`, `gallery_item_desc`, `gallery_item_alt`, `gallery_item_uri`, `gallery_item_uri_thumb`, `gallery_item_order`, `gallery_item_created`, `gallery_item_modified`, `gallery_item_is_visible`) VALUES (NULL, NULL, NULL, 'category', 'Uncategorized', NULL, 'default category for uncategorized images', NULL, NULL, NULL, 0, NULL, NULL, 1);

COMMIT;

-- -----------------------------------------------------
-- Data for table `blog_categories`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `blog_categories` (`blog_categ_id`, `blog_categ_parent_id`, `blog_categ_name`, `blog_categ_slug`, `blog_categ_description`, `blog_categ_created`, `blog_categ_modified`, `blog_categ_is_visible`, `blog_categ_is_comments`, `blog_categ_order`, `blog_categ_is_special`) VALUES (NULL, NULL, 'Uncategorized', 'uncategorized', 'This is the default category for all uncategorized posts', NULL, NULL, 1, 0, 1, 1);
INSERT INTO `blog_categories` (`blog_categ_id`, `blog_categ_parent_id`, `blog_categ_name`, `blog_categ_slug`, `blog_categ_description`, `blog_categ_created`, `blog_categ_modified`, `blog_categ_is_visible`, `blog_categ_is_comments`, `blog_categ_order`, `blog_categ_is_special`) VALUES (NULL, NULL, 'Featured', 'featured', 'This is a special category, which is used for featured posts', NULL, NULL, 1, 0, 2, 1);

COMMIT;
