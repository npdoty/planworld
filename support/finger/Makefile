# Generated automatically from Makefile.in by configure.

srcdir = .

SHELL = /bin/sh

prefix = /usr
exec_prefix = ${prefix}
SBINDIR = ${exec_prefix}/sbin
MANDIR = ${prefix}/man
SRC = .
CC = gcc
CFLAGS =  -g -O2 -DHAVE_CONFIG_H -I. -I/usr/local/mysql/include
LDFLAGS =  -lnsl -lmysqlclient -L/usr/local/mysql/lib/mysql
SED = /bin/sed
KILLALL = /usr/bin/killall
AWK = /bin/gawk
INSTALL = /usr/bin/install -c
INSTALL_DATA = ${INSTALL} -m 644


all: ffingerd

ffingerd: ffingerd.o config.h
	$(CC) $(CFLAGS) -o ffingerd ffingerd.o $(LDFLAGS)
	@echo Ok, do a 'make install' or 'make install.all' now.

install.bin: ffingerd
	$(SHELL) $(srcdir)/mkinstalldirs $(SBINDIR) $(MANDIR) $(MANDIR)/man8
	$(INSTALL) ffingerd $(SBINDIR)/ffingerd
	$(INSTALL) -m 444 $(srcdir)/ffingerd.8 $(MANDIR)/man8

install: install.bin
	@echo Do not forget to fix /etc/inetd.conf and kill -HUP the inetd.

install.all: install.bin
	$(SRC)/install.conf $(SBINDIR)/ffingerd $(AWK) $(SED) $(KILLALL)

uninstall:
	rm -f $(SBINDIR)/ffingerd $(MANDIR)/man8/ffingerd*
	@echo Do not forget to tell inetd...

clean:
	rm -f ffingerd ffingerd.o
