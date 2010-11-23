Installation
============================================================================================================

1. Create a database called "finance" and use the data/mysql.sql file to create the table structure.
2. Create a virtual host in apache with the public folder as the document root. (example below)
3. Go to the url in your web browser and create a user.

VirtualHost
============================================================================================================
    <VirtualHost *:80>
       DocumentRoot "PATH_TO_FILES/public"
       ServerName finance.local
       <Directory "PATH_TO_FILES/public">
           SetEnv APPLICATION_ENV production
           Options Indexes MultiViews FollowSymLinks
           AllowOverride None
           Order allow,deny
           Allow from all
           <IfModule mod_rewrite.c>
               RewriteEngine On
               RewriteCond %{REQUEST_FILENAME} -s [OR]
               RewriteCond %{REQUEST_FILENAME} -l [OR]
               RewriteCond %{REQUEST_FILENAME} -d
               RewriteRule ^.*$ - [NC,L]
               RewriteRule ^(css|js)/(.*)\.[0-9]+\.(.*)$ /$1/$2.$3 [NC,L]
               RewriteRule ^.*$ index.php [NC,L]
           </IfModule>
       </Directory>
    </VirtualHost>
