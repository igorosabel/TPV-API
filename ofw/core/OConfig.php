<?php declare(strict_types=1);
/**
 * OConfig - Class with all the configuration info for the framework
 */
class OConfig {
	private array $log = [
		'dir' => null,
		'level' => 'DEBUG'
	];
	private bool $allow_cross_origin = true;

	private array $plugins  = [];

	private array $dirs = [];
	private array $db = [
		'driver'  => 'mysql',
		'user'    => '',
		'pass'    => '',
		'host'    => '',
		'name'    => '',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_unicode_ci'
	];
	private array $urls = [
		'base'   => ''
	];

	private array $smtp = [
		'host'   => '',
		'port'   => null,
		'secure' => 'tls',
		'user'   => '',
		'pass'   => ''
	];

	private string $cookie_prefix = '';
	private string $cookie_url    = '';

	private ?array $url_list = null;

	private array $error_pages  = [
		'403' => null,
		'404' => null,
		'500' => null
	];

	private array  $css_list      = [];
	private array  $ext_css_list  = [];
	private array  $js_list       = [];
	private array  $ext_js_list   = [];
	private string $default_title = '';
	private string $admin_email   = '';
	private string $mailing_from  = '';
	private string $lang          = 'es';
	private array  $image_types   = [];

	private array $libs   = [];
	private array $extras = [];

	/**
	 * Load app/config/config.json file into this class wich provides methods to access them
	 *
	 * @param string $bd Base directory of the application
	 */
	function __construct(string $bd) {
		$this->setBaseDir($bd);
		$json_file = $this->getDir('app_config').'config.json';
		if (!file_exists($json_file)) {
			echo "ERROR: config.json file not found.\n";
			exit;
		}
		$config = json_decode( file_get_contents($json_file), true );
		if (!$config) {
			echo "ERROR: config.json file is malformed.\n";
			exit;
		}
		$this->loadConfig($config);
		if (array_key_exists('environment', $config)) {
			$json_env_file = $this->getDir('app_config').'config.'.$config['environment'].'.json';
			if (!file_exists($json_env_file)) {
				echo "ERROR: config.".$config['environment'].".json file not found.\n";
				exit;
			}
			$config_env = json_decode( file_get_contents($json_env_file), true );
			if (!$config_env) {
				echo "ERROR: config.".$config['environment'].".json file is malformed.\n";
				exit;
			}
			$this->loadConfig($config_env);
		}
		$plugins_file = $this->getDir('app_config').'plugins.json';
		if (file_exists($plugins_file)) {
			$plugins = json_decode( file_get_contents($plugins_file), true );
			if (array_key_exists('plugins', $plugins) && is_array($plugins['plugins'])) {
				$this->setPlugins($plugins['plugins']);
			}
		}
	}

