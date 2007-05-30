CREATE TABLE nodes (
  name VARCHAR(64) NOT NULL DEFAULT '',
  hostname VARCHAR(128) NOT NULL DEFAULT '',
  path VARCHAR(128) NOT NULL DEFAULT '',
  port INTEGER NOT NULL DEFAULT '80',
  version SMALLINT NOT NULL DEFAULT 2,
  PRIMARY KEY (name)
);

CREATE TABLE online (
  uid BIGINT NOT NULL DEFAULT 0,
  login INTEGER NOT NULL DEFAULT 0,
  last_access INTEGER NOT NULL DEFAULT 0,
  what VARCHAR(64) NOT NULL DEFAULT '',
  PRIMARY KEY (uid)
);

CREATE INDEX login ON online (login);
CREATE INDEX last_access ON online (last_access);

CREATE TABLE preferences (
  uid BIGINT NOT NULL DEFAULT 0,
  name VARCHAR(255) NOT NULL DEFAULT '',
  value VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (uid, name)
);

CREATE TABLE timezones (
  name VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (name)
);

CREATE TABLE themes (
  id SMALLINT NOT NULL DEFAULT 0,
  skin_id SMALLINT NOT NULL DEFAULT 0,
  name VARCHAR(128) NOT NULL DEFAULT '',
  dir VARCHAR(16) NOT NULL DEFAULT '',
  author_name VARCHAR(255) NOT NULL DEFAULT '',
  author_contact VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
);

CREATE INDEX skin_id ON themes (skin_id);

CREATE TABLE cookies (
  id INTEGER NOT NULL DEFAULT 0,
  quote TEXT NOT NULL DEFAULT '',
  author VARCHAR(255) NOT NULL DEFAULT '',
  s_uid BIGINT NOT NULL DEFAULT 0,
  approved CHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (id)
);

CREATE INDEX s_uid ON cookies (s_uid);

CREATE TABLE globalstats (
  totalhits BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY (totalhits)
);

CREATE TABLE news (
  id INTEGER NOT NULL DEFAULT 0,
  news TEXT NOT NULL DEFAULT '',
  date INTEGER NOT NULL DEFAULT 0,
  live CHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (id)
);

CREATE TABLE plans (
  uid BIGINT NOT NULL DEFAULT 0,
  content MEDIUMTEXT NOT NULL DEFAULT '',
  PRIMARY KEY (uid)
);

CREATE TABLE archive (
  uid BIGINT NOT NULL DEFAULT 0,
  posted INTEGER NOT NULL DEFAULT 0,
  name VARCHAR(64) NOT NULL DEFAULT '',
  pub CHAR(1) NOT NULL DEFAULT 'N',
  views INTEGER NOT NULL DEFAULT 0,
  content MEDIUMTEXT NOT NULL DEFAULT '',
  PRIMARY KEY (uid, posted)
);

CREATE INDEX pub ON archive (pub);

CREATE TABLE planwatch (
  uid BIGINT NOT NULL DEFAULT 0,
  w_uid BIGINT NOT NULL DEFAULT 0,
  gid BIGINT NOT NULL DEFAULT 1,
  last_view INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (uid, w_uid)
);

CREATE TABLE snitch (
  uid BIGINT NOT NULL DEFAULT 0,
  s_uid BIGINT NOT NULL DEFAULT 0,
  last_view INTEGER NOT NULL DEFAULT 0,
  views INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (uid, s_uid)
);

CREATE TABLE snitchtracker (
  uid BIGINT NOT NULL DEFAULT 0,
  s_uid BIGINT NOT NULL DEFAULT 0,
  viewed INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (uid, s_uid, viewed)
);

CREATE TABLE snoop (
  uid BIGINT NOT NULL DEFAULT 0,
  s_uid BIGINT NOT NULL DEFAULT 0,
  referenced BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY (uid, s_uid)
);

CREATE TABLE users (
  id BIGINT NOT NULL DEFAULT 0,
  username VARCHAR(128) NOT NULL DEFAULT '',
  remote CHAR(1) NOT NULL DEFAULT 'N',
  world CHAR(1) NOT NULL DEFAULT 'Y',
  snitch CHAR(1) NOT NULL DEFAULT 'N',
  snitch_views SMALLINT NOT NULL DEFAULT 25,
  archive CHAR(1) NOT NULL DEFAULT 'P',
  archive_size INTEGER NOT NULL DEFAULT 0,
  archive_size_pub INTEGER NOT NULL DEFAULT 0,
  views INTEGER NOT NULL DEFAULT 0,
  watch_order VARCHAR(6) NOT NULL DEFAULT 'alph',
  theme_id SMALLINT NOT NULL DEFAULT 1,
  snitch_activated INTEGER NOT NULL DEFAULT 0,
  last_login INTEGER NOT NULL DEFAULT 0,
  last_update INTEGER NOT NULL DEFAULT 0,
  last_ip VARCHAR(15) DEFAULT '',
  first_login INTEGER,
  PRIMARY KEY (id)
);

CREATE UNIQUE INDEX username ON users (username);

CREATE TABLE pw_groups (
  gid BIGINT NOT NULL DEFAULT 0,
  uid BIGINT NOT NULL DEFAULT 0,
  name VARCHAR(64) NOT NULL DEFAULT '',
  pos SMALLINT NOT NULL DEFAULT 1,
  PRIMARY KEY(gid, uid)
);

CREATE TABLE message (
  uid bigint(20) NOT NULL default '0',
  to_uid bigint(20) NOT NULL default '0',
  last_update int(11) NOT NULL default '0',
  message text NOT NULL,
  seen tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid,to_uid)
);

CREATE TABLE send (
  uid int(10) unsigned NOT NULL default '0',
  to_uid int(10) unsigned NOT NULL default '0',
  sent int(10) unsigned NOT NULL default '0',
  seen int(10) unsigned NOT NULL default '0',
  message text,
  PRIMARY KEY  (uid,to_uid,sent)
);
