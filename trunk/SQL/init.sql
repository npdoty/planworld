# initialize the globalstats table
INSERT INTO globalstats (totalhits) VALUES (0);


#initialize default set of themes
INSERT INTO themes VALUES (1,1,'Standard','default','Peter Kupfer, Seth Fitzsimmons','seth@note.amherst.edu');
INSERT INTO themes VALUES (2,1,'Meadow','meadow','Lynne Baer','lebaer00@alumni.amherst.edu');
INSERT INTO themes VALUES (3,1,'Night','night','Katie Buechner','kabuechner@amherst.edu');
INSERT INTO themes VALUES (4,1,'Metal','metal','Jonathan Kaldor','jmkaldor@amherst.edu');
INSERT INTO themes VALUES (5,1,'Soylent Green','soylent','Jonathan Kaldor','jmkaldor@amherst.edu');
INSERT INTO themes VALUES (6,1,'Hotel Pastis','pastis','Baker Franke','befranke02@alumni.amherst.edu');
INSERT INTO themes VALUES (7,1,'Slate','slate','Baker Franke','befranke02@alumni.amherst.edu');
INSERT INTO themes VALUES (8,1,'Plum','plum','Baker Franke','befranke02@alumni.amherst.edu');
INSERT INTO themes VALUES (9,1,'Terminal','oldVax','Lila Maclean','lbmaclean02@alumni.amherst.edu');
INSERT INTO themes VALUES (10,1,'Baby Blue','babyblue','Tal Liron, Matt Weber','mjweber02@alumni.amherst.edu');
INSERT INTO themes VALUES (11,1,'PHP.net','php.net','Matt Gordon','magordon@amherst.edu');
INSERT INTO themes VALUES (12,1,'Minimalism','minimalism','Zach Sacks','zbsacks@amherst.edu');

# create a planworld user and allow access
use mysql;
INSERT INTO user (Host, User, Password) values ('localhost', 'planworld', password('plans'));
INSERT INTO db (Host, Db, User, Select_Priv, Insert_Priv, Update_priv, Delete_priv, Create_priv) VALUES ('localhost', 'planworld', 'planworld', 'Y', 'Y', 'Y', 'Y', 'Y');
FLUSH PRIVILEGES;
