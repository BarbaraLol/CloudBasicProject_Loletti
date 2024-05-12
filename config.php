<?php
$CONFIG = array (
  'htaccess.RewriteBase' => '/',
  'memcache.local' => '\\OC\\Memcache\\APCu',
  'apps_paths' => 
  array (
    0 => 
    array (
      'path' => '/var/www/html/apps',
      'url' => '/apps',
      'writable' => false,
    ),
    1 => 
    array (
      'path' => '/var/www/html/custom_apps',
      'url' => '/custom_apps',
      'writable' => true,
    ),
  ),
  'upgrade.disable-web' => true,
  'instanceid' => 'ocj72h16idm7',
  'passwordsalt' => 'Hj4qBjVXt8pIoX9QXfeBgjzWV6xHkq',
  'secret' => 'YBXn/iYiGZHwR7RWyGUp1eUaj+RhsQvuC/uF9t7wgv4WSS0T',
  'trusted_domains' => 
  array (
    0 => 'localhost:8080',
    1 => 'nextcloud_instance',
    2 => 'nextcloud_instance',
    3 => 'nextcloud_nginx',
  ),
  'datadirectory' => '/var/www/html/data',
  'dbtype' => 'mysql',
  'version' => '28.0.3.2',
  'overwrite.cli.url' => 'http://localhost:8080',
  'dbname' => 'nextcloud',
  'dbhost' => 'nextcloud-db',
  'dbport' => '',
  'dbtableprefix' => 'oc_',
  'mysql.utf8mb4' => true,
  'dbuser' => 'nextcloud',
  'dbpassword' => 'your-user-password',
  'installed' => true,
  'ratelimit.protection.enabled' => false,
  'filelocking.enabled' => false,
  'memcache.distributed' => '\\OC\\Memcache\\Redis',
  'memcache.locking' => '\\OC\\Memcache\\Redis',
  'redis' => 
  array (
    'host' => 'redis',
    'password' => '',
    'port' => 6379,
  ),
);
