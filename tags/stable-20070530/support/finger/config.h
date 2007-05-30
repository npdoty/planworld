/* config.h.  Generated automatically by configure.  */
/* config.h.in.  Generated automatically from configure.in by autoheader.  */

/* Define if the `S_IS*' macros in <sys/stat.h> do not work properly.  */
/* #undef STAT_MACROS_BROKEN */

/* Define if you have the ANSI C header files.  */
#define STDC_HEADERS 1

/* Define if you have the getnameinfo function.  */
#define HAVE_GETNAMEINFO 1

/* Define if you have the getpeername function.  */
#define HAVE_GETPEERNAME 1

/* Define if you have the getpwnam function.  */
#define HAVE_GETPWNAM 1

/* Define if you have the index function.  */
#define HAVE_INDEX 1

/* Define if you have the inet_ntoa function.  */
#define HAVE_INET_NTOA 1

/* Define if you have the strchr function.  */
#define HAVE_STRCHR 1

/* Define if you have the syslog function.  */
#define HAVE_SYSLOG 1

/* Define if you have the <arpa/inet.h> header file.  */
#define HAVE_ARPA_INET_H 1

/* Define if you have the <errno.h> header file.  */
#define HAVE_ERRNO_H 1

/* Define if you have the <strings.h> header file.  */
/* #undef HAVE_STRINGS_H */

/* Define if you have the <sys/errno.h> header file.  */
#define HAVE_SYS_ERRNO_H 1

/* Define if you have the <sys/syslog.h> header file.  */
#define HAVE_SYS_SYSLOG_H 1

/* Define if you have the <syslog.h> header file.  */
#define HAVE_SYSLOG_H 1

/* Define if you have the <unistd.h> header file.  */
#define HAVE_UNISTD_H 1

/* Define if you have the gen library (-lgen).  */
/* #undef HAVE_LIBGEN */

/* Define if you have the nsl library (-lnsl).  */
#define HAVE_LIBNSL 1

/* Define if you have the socket library (-lsocket).  */
/* #undef HAVE_LIBSOCKET */

/* Which syslog facility to use for the ffingerd warning messages
 *    The syslog messages ffingerd can generate are listed in README */
#define LOG_FACILITY LOG_INFO

/* Define this for fascist logging (even successful finger attempts are
 *  * logged to syslog */
/* #undef FASCIST_LOGGING */

/* Define it if you want IPv6 support */
/* #undef INET6 */
