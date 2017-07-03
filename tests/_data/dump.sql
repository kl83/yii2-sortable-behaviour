SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idx` int(10) unsigned NOT NULL,
  `name` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx` (`idx`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=155 ;

INSERT INTO `test` (`id`, `idx`, `name`) VALUES
(1, 10, 'Juice'),
(2, 20, 'Jelly'),
(3, 30, 'Chocolate'),
(4, 40, 'Candy'),
(5, 50, 'Beef');

CREATE TABLE IF NOT EXISTS `test_hierarchical` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` int(10) unsigned NOT NULL,
  `idx` int(10) unsigned NOT NULL,
  `name` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx` (`idx`),
  KEY `parentId` (`parentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

INSERT INTO `test_hierarchical` (`id`, `parentId`, `idx`, `name`) VALUES
(1, 0, 10, 'Juice'),
(2, 1, 20, 'Jelly'),
(3, 1, 30, 'Chocolate'),
(4, 1, 40, 'Candy'),
(5, 7, 50, 'Beef'),
(6, 3, 10, 'Toblerone'),
(7, 3, 20, 'Fazer'),
(8, 0, 0, 'Horse');
