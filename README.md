asf-php
=======

another simple framework for PHP


Important:
if you need use rewrite for your server. please use follow rewrite rule:

for nginx:

   * rewrite ^/([a-z][\-0-9a-z]*)/?$ index.php?\_\_c=$1 last;
   * rewrite ^/([a-z][\-0-9a-z]*)/([a-z][\-0-9a-z]*)/?$ index.php?\_\_c=$1&\_\_a=$2 last;
  
for Apache

   * RewriteEngine on
   * RewriteRule ^/([a-z][\-0-9a-z]*)/?$ /index.php?\_\_c=$1 [QSA,L]
   * RewriteRule ^/([a-z][\-0-9a-z]*)/([a-z][\-0-9a-z]*)/?$ /index.php?\_\_c=$1&\_\_a=$2 [QSA,L]
