/* $Id: ffingerd.c,v 1.6.4.1 2003/03/17 15:44:46 seth Exp $ */

/* Please read the file README !
 *	This is Fefe's finger daemon, the current version is available from
 *		ftp://ftp.fu-berlin.de/pub/unix/security/ffingerd/
 *	There is a home page for this finger daemon, too, the URL is
 *		http://www.fefe.de/ffingerd/
 */

/* All that appears to need to be changed is dump_file()
 * Oh, and adding appropriate MySQL calls in the right places
 */

#include <config.h>

#include <stdio.h>

#include <string.h>
#include <stdlib.h>

#include <unistd.h>
#include <sys/types.h>

#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
/* #include <pwd.h> */
#include <netdb.h>
#include <sys/stat.h>

# include <syslog.h>

#include <errno.h>
#include <sys/errno.h>

/* Seth and James' mysql mods */
#include <mysql/mysql.h>
#include <time.h>
#include <ctype.h>

#define QUERYSTRING_LEN (128)

void dump_plan(char *username, char *found_message, char *not_found_message) {
	MYSQL mysql;
	MYSQL_RES *result;
	MYSQL_ROW row;

	char query[QUERYSTRING_LEN+1];
	unsigned int userid;
	time_t last_login = 0;
	time_t last_updated = 0;

	char timestr[80];
	struct tm *tp;

	/* sanity check username, 
	 * which had better be null terminated */
	char *uptr = username;
	while(isalnum(*uptr)) { uptr++; }
	*uptr = 0; /* null terminate on first non alpha-num char */
		
	/* connect */
	mysql_init(&mysql);
	mysql_real_connect(&mysql,"localhost","planworld","jo(h)n","planworld",0,NULL,0);

	/* get user id */
	/* query string buffer is QUERYSTRING_LEN chars */
	memset(query,0,QUERYSTRING_LEN+1);
	snprintf(query,QUERYSTRING_LEN,
	"SELECT id, last_login, world, last_update FROM users WHERE username='%s'", username);

	if(mysql_query(&mysql, query)) {
		/* error */
		return;
	}
	result = mysql_store_result(&mysql);

	if (mysql_num_rows(result) > 0) {
		row = mysql_fetch_row(result);
		userid = atoi(row[0]);
		last_login = atoi(row[1]);
		last_updated = atoi(row[3]);
	} else {
		/* couldn't find user */
		printf("Login name: %s\n", username);
		puts(not_found_message);
		return;
	}

	if (last_login == 0) {
	  printf("Login name: %s\nLast login: Never\nLast update: Never\n", username);
	  puts(not_found_message);
	  mysql_free_result(result);
	  return;
	}
	if (*row[2] == 'N') {
	  printf("Login name: %s\n[This user's plan is not available]\n", username);
	  mysql_free_result(result);
	  return;
	}

	mysql_free_result(result);


	/* query string buffer is QUERYSTRING_LEN chars */
	memset(query,0,QUERYSTRING_LEN+1);
	snprintf(query,QUERYSTRING_LEN,
			"SELECT content FROM plans WHERE uid='%d'", userid);
	if(mysql_query(&mysql, query)) {
		/* error */
		return;
	}
	/* success */
	result = mysql_store_result(&mysql);
	
	tp = localtime(&last_login);
	strftime(timestr, sizeof(timestr), "%c", tp);
	printf("Login name: %s\nLast login: %.16s (%s)\n",
			username, timestr, tp->tm_zone);
	
	if (mysql_num_rows(result) > 0) {
	  row = mysql_fetch_row(result);

  	  tp = localtime(&last_updated);
	  strftime(timestr, sizeof(timestr), "%c", tp);
	  printf("Last update: %.16s (%s)\n", timestr, tp->tm_zone);

	  puts(found_message);
	  puts(row[0]);
	} else {
	  tp = localtime(&last_login);
	  printf("Last update: Never\n");
	  puts(not_found_message);
	}

	mysql_free_result(result);
	return;
}

#ifdef NEXT
#define S_ISREG(x) (x & S_IFREG)
#endif