	/**
	 * Load a specific configuration file
	 *
	 * @param array $config Key / Value pairs with the configuration of the application
	 *
	 * @return void
	 */
	private function loadConfig(array $config): void {
		if (array_key_exists('db', $config)) {
			$db_fields = ['driver', 'host', 'user', 'pass', 'name', 'charset', 'collate'];
			foreach ($db_fields as $db_field) {
				if (array_key_exists($db_field, $config['db'])) {
					$this->setDB($db_field, $config['db'][$db_field]);
				}
			}
		}
		if (array_key_exists('cookies', $config)) {
			if (array_key_exists('prefix', $config['cookies'])) {
				$this->setCookiePrefix($config['cookies']['prefix']);
			}
			if (array_key_exists('url', $config['cookies'])) {
				$this->setCookieUrl($config['cookies']['url']);
			}
		}
		if (array_key_exists('log_level', $config)) {
			$this->setLog('level', $config['log_level']);
		}
		if (array_key_exists('log_dir', $config)) {
			$this->setLog('dir', $config['log_dir']);
		}
		if (array_key_exists('base_url', $config)) {
			$this->setUrl('base', $config['base_url']);
		}
		if (array_key_exists('default_title', $config)) {
			$this->setDefaultTitle($config['default_title']);
		}
		if (array_key_exists('lang', $config)) {
			$this->setLang($config['lang']);
		}
		if (array_key_exists('smtp', $config)) {
			$smtp_fields = ['host', 'port', 'secure', 'user', 'pass'];
			foreach ($smtp_fields as $smtp_field) {
				if (array_key_exists($smtp_field, $config['smtp'])) {
					$this->setSMTP($smtp_field, $config['smtp'][$smtp_field]);
				}
			}
		}
		if (array_key_exists('error_pages', $config)) {
			$error_fields = ['404', '403', '500'];
			foreach ($error_fields as $error_field) {
				if (array_key_exists($error_field, $config['error_pages'])) {
					$this->setErrorPage($error_field, $config['error_pages'][$error_field]);
				}
			}
		}
		if (array_key_exists('css', $config)) {
			$this->setCssList($config['css']);
		}
		if (array_key_exists('ext_css', $config)) {
			$this->setExtCssList($config['ext_css']);
		}
		if (array_key_exists('js', $config)) {
			$this->setJsList($config['js']);
		}
		if (array_key_exists('ext_js', $config)) {
			$this->setExtJsList($config['ext_js']);
		}
		if (array_key_exists('extra', $config)) {
			foreach ($config['extra'] as $key => $value){
				$this->setExtra($key, $value);
			}
		}
		if (array_key_exists('dir', $config)) {
			$dir_list = $this->getDir();
			$dir_from = [];
			$dir_to = [];
			foreach ($dir_list as $key => $value) {
				array_push($dir_from, '{{'.$key.'}}');
				array_push($dir_to, $value);
			}
			foreach ($config['dir'] as $key => $value) {
				$this->setDir($key, str_ireplace($dir_from, $dir_to, $value));
			}
		}
		if (array_key_exists('libs', $config)) {
			$this->setLibs($config['libs']);
		}
	}

	/**
	 * Set information for the logging tools
	 *
	 * @param string $key "dir" -log directory- of "level" -logging importance level-
	 *
	 * @param string $value Value of the logging configuration
	 *
	 * @return void
	 */
	public function setLog(string $key, string $value): void {
		$this->log[$key] = $value;
	}

	/**
	 * Get value of the logging configuration
	 *
	 * @param string $key "dir" -log directory- of "level" -logging importance level-
	 *
	 * @return ?string Value of the logging configuration
	 */
	public function getLog(string $key): ?string {
		return array_key_exists($key, $this->log) ? $this->log[$key] : null;
	}

	/**
	 * Set if Cross-Origin calls are allowed
	 *
	 * @param bool $value Value of the Cross-Origin configuration
	 *
	 * @return void
	 */
	public function setAllowCrossOrigin(string $value): void {
		$this->allow_cross_origin = $value;
	}

	/**
	 * Get if Cross-Origin calls are allowed
	 *
	 * @return bool Value of the Cross-Origin configuration
	 */
	public function getAllowCrossOrigin(): bool {
		return $this->allow_cross_origin;
	}

	/**
	 * Set list of installed plugins
	 *
	 * @param array $p Installed plugin list
	 *
	 * @return void
	 */
	public function setPlugins(array $p): void {
		$this->plugins = $p;
	}

	/**
	 * Get full list of installed plugins
	 *
	 * @return array List of installed plugins
	 */
	public function getPlugins(): array {
		return $this->plugins;
	}

	/**
	 * Check if plugin is installed
	 *
	 * @param string $p Code name of the plugin
	 *
	 * @return bool Plugin is installed or not
	 */
	public function getPlugin(string $p): bool {
		return in_array($p, $this->plugins);
	}

	/**
	 * Set list of framework and user defined directories
	 *
	 * @param string $dir Name or code of the directory
	 *
	 * @param string $value Full path of the directory
	 *
	 * @return void
	 */
	public function setDir(string $dir, string $value): void {
		$this->dirs[$dir] = $value;
	}

