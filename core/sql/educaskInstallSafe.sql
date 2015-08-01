-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role` (
  `roleID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the role.',
  `roleName` VARCHAR(50) NOT NULL COMMENT 'The human name for the role. Example: Administrator.',
  `description` VARCHAR(500) NULL COMMENT 'A human readable description of the role.',
  PRIMARY KEY (`roleID`))
ENGINE = InnoDB
COMMENT = 'Puts users into groups.';

CREATE UNIQUE INDEX `roleID_UNIQUE` ON `role` (`roleID` ASC);

CREATE UNIQUE INDEX `roleName_UNIQUE` ON `role` (`roleName` ASC);


-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `userID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the row.',
  `userName` VARCHAR(25) NOT NULL COMMENT 'Another unique identifier for the row, but is longer and human readable. Can be used for the user to log in. Also known as the screen name.',
  `firstName` VARCHAR(50) NOT NULL COMMENT 'The first name of the user.',
  `lastName` VARCHAR(50) NOT NULL COMMENT 'The last name of the user.',
  `email` VARCHAR(50) NOT NULL COMMENT 'The email the user would like to be contacted by.',
  `givenIdentifier` VARCHAR(100) NULL COMMENT 'The identifier given to the user by the institution.',
  `bio` VARCHAR(256) NULL COMMENT 'Optional information that the users give about themselves.',
  `birthday` DATE NULL DEFAULT '1993-04-30' COMMENT 'The date of birth of the user.',
  `profilePictureLocation` VARCHAR(60) NOT NULL DEFAULT 'images/defaultUserPicture.png',
  `password` LONGTEXT NOT NULL COMMENT 'The users chosen pass phrase to enter Educask. It is encrypted.',
  `accountCreationDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time in history when the user was added to Educask.',
  `lastAccess` DATETIME NOT NULL COMMENT 'The last time that the user accessed Educask.',
  `roleID` INT NOT NULL COMMENT 'The role of the user.',
  `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'A flag to indicate if the account is able to login or not.',
  `isExternalAuthentication` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'A flag to indicate if a plugin handles authenticating the user.',
  PRIMARY KEY (`userID`),
  CONSTRAINT `fk_user_role1`
    FOREIGN KEY (`roleID`)
    REFERENCES `role` (`roleID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'People who are registered on the system';

CREATE UNIQUE INDEX `userID_UNIQUE` ON `user` (`userID` ASC);

CREATE UNIQUE INDEX `userName_UNIQUE` ON `user` (`userName` ASC);

CREATE UNIQUE INDEX `givenIdentifier_UNIQUE` ON `user` (`givenIdentifier` ASC);

CREATE INDEX `fk_user_role1_idx` ON `user` (`roleID` ASC);


-- -----------------------------------------------------
-- Table `userFriendship`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `userFriendship` (
  `relationID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the relation.',
  `friendID` INT NOT NULL COMMENT 'The friend of the user.',
  `dateCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time in history when the friendship was created.',
  `confirmed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ensures that both users confirmed that they are friends.',
  `userID` INT NOT NULL COMMENT 'The user who initiated the friendship.',
  PRIMARY KEY (`relationID`),
  CONSTRAINT `fk_userFriendship_user`
    FOREIGN KEY (`userID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Maps users to their friends.';

CREATE UNIQUE INDEX `relationID_UNIQUE` ON `userFriendship` (`relationID` ASC);

CREATE INDEX `fk_userFriendship_user_idx` ON `userFriendship` (`userID` ASC);


-- -----------------------------------------------------
-- Table `permission`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permission` (
  `permissionID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the permission.',
  `permissionName` VARCHAR(50) NOT NULL COMMENT 'The computer name for the permission. Example: canReadPage.',
  `humanName` VARCHAR(50) NOT NULL COMMENT 'The human readable name of the permission. Example: Can Read Page.',
  `permissionDescription` VARCHAR(500) NULL COMMENT 'The description of the permission. Example: Enabling this permission will let the role read the page.',
  PRIMARY KEY (`permissionID`))
ENGINE = InnoDB
COMMENT = 'Contains information about possible permissions for users.';

CREATE UNIQUE INDEX `permissionID_UNIQUE` ON `permission` (`permissionID` ASC);

