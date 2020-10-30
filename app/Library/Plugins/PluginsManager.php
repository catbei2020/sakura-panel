<?php 

namespace Sakura\Library\Plugins;

use Sakura\Controllers\ControllerBase;

/**
 * Pluginsmanager
 */
class Pluginsmanager extends ControllerBase
{
	protected $plugins = [];
	

	/**
	 * check plugin info 
	 * @param $key
	 * @param $default = null
	 * @return $info
	 */
	public function has($key)
	{
		return !empty($this->plugins[$key]);
	}

	/**
	 * get plugin info 
	 * @param $key
	 * @param $default = null
	 * @return $info
	 */
	public function get($key)
	{
		return $this->plugins[$key] ?? false;
	}

	/**
	 * set plugin info
	 * @param $key
	 * @param $val
	 */
	public function set($key , $val = null)
	{
		$this->plugins[$key] = $val;
	}

	/**
	 * Add Plugin
	 * @param $plugin : Plugin
	 * @return $this : Pluginsmanager
	 */
	public function addPlugin($plugin)
	{
		if ($plugin instanceof Plugin)
			$this->plugins[$plugin->get('name')] = $plugin;
		else
			throw new Exception("Plugin $plugin is not a valide plugin ! ");
		

		return $this;
	}


	/**
	 * Load Plugins
	 * @return $this : Pluginsmanager
	 */
	public function loadPlugins()
	{
		$this->loadPluginsFromDirs();
		foreach ($this->plugins as $key => $plugin) {
			if ($plugin->get('status')) {
				try{
					$plugin->load($this->getDI());
				}catch(Exception $e){
					$this->getDI()->warning("Plugin Bot: ". $e->getMessage());
				}
			}
		}

		return $this;
	}

	/**
	 * loadFilesPlugins
	 */
	public function loadPluginsFromDirs()
	{
		$pluginsFolder = $this->di->getConfig()->application->pluginsDir ?? BASE_PATH.'/plugins';	

	    if (!is_dir($pluginsFolder)) return;

		try {   
		    $plugin_config_name = $this::PLUGIN_CONFIG_NAME;

		    $listPFolders = scandir($pluginsFolder);
		    
		    foreach ($listPFolders as $name) {
		        $_path = str_replace("//", "/", $pluginsFolder . '/'. $name.'/');

		        if (is_dir($_path) && in_array($plugin_config_name, scandir($_path))) {
					$groupName = ucfirst($name);
					try{
						
						$plugin_config = include($_path . $plugin_config_name);
						if (is_object($plugin_config) && $plugin instanceof Plugin)
							$this->addPlugin($plugin_config);
							
					}catch(Exception $e){
						$this->getDI()->getLogger()->warning("Plugin Include : ". $e->getMessage());
					}
		            
		        }      
		    }
 
		} catch (\Exception $e) {
	
		    if (getenv('APP_DEBUG') === true)
			    exit($e->getMessage());
			else
				$this->getDi()->getLogger()->error($e->getMessage());
			
		}
	}
}