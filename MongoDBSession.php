<?php

namespace FedorovVldmr;

class MongoDBSession
{
	private static $instance;
	private $instanceConfig = [];
	private $client;
	private $db;
	private $collection;
	private $sessionData;
	
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
	
	public static function config(array $config = [])
	{
		self::$config = array_merge(self::$config, $config);
	}
	
	public function setInstanceConfig($config)
	{
		$this->instanceConfig = $config;
	}
	
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	private function __construct()
	{
		$this->setInstanceConfig(self::$config);
		
		$this->client = new \MongoDB\Client($this->instanceConfig['uri'], $this->instanceConfig['uri_options'], $this->instanceConfig['driver_options']);
		$this->db = $this->client->{$this->instanceConfig['db']};
		$this->collection = $this->db->{$this->instanceConfig['collection']};
		
		ini_set('session.auto_start', (int) $this->instanceConfig['auto_start']);
		ini_set('session.gc_probability', (int) $this->instanceConfig['gc_probability']);
		ini_set('session.gc_divisor', $this->instanceConfig['gc_divisor']);
		ini_set('session.gc_maxlifetime', $this->instanceConfig['lifetime']);
		ini_set('session.referer_check', $this->instanceConfig['referer_check']);
		ini_set('session.entropy_file', $this->instanceConfig['entropy_file']);
		ini_set('session.entropy_length', $this->instanceConfig['entropy_length']);
		ini_set('session.use_strict_mode', (int) $this->instanceConfig['use_strict_mode']);
		ini_set('session.use_cookies', (int) $this->instanceConfig['use_cookies']);
		ini_set('session.use_only_cookies', (int) $this->instanceConfig['use_only_cookies']);
		ini_set('session.cookie_lifetime', $this->instanceConfig['lifetime']);
		ini_set('session.cookie_path', $this->instanceConfig['cookie_path']);
		ini_set('session.cookie_domain', $this->instanceConfig['cookie_domain']);
		ini_set('session.cookie_path', $this->instanceConfig['cookie_path']);
		ini_set('session.cookie_secure', $this->instanceConfig['cookie_secure']);
		ini_set('session.cookie_httponly', $this->instanceConfig['cookie_httponly']);
		ini_set('session.cache_limiter', $this->instanceConfig['cache_limiter']);
		ini_set('session.cache_expire', $this->instanceConfig['cache_expire']);
		
		session_name($this->instanceConfig['session_name']);
		register_shutdown_function('session_write_close');
		session_set_cookie_params($this->instanceConfig['lifetime'], $this->instanceConfig['cookie_path'], $this->instanceConfig['cookie_domain'], $this->instanceConfig['cookie_secure'], $this->instanceConfig['cookie_httponly']);
		session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));
		
		session_start();
		setcookie($this->instanceConfig['session_name'], session_id(), time() + $this->instanceConfig['lifetime'], $this->instanceConfig['cookie_path'], $this->instanceConfig['cookie_domain'], $this->instanceConfig['cookie_secure'], $this->instanceConfig['cookie_httponly']);
	}
	
	public function __destruct()
	{
		session_write_close();
	}
	
	public function open($path, $sessionName)
	{
		return true;
	}
	
	public function close()
	{
		return true;
	}
	
	public function read($session_id)
	{
		$this->sessionData = $this->collection->findOne(['_id' => $session_id]);
		
		return (!is_null($this->sessionData)) ? $this->sessionData->data : '';
	}
	
	public function write($session_id, $data)
	{
		if($data === '')
		{
			return false;
		}
		
		if(!$this->sessionData)
		{
			$this->sessionData = [
				'_id'     => $session_id,
				'started' => new \MongoDB\BSON\UTCDateTime(),
			];
		}
		
		$this->sessionData = [
			'last_access' => new \MongoDB\BSON\UTCDateTime(),
			'data'        => $data,
		];
		
		$result = $this->collection->updateOne(['_id' => $session_id], ['$set' => $this->sessionData], ['upsert' => true]);
		
		return true;
	}
	
	public function destroy($session_id)
	{
		$result = $this->collection->deleteMany(['_id' => $session_id]);
	}
	
	public function gc($lifetime = 0)
	{
		$time = ($lifetime !== 0) ? $lifetime : $this->instanceConfig['lifetime'];
		$this->collection->deleteMany([
			'last_access' => new \MongoDB\BSON\UTCDateTime(time() - $time),
		]);
	}
}
