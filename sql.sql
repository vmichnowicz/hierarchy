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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `hierarchy`
--

INSERT INTO `hierarchy` (`hierarchy_id`, `parent_id`, `lineage`, `deep`) VALUES
(1, NULL, '1', 0),
(2, NULL, '2', 0),
(3, 2, '2-3', 1),
(4, 3, '2-3-4', 2),
(5, 4, '2-3-4-5', 3),
(6, 4, '2-3-4-6', 3),
(7, 4, '2-3-4-7', 3),
(8, 3, '2-3-8', 2),
(9, 8, '2-3-8-9', 3),
(10, 8, '2-3-8-10', 3),
(11, 2, '2-11', 1),
(12, 11, '2-11-12', 2),
(13, 11, '2-11-13', 2),
(14, 12, '2-11-12-14', 3),
(15, 12, '2-11-12-15', 3),
(16, 12, '2-11-12-16', 3),
(17, NULL, '17', 0),
(18, 17, '17-18', 1),
(19, 17, '17-19', 1),
(20, 17, '17-20', 1),
(21, 18, '17-18-21', 2),
(22, 18, '17-18-22', 2),
(23, 18, '17-18-23', 2),
(24, NULL, '24', 0),
(25, 24, '24-25', 1),
(26, 24, '24-26', 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=48 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `hierarchy_id`, `title`, `url`) VALUES
(1, 1, 'Home', 'home'),
(2, 2, 'About', 'about'),
(3, 3, 'Company', 'about/company'),
(5, 4, 'History', 'about/company/history'),
(7, 5, 'The Beginning', 'about/company/history/the_beginning'),
(9, 6, 'The Middle', 'about/company/history/the_middle'),
(11, 7, 'Now', 'about/company/history/now'),
(13, 8, 'People', 'about/company/people'),
(15, 9, 'Management', 'about/company/people/management'),
(17, 10, 'Staff', 'about/company/people/staff'),
(19, 11, 'Affiliates', 'about/affiliates'),
(21, 12, 'Members', 'about/affiliates/members'),
(23, 13, 'Become a Member', 'about/affiliates/become_a_member'),
(25, 14, 'Gary&rsquo;s Gadgets', 'about/affiliates/members/garys_gadgets'),
(27, 15, 'Martha&rsquo;s Marinade', 'about/affiliates/members/marthas_marinade'),
(30, 16, 'Sue&rsquo;s Soccer Balls', 'about/affiliates/members/sues_soccer_balls'),
(31, 17, 'Stores', 'stores'),
(32, 18, 'Locations', 'stores/locations'),
(33, 19, 'Find a Store', 'stores/find_a_store'),
(35, 20, 'Franchise Opportunities', 'stores/franchise_opportunities'),
(37, 21, 'USA', 'stores/locations/usa'),
(39, 22, 'Europe', 'stores/locations/europe'),
(41, 23, 'North Pole', 'stores/locations/north_pole'),
(43, 24, 'Contact', 'contact'),
(44, 25, 'Sales', 'contact/sales'),
(46, 26, 'Customer Relations', 'contact/customer_relations');

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