CREATE UNIQUE INDEX `permissionName_UNIQUE` ON `permission` (`permissionName` ASC);


-- -----------------------------------------------------
-- Table `permissionSet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissionSet` (
  `canDo` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'States whether the role has permission to perform the action associated with the permission.',
  `roleID` INT NOT NULL COMMENT 'The role to set the permission for.',
  `permissionID` INT NOT NULL COMMENT 'The permission that is being enabled or disabled.',
  CONSTRAINT `fk_permissionSet_role1`
    FOREIGN KEY (`roleID`)
    REFERENCES `role` (`roleID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_permissionSet_permission1`
    FOREIGN KEY (`permissionID`)
    REFERENCES `permission` (`permissionID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Relates user roles to permissions and keeps track if the role is allowed to do the action specified in the permission.';

CREATE INDEX `fk_permissionSet_role1_idx` ON `permissionSet` (`roleID` ASC);

CREATE INDEX `fk_permissionSet_permission1_idx` ON `permissionSet` (`permissionID` ASC);


-- -----------------------------------------------------
-- Table `status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `status` (
  `statusID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the status.',
  `posterID` INT NOT NULL COMMENT 'The author of the status.',
  `parentStatus` INT NOT NULL DEFAULT 0 COMMENT 'The status this status may be a comment on.',
  `supporterCount` INT NOT NULL DEFAULT 0 COMMENT 'The amount of people who \"liked\" the comment.',
  PRIMARY KEY (`statusID`),
  CONSTRAINT `fk_status_status1`
    FOREIGN KEY (`parentStatus`)
    REFERENCES `status` (`statusID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_status_user1`
    FOREIGN KEY (`posterID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Status or comments posted to pages.';

CREATE UNIQUE INDEX `statusID_UNIQUE` ON `status` (`statusID` ASC);

CREATE INDEX `fk_status_status1_idx` ON `status` (`parentStatus` ASC);

CREATE INDEX `fk_status_user1_idx` ON `status` (`posterID` ASC);


-- -----------------------------------------------------
-- Table `statusSupporter`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `statusSupporter` (
  `supporterID` INT NOT NULL COMMENT 'The user supporting the status.',
  `statusID` INT NOT NULL COMMENT 'The status being supported.',
  CONSTRAINT `fk_statusSupporter_user1`
    FOREIGN KEY (`supporterID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_statusSupporter_status1`
    FOREIGN KEY (`statusID`)
    REFERENCES `status` (`statusID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Maps a user to a status they support and vise versa.';

CREATE INDEX `fk_statusSupporter_user1_idx` ON `statusSupporter` (`supporterID` ASC);

CREATE INDEX `fk_statusSupporter_status1_idx` ON `statusSupporter` (`statusID` ASC);


-- -----------------------------------------------------
-- Table `variable`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `variable` (
  `variableName` VARCHAR(100) NOT NULL COMMENT 'The name of the variable. Example: lastCronRun.',
  `variableValue` VARCHAR(255) NOT NULL COMMENT 'The value for the variable. Example: 05/03/2014 9:46PM',
  `readOnly` TINYINT(1) NOT NULL COMMENT 'Flag to indicate if the variable cant be changed.',
  PRIMARY KEY (`variableName`))
ENGINE = InnoDB
COMMENT = 'Contains configuration settings.';

CREATE UNIQUE INDEX `variableName_UNIQUE` ON `variable` (`variableName` ASC);


-- -----------------------------------------------------
-- Table `systemLog`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `systemLog` (
  `eventID` INT NOT NULL COMMENT 'The unique identifier for the log entry.',
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time in history when the log entry was made.',
  `message` VARCHAR(500) NOT NULL COMMENT 'The log message recorded.',
  `type` VARCHAR(45) NOT NULL,
  `userID` INT NULL DEFAULT 0 COMMENT 'If the message regards a specific user, this contains the user id. Otherwise, it is 0.',
  PRIMARY KEY (`eventID`),
  CONSTRAINT `fk_systemLog_user1`
    FOREIGN KEY (`userID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Recorded events and notifications.';

CREATE UNIQUE INDEX `eventID_UNIQUE` ON `systemLog` (`eventID` ASC);

CREATE INDEX `fk_systemLog_user1_idx` ON `systemLog` (`userID` ASC);


-- -----------------------------------------------------
-- Table `statusRevision`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `statusRevision` (
  `revisionID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the revision.',
  `status` VARCHAR(1000) NOT NULL COMMENT 'The body of the message posted.',
  `timePosted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time in history when the ',
  `statusID` INT NOT NULL COMMENT 'The status which this revision belongs to.',
  `revisorID` INT NOT NULL COMMENT 'The id of the user who made the edit to the status.',
  `isCurrent` TINYINT(1) NOT NULL COMMENT 'Flag to indicate if the status revision is the most recent one.',
  PRIMARY KEY (`revisionID`),
  CONSTRAINT `fk_statusRevision_status1`
    FOREIGN KEY (`statusID`)
    REFERENCES `status` (`statusID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_statusRevision_user1`
    FOREIGN KEY (`revisorID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Tracks edits made to status/comment posts.';

CREATE UNIQUE INDEX `revisionId_UNIQUE` ON `statusRevision` (`revisionID` ASC);

CREATE INDEX `fk_statusRevision_status1_idx` ON `statusRevision` (`statusID` ASC);

CREATE INDEX `fk_statusRevision_user1_idx` ON `statusRevision` (`revisorID` ASC);


-- -----------------------------------------------------
-- Table `userOption`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `userOption` (
  `optionID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the option.',
  `optionName` VARCHAR(50) NOT NULL COMMENT 'The computer name for the option.',
  `humanName` VARCHAR(50) NOT NULL COMMENT 'The human readable name for the option.',
  `optionDescription` VARCHAR(500) NULL COMMENT 'Describes the option.',
  PRIMARY KEY (`optionID`))
ENGINE = InnoDB
COMMENT = 'User specific options.';

CREATE UNIQUE INDEX `optionID_UNIQUE` ON `userOption` (`optionID` ASC);

CREATE UNIQUE INDEX `optionName_UNIQUE` ON `userOption` (`optionName` ASC);


-- -----------------------------------------------------
-- Table `userOptionSet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `userOptionSet` (
  `optionSetID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the row.',
  `value` VARCHAR(50) NOT NULL COMMENT 'The users selected value for the option.',
  `userID` INT NOT NULL COMMENT 'The user that the setting is for.',
  `optionID` INT NOT NULL COMMENT 'The option the setting is for.',
  PRIMARY KEY (`optionSetID`),
  CONSTRAINT `fk_userOptionSet_user1`
    FOREIGN KEY (`userID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_userOptionSet_userOption1`
    FOREIGN KEY (`optionID`)
    REFERENCES `userOption` (`optionID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Relates a user to user options and keeps track on their preferences for the option.';

CREATE UNIQUE INDEX `optionSetID_UNIQUE` ON `userOptionSet` (`optionSetID` ASC);

CREATE INDEX `fk_userOptionSet_user1_idx` ON `userOptionSet` (`userID` ASC);

CREATE INDEX `fk_userOptionSet_userOption1_idx` ON `userOptionSet` (`optionID` ASC);


-- -----------------------------------------------------
-- Table `urlAlias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `urlAlias` (
  `aliasID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the entry.',
  `source` VARCHAR(1000) NOT NULL COMMENT 'The original URL.',
  `alias` VARCHAR(1000) NOT NULL COMMENT 'The new URL.',
  PRIMARY KEY (`aliasID`))
ENGINE = InnoDB
COMMENT = 'Maps a non-pretty URL to a pretty URL. Example: node/35/cat/drtiou/325s/a to /elephant.';

CREATE UNIQUE INDEX `aliasID_UNIQUE` ON `urlAlias` (`aliasID` ASC);


-- -----------------------------------------------------
-- Table `menus`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `menu` (
  `menuID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the row.',
  `computerName` VARCHAR(64) NOT NULL COMMENT 'The computer name from the menu. Example: \"mainMenu\"',
  `humanName` VARCHAR(64) NOT NULL COMMENT 'The human readable name for the menu. Example: \"Main Menu\"',
  `themeRegion` VARCHAR(64) NOT NULL COMMENT 'The place in the theme where the menu belongs to.',
  `enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Flag to indicate if the menu is in use or not.',
  PRIMARY KEY (`menuID`))
ENGINE = InnoDB
COMMENT = 'Information about possible menus.';

CREATE UNIQUE INDEX `menuID_UNIQUE` ON `menu` (`menuID` ASC);


-- -----------------------------------------------------
-- Table `menuItem`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `menuItem` (
  `menuItemID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the row.',
  `menuID` INT NOT NULL COMMENT 'The menu that the menu item belongs to.',
  `linkText` VARCHAR(50) NOT NULL COMMENT 'The text to display in the link.',
  `href` VARCHAR(2000) NOT NULL COMMENT 'The link.',
  `weight` INT NOT NULL DEFAULT 0 COMMENT 'Sort the item.',
  `hasChildren` TINYINT(1) NOT NULL COMMENT 'Flag to indicate if the menu item has children or not.',
  `enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'A flag to indicate if the menu item is used or not.',
  `parent` INT NULL DEFAULT 0 COMMENT 'The menu item that this menu item is a child of.',
  PRIMARY KEY (`menuItemID`),
  CONSTRAINT `fk_menuItem_menu1`
    FOREIGN KEY (`menuID`)
    REFERENCES `menu` (`menuID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_menuItem_menuItem1`
    FOREIGN KEY (`parent`)
    REFERENCES `menuItem` (`menuItemID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Individual links that belong to menus.';

CREATE UNIQUE INDEX `menuItemID_UNIQUE` ON `menuItem` (`menuItemID` ASC);

CREATE INDEX `fk_menuItem_menu1_idx` ON `menuItem` (`menuID` ASC);

CREATE INDEX `fk_menuItem_menuItem1_idx` ON `menuItem` (`parent` ASC);


-- -----------------------------------------------------
-- Table `menuItemVisibility`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `menuItemVisibility` (
  `ruleID` INT NOT NULL AUTO_INCREMENT COMMENT 'A unique identifier for the rule',
  `menuItemID` INT NOT NULL COMMENT 'The menu item the rule is for.',
  `referenceID` VARCHAR(50) NOT NULL COMMENT 'The unique identifier for the object being referenced.',
  `referenceType` VARCHAR(50) NOT NULL COMMENT 'The type of the object being referenced. Example: user.',
  `visible` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'The flag that determines if the Menu Item is visible in the rule.',
  PRIMARY KEY (`ruleID`),
  CONSTRAINT `fk_menuItemVisibility_menuItem1`
    FOREIGN KEY (`menuItemID`)
    REFERENCES `menuItem` (`menuItemID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Holds information the visibility rules for menu items.';

CREATE UNIQUE INDEX `ruleID_UNIQUE` ON `menuItemVisibility` (`ruleID` ASC);

CREATE INDEX `fk_menuItemVisibility_menuItem1_idx` ON `menuItemVisibility` (`menuItemID` ASC);


-- -----------------------------------------------------
-- Table `module`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `module` (
  `moduleID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unqiue identifier for the module.',
  `moduleName` VARCHAR(64) NOT NULL COMMENT 'The computer name for the module. Example: myModule.',
  `humanName` VARCHAR(70) NOT NULL COMMENT 'The human name for the module. Example: My Module.',
  `enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'A flag to indicate if the module is to be used.',
  PRIMARY KEY (`moduleID`))
ENGINE = InnoDB
COMMENT = 'Contains information about installed modules and plugins.';

CREATE UNIQUE INDEX `moduleID_UNIQUE` ON `module` (`moduleID` ASC);

CREATE UNIQUE INDEX `moduleName_UNIQUE` ON `module` (`moduleName` ASC);


-- -----------------------------------------------------
-- Table `block`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `block` (
  `blockID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the row.',
  `blockName` VARCHAR(64) NOT NULL COMMENT 'The name of the block.',
  `title` VARCHAR(64) NULL COMMENT 'A custom title that will override any software title.',
  `theme` VARCHAR(64) NOT NULL COMMENT 'The theme this entry is for.',
  `themeRegion` VARCHAR(64) NOT NULL COMMENT 'The region in the theme where the block should be rendered.',
  `weight` INT NOT NULL COMMENT 'Determines the order the blocks should be rendered.',
  `enabled` TINYINT(1) NOT NULL COMMENT 'A flag to indicate if the block should be rendered or not.',
  `module` INT NOT NULL COMMENT 'The identifier for the module responsible for rendering the content.',
  PRIMARY KEY (`blockID`),
  CONSTRAINT `fk_block_module1`
    FOREIGN KEY (`module`)
    REFERENCES `module` (`moduleID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Information about regions on the page and their content.';

CREATE UNIQUE INDEX `blockID_UNIQUE` ON `block` (`blockID` ASC);

CREATE INDEX `fk_block_module1_idx` ON `block` (`module` ASC);


-- -----------------------------------------------------
-- Table `blockVisibility`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `blockVisibility` (
  `ruleID` INT NOT NULL COMMENT 'A unique identifier for the rule',
  `referenceID` VARCHAR(50) NOT NULL COMMENT 'The unique identifier for the object being referenced.',
  `referenceType` VARCHAR(50) NOT NULL COMMENT 'The type of the object being referenced. Example: user.',
  `visible` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'The flag that determines if the block is visible in the rule.',
  `blockID` INT NOT NULL COMMENT 'The block that the rule is for.',
  PRIMARY KEY (`ruleID`),
  CONSTRAINT `fk_blockVisibility_block1`
    FOREIGN KEY (`blockID`)
    REFERENCES `block` (`blockID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Holds information the visibility rules for blocks.';

CREATE UNIQUE INDEX `ruleID_UNIQUE` ON `blockVisibility` (`ruleID` ASC);

CREATE INDEX `fk_blockVisibility_block1_idx` ON `blockVisibility` (`blockID` ASC);


-- -----------------------------------------------------
-- Table `folder`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `folder` (
  `folderID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the folder.',
  `title` VARCHAR(50) NOT NULL COMMENT 'The name for the folder.',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time in history when the folder was created.',
  `ownerID` INT NOT NULL COMMENT 'The creator of the folder.',
  `parentFolder` INT NOT NULL COMMENT 'The parent folder of the folder.',
  PRIMARY KEY (`folderID`),
  CONSTRAINT `fk_folder_user1`
    FOREIGN KEY (`ownerID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_folder_folder1`
    FOREIGN KEY (`parentFolder`)
    REFERENCES `folder` (`folderID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Organize files.';

CREATE UNIQUE INDEX `folderID_UNIQUE` ON `folder` (`folderID` ASC);

CREATE INDEX `fk_folder_user1_idx` ON `folder` (`ownerID` ASC);

CREATE INDEX `fk_folder_folder1_idx` ON `folder` (`parentFolder` ASC);


-- -----------------------------------------------------
-- Table `file`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `file` (
  `fileID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the upload.',
  `uploaded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time in history when the file was uploaded.',
  `title` VARCHAR(50) NOT NULL COMMENT 'The name of the file.',
  `mimeType` VARCHAR(50) NOT NULL COMMENT 'The type of the file uploaded.',
  `size` INT NOT NULL DEFAULT 0 COMMENT 'The size of the file uploaded.',
  `location` VARCHAR(75) NOT NULL COMMENT 'The location to where the file was uploaded on the server.',
  `uploader` INT NOT NULL COMMENT 'The user who uploaded the file.',
  `folderID` INT NOT NULL COMMENT 'The folder that the file was uploaded to.',
  PRIMARY KEY (`fileID`),
  CONSTRAINT `fk_files_user1`
    FOREIGN KEY (`uploader`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_folder1`
    FOREIGN KEY (`folderID`)
    REFERENCES `folder` (`folderID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Uploaded files.';

CREATE UNIQUE INDEX `fileID_UNIQUE` ON `file` (`fileID` ASC);

CREATE INDEX `fk_files_user1_idx` ON `file` (`uploader` ASC);

CREATE INDEX `fk_files_folder1_idx` ON `file` (`folderID` ASC);


-- -----------------------------------------------------
-- Table `fileSystemShare`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fileSystemShare` (
  `ruleID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the row.',
  `referenceID` INT NOT NULL COMMENT 'The file or folder being referenced.',
  `referenceType` VARCHAR(50) NOT NULL COMMENT 'Used to determine if a file or folder is being referenced.',
  `shared` TINYINT(1) NOT NULL COMMENT 'Flag to indicate if the rule is making the file or folder shared or not.',
  `userID` INT NOT NULL COMMENT 'The user who is being granted or denied permission to view the file or folder.',
  PRIMARY KEY (`ruleID`),
  CONSTRAINT `fk_fileSystemShare_user1`
    FOREIGN KEY (`userID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Shares folders and files between users.';

CREATE UNIQUE INDEX `ruleID_UNIQUE` ON `fileSystemShare` (`ruleID` ASC);

CREATE INDEX `fk_fileSystemShare_user1_idx` ON `fileSystemShare` (`userID` ASC);


-- -----------------------------------------------------
-- Table `forgotPassword`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `forgotPassword` (
  `requestID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the request.',
  `token` VARCHAR(200) NOT NULL COMMENT 'The unique token for the request. User to minimize the chances of someone high-jacking the request.',
  `requestDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time in history when the request was made. After a period of time, the request will be deleted.',
  `userID` INT NOT NULL COMMENT 'The user that the request was made for.',
  PRIMARY KEY (`requestID`, `userID`),
  CONSTRAINT `fk_forgotPassword_user1`
    FOREIGN KEY (`userID`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Keep track of forgotten password requests.';

CREATE UNIQUE INDEX `requestID_UNIQUE` ON `forgotPassword` (`requestID` ASC);

CREATE UNIQUE INDEX `token_UNIQUE` ON `forgotPassword` (`token` ASC);

CREATE INDEX `fk_forgotPassword_user1_idx` ON `forgotPassword` (`userID` ASC);

CREATE UNIQUE INDEX `userID_UNIQUE` ON `forgotPassword` (`userID` ASC);


-- -----------------------------------------------------
-- Table `lockout`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lockout` (
  `ipAddress` VARCHAR(20) NOT NULL COMMENT 'The IP address that is locked out',
  `numberOfFailedAttempts` INT NOT NULL COMMENT 'The count for how many times the client failed to log in.',
  `lastUpdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The lasts time this entry was modified.',
  `attemptsLeft` INT NOT NULL COMMENT 'The number of attempts the client has left before being locked out.',
  PRIMARY KEY (`ipAddress`))
ENGINE = InnoDB
COMMENT = 'Prevents spam bots from abusing the site and hacking.';

CREATE UNIQUE INDEX `ipAddress_UNIQUE` ON `lockout` (`ipAddress` ASC);


-- -----------------------------------------------------
-- Table `censorship`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `censorship` (
  `censorID` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the row.',
  `word` VARCHAR(200) NOT NULL COMMENT 'The word being banned.',
  `replacement` VARCHAR(200) NULL COMMENT 'The word to replace the banned word with.',
  `banned` TINYINT(1) NOT NULL COMMENT 'A flag to indicate that this ban is in effect or not.',
  PRIMARY KEY (`censorID`))
ENGINE = InnoDB
COMMENT = 'Holds data on censored words.';

CREATE UNIQUE INDEX `censorID_UNIQUE` ON `censorship` (`censorID` ASC);


-- -----------------------------------------------------
-- Table `mailTemplate`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mailTemplate` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier for the row.',
  `name` VARCHAR(50) NOT NULL COMMENT 'The name for the template.',
  `subject` VARCHAR(50) NOT NULL COMMENT 'The subject for the email.',
  `body` VARCHAR(500) NOT NULL COMMENT 'The body of the email.',
  `senderEmail` VARCHAR(50) NOT NULL COMMENT 'The email that the template should email from.',
  `senderName` VARCHAR(50) NOT NULL COMMENT 'The name of the sender.',
  `modifier` INT NOT NULL COMMENT 'The id of the last user to modify the template.',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_mailTemplate_user1`
    FOREIGN KEY (`modifier`)
    REFERENCES `user` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Provides standard templates for emails.';

CREATE INDEX `fk_mailTemplate_user1_idx` ON `mailTemplate` (`modifier` ASC);

-- -----------------------------------------------------
-- Table `session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `session` (
  `id` VARCHAR(255) NOT NULL COMMENT 'The hashed unique-identifier of the session.',
  `variables` LONGTEXT NULL COMMENT 'The encrypted data in the session.',
  `lastAccess` INT NULL COMMENT 'The last time in history when the session was written to.',
  `locked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indicates if the database session entry is currently in use or not.',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Holds a users session data.';

CREATE UNIQUE INDEX `id_UNIQUE` ON `session` (`id` ASC);