void dump_user(char* user) {

	dump_plan(user, "Plan:", "No Plan.");

/*
	char filename[PATH_MAX+20];
	struct stat stat_buf;
 */

/* change this to a query (external fingering should be an option) */
/*
	sprintf(filename,"%.256s/.nofinger",pwd->pw_dir);
	if (lstat(filename,&stat_buf) && (errno==ENOENT)) {
		sprintf(filename,"%.256s",pwd->pw_gecos);
		if (strchr(filename,',')) { *(char *)strchr(filename,',')=0; }
		printf("Login: %-30s  Name: %-40s\n",pwd->pw_name,filename);
		sprintf(filename,"%.256s/.project",pwd->pw_dir);
		dump_file(filename,"Project:","No project.");
		sprintf(filename,"%.256s/.plan",pwd->pw_dir);
		dump_file(filename,"Plan:","No plan.");
		sprintf(filename,"%.256s/.pubkey",pwd->pw_dir);
		dump_file(filename,"Public key:","No public key.");
	} else {
		char message[1500];
		sprintf(message,"attempt to finger \"%.256s\" from %.1076s\n",pwd->pw_name,remote);
		syslog(LOG_FACILITY,"%s",message);
		puts("That user does not want to be fingered.");
	}
 */
}

int main()
{
	char message[1435];
	unsigned char query[256];
	unsigned char *qptr;
	struct sockaddr_in name;
	char *Remote_IP;
	struct hostent *host;
	int len;
	unsigned long remote;
	char Remote[1077];

	if(geteuid() == 0) {
		syslog(LOG_FACILITY,"ffingerd refuses to run as root");
		fprintf(stderr, "ffingerd refuses to run as root\n");
		printf("Temporarily out of service\n");
		exit(1);
	}

	openlog("fingerd",LOG_PID,LOG_DAEMON);
	len=sizeof(name);
	if (getpeername(0, (struct sockaddr *)&name,&len) == -1) {
/*    perror("getpeername"); */
		if (errno==ENOTSOCK) {
			remote=0x7f000001;
		} else {
			syslog(LOG_ERR,"getpeername: %m");
			closelog();
			exit(0);
		}
	} else {
		if (name.sin_family != AF_INET) {
			syslog(LOG_ERR,"Connection not from INET domain ?!");
			closelog();
			exit(0);
		}
		remote=ntohl(name.sin_addr.s_addr);
		Remote_IP=inet_ntoa(name.sin_addr);
		if ((host=gethostbyaddr((char *)&name.sin_addr,sizeof(struct in_addr),AF_INET))) {
			sprintf(Remote, "%.512s [%.15s]",host->h_name,Remote_IP);
		} else {
			/*      perror("gethostbyaddr");*/
			sprintf(Remote, "%.15s [%.15s]",Remote_IP,Remote_IP);
		}
	}

	if (!fgets(query,255,stdin)) {
		syslog(LOG_FACILITY,"fgets failed");
		closelog();
		exit(1);    
	}

	for (len = 0; query[len]; len++) {
		if (query[len] == '\r' || query[len] == '\n') {
			query[len] = '\0';
			break;
		}
	}    

	if (strchr(query, '@') != NULL) {
		sprintf(message,"indirect finger attempt at %.255s from %.1076s\n",query,Remote);
		syslog(LOG_FACILITY,"%s",message);
		closelog();
		puts("Sorry, we do not support indirect finger queries.");
		exit(0);
	}
	
	qptr=query;
	if (*qptr==' ') qptr++;
	if (*qptr=='/' && (*(qptr+1)=='W' || *(qptr+1)=='w')) qptr+=2;
	if (*qptr==' ') qptr++;

	if (*qptr==0) {
		sprintf(message,"empty finger attempt from %.1076s\n",Remote);
		syslog(LOG_FACILITY,"%s",message);
		closelog();
		puts("Sorry, we do not support empty finger queries.");
		exit(0);
	}

	dump_user(qptr);

/*
	if ((pwd=getpwnam(qptr))) {
		dump_user(pwd,(unsigned char*)Remote);
	} else {
		sprintf(message,"attempt to finger \"%.255s\" from %.1076s\n",qptr,Remote);
		syslog(LOG_FACILITY,"%s",message);
		puts("That user does not want to be fingered.");
	}
*/
	closelog();
	return 0;
}
