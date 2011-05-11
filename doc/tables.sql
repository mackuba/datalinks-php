CREATE TABLE datalinks_links (
  id int(11) NOT NULL auto_increment,
  title varchar(250),
  url tinytext NOT NULL,
  category int(11) NOT NULL,
  description text,
  PRIMARY KEY (id)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE datalinks_categories (
  id int(11) NOT NULL auto_increment,
  name varchar(100) NOT NULL,
  parent int(11) default NULL,
  type int(1) default '0',
  invisible int(1) default '0',
  subtree_links int default '0',
  subtree_categories int default '0',
  subtree_visible_links int default '0',
  subtree_visible_categories int default '0',
  PRIMARY KEY (id)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT INTO datalinks_categories VALUES (1, 'Datalinks', NULL);
