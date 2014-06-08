CREATE TABLE IF NOT EXISTS `role` (
`roleID` int(11) NOT NULL COMMENT 'The unique identifier for the role.',
  `roleName` varchar(50) NOT NULL COMMENT 'The human name for the role. Example: Administrator.',
  `description` varchar(500) DEFAULT NULL COMMENT 'A human readable description of the role.'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Puts users into groups.' AUTO_INCREMENT=0 ;

INSERT INTO `role` (`roleID`, `roleName`, `description`) VALUES
(1, 'Guest', 'default guest role'),
(2, 'Student', 'The default role for all students'),
(3, 'Faculty', 'Instructors and such'),
(4, 'Administrator', NULL);

ALTER TABLE `role`
 ADD PRIMARY KEY (`roleID`), ADD UNIQUE KEY `roleID_UNIQUE` (`roleID`), ADD UNIQUE KEY `roleName_UNIQUE` (`roleName`);