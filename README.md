Fluid
Michael Rajotte - 2016 June

Fluid is a php/javascript/mysql/bootstrap 3 driven e-commerce website platform.

It has payment integration with PayPal, moneris and several other payment gateways. 

Requirements:
Basic web server with Composer, Curl, Git, MySQL, csvsql and PHP with JSON, PHP-CURL, PHP-XML, PHP-IMAP PHP-SOAP and PECL intl (for locale) support php-pecl php-intl.

How to install:

(1). Run the attached MySQL file to setup the database.
(2). Create a file in ../fluid called fluid.db.php. Or use the fluid.db.php.setup file instead.
(3). You will need to setup a image and files alias on the admin panel so fluid/admin/images is the same as fluid/htdocs/images and the same for fluid/admin/files to fluid/htdocs/files. This is required for imagedropzone to function properly when loading images from a cross domain. Only required for image uploading in the editor. The file alias is required for the banner editor for loading images from the /file folder. Alias uploads is required for the banner file manager uploader.
	--> In Apache for example you would add the line:
			Alias /images /var/www/local/fluid/htdocs/images
			Alias /files /var/www/local/fluid/htdocs/files
			Alias /uploads /var/www/local/fluid/htdocs/uploads
(4). For using 3D bin packing, you must go into the 3rd-party-src/packing-api and run "composer install" to install the dependencies.
	(a). Installing composer: curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
	(b). fluid/3rd-party-src/packing-api -> run "composer require dvdoug/boxpacker" to install the dependencies.
	(c). NOTE: For 3D Bin packing, at the moment, Fluid is only compatible with older versions ie: 2.3 or lower. Newer versions of bin packing require php 7.1 or higher and changes to the fluid.box.php code to fix :void type errors.
(5). Moneris api.
	(a). Open port 43924
	(b). fluid/3rd-party-src/moneris-api -> run "composer install"
(6). Enable mod_rewrite in the Apache server with override ALL. .htaccess file include in the /htdocs folder.
(7). For using a EPSON printer eospos-api. php mbstring is required. php-guzzlehttp and  php-ext-imagick recommended.
(8). Make sure admin/tmp, htdocs/images, htdocs/cached, htdocs/temp folders are set to chmod writeable
(9). Banwords-api.

NOTE: There are .htaccess files in both admin/htdocs and /htdocs, so you need to enable AllowOverride Yes in apache2 configuration for these folders. Required for css and js auto-versioning and folder structures.
NOTE: A file below the /fluid folder needs to be created called: fluid.db.php and it needs to have the following information which will need to be changed depending on your installation setup. You may copy and rename fluid.db.php.setup in this root folder as a template basis to use, which should be renamed to fluid.db.php and move into 1 folder up as described in step #2 above:
NOTE: If you get a Negotiation: discovered file(s) matching request: in the apache error.log file, this may be due to broken rewrite rules, particularlly on certain .php files if they dont load correct, so in /etc/apache2/mods-available/mime.conf or the into apache mod_mime section into httpd.conf, add the following line without the quotations: "AddType application/x-httpd-php .php"
