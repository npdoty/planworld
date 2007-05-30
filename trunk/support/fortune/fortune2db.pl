#!/usr/bin/perl

use strict;

my $fortune_file = 'fortune.txt';
my ($cookie, $i);

open(FORTUNE, $fortune_file);
while(<FORTUNE>) {
  chomp;
  if ($_) {
    s/\'/\\\'/g;
    $cookie .= $_ . "<br>\n";
  } else {
    $i++;
    $cookie =~ s/\<br\>$//g;
    my $author;
    if ($cookie =~ s/\s{1,2}\-\-\s(.+)$//g) {
      $author = $1;
    }
    print "INSERT INTO cookies (id, quote, author, approved) VALUES ($i, '$cookie', '$author', 'Y');\n";
    $cookie = '';
  }
}
close(FORTUNE);