	/**
	* Get path of a given directory or full list of configured directories if ommitted
	*
	* @param string $dir Name or code of the directory
	*
	* @return string|array Path of requested directory or full list of configured directories
	*/
	public function getDir(string $dir=null) {
		if (is_null($dir)) {
			return $this->dirs;
		}
		return array_key_exists($dir, $this->dirs) ? $this->dirs[$dir] : null;
	}

	/**
	 * Sets up framework internal directories based on applications base directory
	 *
	 * @param string $bd Base directory of the application
	 *
	 * @return void
	 */
	private function setBaseDir(string $bd): void {
		$this->setDir('base',           $bd);
		$this->setDir('app',            $bd.'app/');
		$this->setDir('app_cache',      $bd.'app/cache/');
		$this->setDir('app_config',     $bd.'app/config/');
		$this->setDir('app_controller', $bd.'app/controller/');
		$this->setDir('app_filter',     $bd.'app/filter/');
		$this->setDir('app_model',      $bd.'app/model/');
		$this->setDir('app_service',    $bd.'app/service/');
		$this->setDir('app_template',   $bd.'app/template/');
		$this->setDir('app_task',       $bd.'app/task/');
		$this->setDir('ofw_core',       $bd.'ofw/core/');
		$this->setDir('ofw_lib',        $bd.'ofw/lib/');
		$this->setDir('ofw_locale',     $bd.'ofw/locale/');
		$this->setDir('ofw_plugins',    $bd.'ofw/plugins/');
		$this->setDir('ofw_task',       $bd.'ofw/task/');
		$this->setDir('ofw_export',     $bd.'ofw/export/');
		$this->setDir('ofw_template',   $bd.'ofw/template/');
		$this->setDir('ofw_tmp',        $bd.'ofw/tmp/');
		$this->setDir('logs',           $bd.'logs/');
		$this->setDir('debug_log',      $bd.'logs/debug.log');
		$this->setDir('web',            $bd.'web/');

		// If user hasn't defined logs directory, set a default one
		if (is_null($this->getLog('dir'))){
			$this->setLog('dir', $this->getDir('debug_log'));
		}
	}

	/**
	 * Set database configuration values
	 *
	 * @param string $key Database configuration key (driver, user, pass, host, name, charset or collate)
	 *
	 * @param string $value Configuration value
	 *
	 * @return void
	 */
	public function setDB(string $key, string $value): void {
		$this->db[$key] = $value;
	}

	/**
	 * Get database configuration value
	 *
	 * @param string $key Database configuration key (driver, user, pass, host, name, charset or collate)
	 *
	 * @return string Configuration value
	 */
	public function getDB(string $key): string {
		return array_key_exists($key, $this->db) ? $this->db[$key] : null;
	}

	/**
	 * Set up a URL with a key
	 *
	 * @param string $key Key code of a URL
	 *
	 * @param string $url URL to be stored
	 *
	 * @return void
	 */
	public function setUrl(string $key, string $url): void {
		$this->urls[$key] = $url;
	}

	/**
	* Get a stored URL based on a key
	*
	* @param string $key Key code of the URL to be retrieved
	*
	* @return string Stored URL or null if key doesn't exist
	*/
	public function getUrl(string $key): string {
		return array_key_exists($key, $this->urls) ? $this->urls[$key] : null;
	}

	/**
	 * Set up configuration for SMTP based email sendings
	 *
	 * @param string $key Configuration key (host / port / secure / user / pass)
	 *
	 * @param string|int $value Configuration value
	 *
	 * @return void
	 */
	public function setSMTP(string $key, $value): void {
		$this->smtp[$key] = $value;
	}

