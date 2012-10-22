asf-php
=======

another sample framework for PHP


Important:
if you need use rewrite for your server. please use follow rewrite rule:
  for nginx:

   * rewrite ^/([a-z][\-0-9a-z]*)$ index.php?__c=$1 last;
   * rewrite ^/([a-z][\-0-9a-z]*)/([a-z][\-0-9a-z]*)$ index.php?\__c=$1&__a=$2 last;
  
  for Apache

   * RewriteEngine on
   * RewriteRule ^/([a-z][\-0-9a-z]*)$ /index.php?__c=$1 [QSA,L]
   * RewriteRule ^/([a-z][\-0-9a-z]*)/([a-z][\-0-9a-z]*)$ /index.php?\__c=$1&__a=$2 [QSA,L]
