drop table refs;
drop table sayings;
drop table groups;

rename table Nodes to nodes;
alter table nodes change Name name VARCHAR(64) NOT NULL DEFAULT '';
alter table nodes change Hostname hostname VARCHAR(128) NOT NULL DEFAULT '';
alter table nodes change Path path VARCHAR(128) NOT NULL DEFAULT '';
alter table nodes change Port port INTEGER NOT NULL DEFAULT 80;
alter table nodes add column version SMALLINT NOT NULL DEFAULT 2 after Port;

rename table Online to online;
alter table online change UserID uid BIGINT NOT NULL DEFAULT 0;
alter table online change Login login INTEGER NOT NULL DEFAULT 0;
alter table online change LastAccess last_access INTEGER NOT NULL DEFAULT 0;
alter table online change What what VARCHAR(64) NOT NULL DEFAULT '';

rename table Preferences to preferences;
alter table preferences change UserID uid BIGINT NOT NULL DEFAULT 0;
alter table preferences change Name name VARCHAR(255) NOT NULL DEFAULT '';
alter table preferences change Value value VARCHAR(255) NOT NULL DEFAULT '';

rename table Timezones to timezones;
alter table timezones change Name name char(128) NOT NULL DEFAULT '';

rename table Themes to themes;
alter table themes change ID id SMALLINT NOT NULL DEFAULT 0;
alter table themes change SkinID skin_id SMALLINT NOT NULL DEFAULT 0;
alter table themes change Name name VARCHAR(128) NOT NULL DEFAULT '';
alter table themes change Dir dir VARCHAR(16) NOT NULL DEFAULT '';
alter table themes change AuthorName author_name VARCHAR(255) NOT NULL DEFAULT '';
alter table themes change AuthorContact author_contact VARCHAR(128) NOT NULL DEFAULT '';

alter table cookies change cookieId id INTEGER NOT NULL DEFAULT 0;
alter table cookies change submittedBy s_uid BIGINT NOT NULL DEFAULT 0;
alter table cookies add column approved CHAR(1) NOT NULL DEFAULT 'N';
CREATE INDEX s_uid ON cookies (s_uid);

alter table globalstats change totalhits totalhits BIGINT NOT NULL DEFAULT 0;

alter table news change newsId id INTEGER NOT NULL DEFAULT 0;
alter table news add column live CHAR(1) NOT NULL DEFAULT 'N';

rename table plan to plans;
alter table plans change userId uid BIGINT NOT NULL DEFAULT 0;
alter table plans change planText content TEXT NOT NULL DEFAULT '';

rename table Archive to archive;

alter table planwatch drop column groupId;
alter table planwatch change userId uid BIGINT NOT NULL DEFAULT 0;
alter table planwatch change watchId w_uid BIGINT NOT NULL DEFAULT 0;
alter table planwatch change lastView last_view INTEGER NOT NULL DEFAULT 0;
alter table planwatch add column gid BIGINT NOT NULL DEFAULT 1 after w_uid;

alter table snitch change userId uid BIGINT NOT NULL DEFAULT 0;
alter table snitch change snitchedBy s_uid BIGINT NOT NULL DEFAULT 0;
alter table snitch change snitchDate last_view INTEGER NOT NULL DEFAULT 0;
alter table snitch change snitchTimes views INTEGER NOT NULL DEFAULT 0;

alter table snoop change userId uid BIGINT NOT NULL DEFAULT 0;
alter table snoop change snoopedBy s_uid BIGINT NOT NULL DEFAULT 0;
alter table snoop change snoopDate referenced BIGINT NOT NULL DEFAULT 0;

alter table users change snitchViews snitch_views SMALLINT NOT NULL DEFAULT 25;
alter table users change totalViews views INTEGER NOT NULL DEFAULT 0;
alter table users change watchOrder watch_order VARCHAR(6) NOT NULL DEFAULT 'alph';
alter table users change themeID theme_id SMALLINT NOT NULL DEFAULT 1;
alter table users change snitchOn snitch_activated INTEGER NOT NULL DEFAULT 0;
alter table users change lastOn last_login INTEGER NOT NULL DEFAULT 0;
alter table users change lastUpdate last_update INTEGER NOT NULL DEFAULT 0;