	/**
	 * Get SMTP configuration based on a given key or get all the configuration values if ommitted
	 *
	 * @param string $key Configuration key (host / port / secure / user / pass)
	 *
	 * @return string|int|array Required configuration value or whole configuration if key is ommitted
	 */
	public function getSMTP(string $key=null) {
		if (is_null($key)) {
			return $this->smtp;
		}
		return array_key_exists($key, $this->smtp) ? $this->smtp[$key] : null;
	}

	/**
	 * Set up a prefix for the cookies used in the application
	 *
	 * @param string $cp Cookie prefix (eg osumi-)
	 *
	 * @return void
	 */
	public function setCookiePrefix(string $cp): void {
		$this->cookie_prefix = $cp;
	}

	/**
	 * Get the previously configured cookie prefix
	 *
	 * @return string Cookie prefix (eg osumi-)
	 */
	public function getCookiePrefix(): string {
		return $this->cookie_prefix;
	}

	/**
	 * Set up the URL to be used in the cookies
	 *
	 * @param string $cu URL of the cookies
	 *
	 * @return void
	 */
	public function setCookieUrl(string $cu): void {
		$this->cookie_url = $cu;
	}

	/**
	 * Get the previously configured cookie URL
	 *
	 * @return string URL of the cookies
	 */
	public function getCookieUrl(): string {
		return $this->cookie_url;
	}

	/**
	 * Store in memory flattened/cached URL list of the application
	 *
	 * @param array Array of the application URLs and their configuration
	 *
	 * @return void
	 */
	public function setUrlList(array $u): void {
		$this->url_list = $u;
	}

	/**
	 * Retrieve stored flattened/cache URL list
	 *
	 * @return array Array of the application URLs and their configuration
	 */
	public function getUrlList(): ?array {
		return $this->url_list;
	}

	/**
	 * Set up a customized URL for a given error status (403, 404, 500)
	 *
	 * @param string $status Status code where user has to be redirected (403, 404, 500)
	 *
	 * @param string $url URL where the user has to be redirected
	 *
	 * @return void
	 */
	public function setErrorPage(string $status, string $url): void {
		$this->error_pages[$status] = $url;
	}

	/**
	 * Get the URL where the user has to be redirected on a given HTTP status code or null if it hasn't been customized
	 *
	 * @param string $status Status code to be checked
	 *
	 * @return string URL where the user has to be redirected
	 */
	public function getErrorPage(string $status): ?string {
		if (array_key_exists($status, $this->error_pages)){
			return $this->error_pages[$status];
		}
		return null;
	}

	/**
	 * Set array of CSS files to be used in the application
	 *
	 * @param string[] $cl Array of CSS file names to be included
	 *
	 * @return void
	 */
	public function setCssList(array $cl): void {
		$this->css_list = $cl;
	}

	/**
	 * Get array of CSS file names to be included in the application
	 *
	 * @return string[] Array of CSS file names to be included
	 */
	public function getCssList(): array {
		return $this->css_list;
	}

	/**
	 * Adds a single item to the array of CSS files to be included in the application
	 *
	 * @param string $item Name of a CSS file to be included
	 *
	 * @return void
	 */
	public function addCssList(string $item): void {
		array_push($this->css_list, $item);
	}

	/**
	 * Set array of external CSS file URLs to be used in the application (eg in a CDN)
	 *
	 * @param string[] $ecl Array of external CSS file URLs to be included
	 *
	 * @return void
	 */
	public function setExtCssList(array $ecl): void {
		$this->ext_css_list = $ecl;
	}

	/**
	 * Get array of external CSS file URLs to be included in the application
	 *
	 * @return string[] Array of external CSS file URLs to be included
	 */
	public function getExtCssList(): array {
		return $this->ext_css_list;
	}

	/**
	 * Adds a single item to the array of external CSS file URLs to be included in the application
	 *
	 * @param string $item Name of a CSS file URL to be included
	 *
	 * @return void
	 */
	public function addExtCssList(string $item): void {
		array_push($this->ext_css_list, $item);
	}

