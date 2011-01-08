--
-- Database: `hierarchy`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `hierarchy_id` int(64) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `comment` text NOT NULL,
  `author` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `url` varchar(128) DEFAULT NULL,
  `timestamp` int(32) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hierarchy_id` (`hierarchy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `comments`
--


-- --------------------------------------------------------

--
-- Table structure for table `hierarchy`
--

CREATE TABLE IF NOT EXISTS `hierarchy` (
  `hierarchy_id` int(64) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(64) unsigned DEFAULT NULL,
  `lineage` varchar(128) CHARACTER SET latin1 NOT NULL,
  `deep` smallint(8) unsigned NOT NULL,
  PRIMARY KEY (`hierarchy_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `hierarchy`
--

INSERT INTO `hierarchy` (`hierarchy_id`, `parent_id`, `lineage`, `deep`) VALUES
(1, NULL, '1', 0),
(2, NULL, '2', 0),
(3, 2, '2-3', 1),
(4, 2, '2-4', 1),
(5, NULL, '5', 0),
(6, 5, '5-6', 1),
(7, 6, '5-6-7', 2),
(8, 6, '5-6-8', 2),
(11, 6, '5-6-11', 2),
(12, 11, '5-6-11-12', 3),
(13, 11, '5-6-11-13', 3);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `hierarchy_id` int(64) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `url` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hierarchy_id` (`hierarchy_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `hierarchy_id`, `title`, `url`) VALUES
(1, 1, 'Home', 'home'),
(2, 2, 'About', 'about'),
(3, 3, 'Our History', 'about/history'),
(4, 4, 'Our Future', 'about/future'),
(5, 5, 'Products', 'products'),
(6, 6, 'Cars', 'products/cars'),
(7, 7, 'Ford', 'products/cars/ford'),
(8, 8, 'Chevrolet', 'products/cars/chevrolet'),
(9, 11, 'Honda', 'products/cars/honda'),
(10, 12, 'New', 'products/cars/honda/new'),
(11, 13, 'Used', 'products/cars/honda/used');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`hierarchy_id`) REFERENCES `hierarchy` (`hierarchy_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hierarchy`
--
ALTER TABLE `hierarchy`
  ADD CONSTRAINT `hierarchy_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `hierarchy` (`hierarchy_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`hierarchy_id`) REFERENCES `hierarchy` (`hierarchy_id`) ON DELETE CASCADE ON UPDATE CASCADE;