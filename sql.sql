--
-- Table structure for table `hierarchy`
--

CREATE TABLE IF NOT EXISTS `hierarchy` (
  `id` int(64) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `parent_id` int(64) unsigned DEFAULT NULL,
  `lineage` varchar(128) NOT NULL,
  `deep` smallint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `hierarchy`
--

INSERT INTO `hierarchy` (`id`, `title`, `parent_id`, `lineage`, `deep`) VALUES
(1, 'Home', NULL, '1', 0),
(2, 'About', NULL, '2', 0),
(3, 'Our History', 2, '2-3', 1),
(4, 'Our Future', 2, '2-4', 1),
(5, 'Products', NULL, '5', 0),
(6, 'Cars', 5, '5-6', 1),
(7, 'Ford', 6, '5-6-7', 2),
(8, 'Chevrolet', 6, '5-6-8', 2),
(11, 'Honda', 6, '5-6-11', 2),
(12, 'New', 11, '5-6-11-12', 3),
(13, 'Used', 11, '5-6-11-13', 3);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hierarchy`
--
ALTER TABLE `hierarchy`
  ADD CONSTRAINT `hierarchy_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `hierarchy` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;