	/**
	 * Set array of JS files to be used in the application
	 *
	 * @param string[] $jl Array of JS file names to be included
	 *
	 * @return void
	 */
	public function setJsList(array $jl): void {
		$this->js_list = $jl;
	}

	/**
	 * Get array of JS file names to be included in the application
	 *
	 * @return string[] Array of JS file names to be included
	 */
	public function getJsList(): array {
		return $this->js_list;
	}

	/**
	 * Adds a single item to the array of JS files to be included in the application
	 *
	 * @param string $item Name of a JS file to be included
	 *
	 * @return void
	 */
	public function addJsList(string $item): void {
		array_push($this->js_list, $item);
	}

	/**
	 * Set array of external JS file URLs to be used in the application (eg in a CDN)
	 *
	 * @param string[] $ejl Array of external JS file URLs to be included
	 *
	 * @return void
	 */
	public function setExtJsList(array $ejl): void {
		$this->ext_js_list = $ejl;
	}

	/**
	 * Get array of external JS file URLs to be included in the application
	 *
	 * @return string[] Array of external JS file URLs to be included
	 */
	public function getExtJsList(): array {
		return $this->ext_js_list;
	}

	/**
	 * Adds a single item to the array of external JS file URLs to be included in the application
	 *
	 * @param string $item Name of a JS file URL to be included
	 *
	 * @return void
	 */
	public function addExtJsList(string $item): void {
		array_push($this->ext_js_list, $item);
	}

	/**
	 * Set up the default title that will be shown in every page of the application (in <title> tag)
	 *
	 * @param string $dt Default title
	 *
	 * @return void
	 */
	public function setDefaultTitle(string $dt): void {
		$this->default_title = $dt;
	}

	/**
	 * Get the default title for the application
	 *
	 * @return string Default title
	 */
	public function getDefaultTitle(): string {
		return $this->default_title;
	}

	/**
	 * Set up the language code for the application (eg "es", "en", "eu"...)
	 *
	 * @param string $l Language code
	 *
	 * @return void
	 */
	public function setLang(string $l): void {
		$this->lang = $l;
	}

	/**
	 * Get the language code for the application
	 *
	 * @return string Language code
	 */
	public function getLang(): string {
		return $this->lang;
	}

	/**
	 * Set up the senders email address when sending emails
	 *
	 * @param string $mf Senders email address
	 *
	 * @return void
	 */
	public function setMailingFrom(string $mf): void {
		$this->mailing_from = $mf;
	}

	/**
	 * Get the senders email address when sending emails
	 *
	 * @return string Senders email address
	 */
	public function getMailingFrom(): string {
		return $this->mailing_from;
	}

	/**
	 * Set up the list of third-party libraries to be loaded into the application
	 *
	 * @param string[] $l Array of third-party library names
	 *
	 * @return void
	 */
	public function setLibs(array $l): void {
		$this->libs = $l;
	}

	/**
	 * Get the list of third-party libraries loaded into the application
	 *
	 * @return string[] Array of third-party library names
	 */
	public function getLibs(): array {
		return $this->libs;
	}

	/**
	 * Add a single library to the list of third-party libraries to be loaded
	 *
	 * @param string $item Name of the library to be loaded
	 *
	 * @return void
	 */
	public function addLib(string $item): void {
		array_push($this->libs, $item);
	}

	/**
	 * Set a customized key / value pair (eg encryption secret, custom token...)
	 *
	 * @param string $key Key of the item to be stored
	 *
	 * @param string|int|float|bool $value Value of the stored item
	 *
	 * @return void
	 */
	public function setExtra(string $key, $value): void {
		$this->extras[$key] = $value;
	}

	/**
	 * Get the value of the stored item
	 *
	 * @param string $key Key of the item to be retrieved
	 *
	 * @return string|int|float|bool Value of the stored item
	 */
	public function getExtra(string $key) {
		return array_key_exists($key, $this->extras) ? $this->extras[$key] : null;
	}
}