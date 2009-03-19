-- phpMyAdmin SQL Dump
-- version 3.1.3deb1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeu 19 Mars 2009 à 19:51
-- Version du serveur: 5.0.51
-- Version de PHP: 5.2.6-3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `BSE`
--

-- --------------------------------------------------------

--
-- Structure de la table `bse_admins`
--

CREATE TABLE `bse_admins` (
  `username` varchar(256) character set utf8 collate utf8_bin NOT NULL,
  `password` varchar(512) character set utf8 collate utf8_bin NOT NULL,
  `level` smallint(6) NOT NULL,
  `logged` tinyint(1) NOT NULL,
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Default admin
INSERT INTO `bse_admins` (`username`, `password`, `level`, `logged`) VALUES
('admin', '5f4dcc3b5aa765d61d8327deb882cf99', 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `bse_counter`
--

CREATE TABLE `bse_counter` (
  `ip` varchar(32) NOT NULL,
  `count` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `bse_devel`
--

CREATE TABLE `bse_devel` (
  `id` int(11) NOT NULL auto_increment,
  `date` bigint(20) NOT NULL,
  `content` text character set utf8 collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='avancement du moteur' ;

-- --------------------------------------------------------

--
-- Structure de la table `bse_medias`
--

CREATE TABLE `bse_medias` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uri` varchar(1024) character set utf8 collate utf8_bin NOT NULL COMMENT 'media URI',
  `tb_uri` varchar(1024) character set utf8 collate utf8_bin NOT NULL COMMENT 'thumbnail URI',
  `size` int(10) unsigned NOT NULL COMMENT 'media size in bytes',
  `mdate` bigint(20) NOT NULL COMMENT 'media upload timestamp',
  `type` int(11) NOT NULL COMMENT 'media type ID',
  `uid` int(10) unsigned NOT NULL default '0' COMMENT 'uploader ID',
  `tags` varchar(256) character set utf8 collate utf8_bin NOT NULL COMMENT 'tags',
  `desc` varchar(512) character set utf8 collate utf8_bin NOT NULL COMMENT 'media name',
  `comment` text character set utf8 collate utf8_bin NOT NULL COMMENT 'comment',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Structure de la table `bse_news`
--

CREATE TABLE `bse_news` (
  `id` int(11) NOT NULL auto_increment,
  `date` bigint(20) NOT NULL,
  `mdate` bigint(20) NOT NULL,
  `titre` varchar(256) collate utf8_bin NOT NULL,
  `contenu` text collate utf8_bin NOT NULL,
  `source` text collate utf8_bin NOT NULL,
  `auteur` varchar(256) collate utf8_bin NOT NULL,
  `mauthor` varchar(256) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;
