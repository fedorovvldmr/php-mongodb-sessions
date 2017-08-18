# php-mongodb-sessions

## Description
Reading / writing php sessions in MongoDB

## Requirements
* PHP 5.5+
* PHP MongoDB driver version 1.2+
* mongodb/mongo-php-library

## Usage
	MongoDBSession::getInstance(); // singleton
	
	$_SESSION['hi'] = 'Hello, world!';
	echo $_SESSION['hi'];
	
## Configuration
	MongoDBSession::config([
		'db'            => 'admin',
		'lifetime'      => ini_get('session.gc_maxlifetime'),
		'cookie_domain' => '.' . $_SERVER['HTTP_HOST']
		]);
	
Call before MongoDBSession::getInstance().

	private static $config = [
      'session_name'     => 'PHPSESSID',
      'uri'              => 'mongodb://localhost:27017',
      'db'               => 'admin',
      'collection'       => 'sessions',
      'lifetime'         => 3600,
      'cookie_path'      => '/',
      'cookie_domain'    => '.domain.com',
      'cookie_secure'    => false,
      'cookie_httponly'  => false,
      'uri_options'      => [],
      'driver_options'   => [],
      'auto_start'       => true,
      'gc_probability'   => true,
      'gc_divisor'       => 100,
      'referer_check'    => '',
      'entropy_file'     => '/dev/urandom',
      'entropy_length'   => 32,
      'use_strict_mode'  => false,
      'use_cookies'      => true,
      'use_only_cookies' => true,
      'cache_limiter'    => 'nocache',
      'cache_expire'     => 180,
      ];
      
[Configuration params](http://php.net/manual/ru/session.configuration.php)