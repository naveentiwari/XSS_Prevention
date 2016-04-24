USE blogdb;

DROP TABLE IF EXISTS `blog_entries`;
DROP TABLE IF EXISTS `user_info`;

CREATE TABLE `user_info` (
    `userID` int(25) unsigned NOT NULL AUTO_INCREMENT,
    `username` varchar(256) NOT NULL UNIQUE,
    `shpasswd` varchar(256) NOT NULL,
    `fname` varchar(128) DEFAULT NULL,
    `lname` varchar(128) DEFAULT NULL,
    `email` varchar(128) NOT NULL,
    PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `blog_entries` (
    `blogID` int(25) unsigned NOT NULL AUTO_INCREMENT,
    `userID` int(25) unsigned,
    `title`text,
    `intro` text,
    `content` text,
    `postDate` datetime,
    PRIMARY KEY (`blogID`),
    FOREIGN KEY (`userID`) REFERENCES user_info(userID)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
