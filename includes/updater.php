<?php

class PDQTrackerUpdater
{
    private $slug;
    private $pluginData;
    private $pluginFile;
    private $storageResponse;
    private $pluginActivated;
	
    function __construct($pluginFile)
    {
        add_filter("pre_set_site_transient_update_plugins", array($this, "setTransitent"));
        add_filter("plugins_api", array($this, "setPluginInfo"), 10, 3);
        add_filter("upgrader_pre_install", array($this, "preInstall"), 10, 3);
        add_filter("upgrader_post_install", array($this, "postInstall"), 10, 3);

        $this->pluginFile = $pluginFile;
    }
	
    private function initPluginData()
    {
		$this->slug = plugin_basename($this->pluginFile);
		$this->pluginData = get_plugin_data($this->pluginFile);
    }
	
	private function getRepoReleaseInfo()
    {
        if(!empty($this->storageResponse))
        {
    		return;
		}
		
		$url = "http://www.ashleyholton.co.uk/cpcw_plugins/pdq/releases.json";
		
		$this->storageResponse = wp_remote_retrieve_body(wp_remote_get($url));

		if(!empty($this->storageResponse))
		{
		    $this->storageResponse = @json_decode($this->storageResponse);
		}
		
		if(is_array($this->storageResponse))
		{
		    $this->storageResponse = $this->storageResponse[0];
		}
    }
	
	public function setTransitent($transient)
    {
        if(empty($transient->checked))
        {
    		return $transient;
		}
		
		$this->initPluginData();
		$this->getRepoReleaseInfo();

		$doUpdate = version_compare($this->storageResponse->version_number, $transient->checked[$this->slug], '>');

		if($doUpdate)
		{
			$package = $this->storageResponse->zip;
			
			$obj = new stdClass();
			$obj->slug = $this->slug;
			$obj->new_version = $this->storageResponse->version_number;
			$obj->url = $this->pluginData["PluginURI"];
			$obj->package = $package;

			$transient->response[$this->slug] = $obj;
		}

        return $transient;
    }
	
    public function setPluginInfo($false, $action, $response)
    {
		$this->initPluginData();
		$this->getRepoReleaseInfo();

		if(empty($response->slug ) || $response->slug != $this->slug)
		{
		    return $false;
		}
		
		$response->last_updated = $this->storageResponse->release_date;
		$response->slug = $this->slug;
		$response->name = $this->pluginData["Name"];
		$response->version = $this->storageResponse->version_number;
		$response->author = $this->pluginData["AuthorName"];
		$response->homepage = $this->pluginData["PluginURI"];
		
		$downloadLink = $this->storageResponse->zip;

		$response->download_link = $downloadLink;
		
		require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'Parsedown.php');
		
		$response->sections = array(
			'Description' => $this->pluginData["Description"],
			'changelog' => class_exists("Parsedown") ? Parsedown::instance()->parse($this->storageResponse->description) : $this->storageResponse->description
		);
		
        return $response;
    }
	
    public function preInstall($true, $args)
    {
		$this->initPluginData();
		
    	$this->pluginActivated = is_plugin_active($this->slug);
    }
	
    public function postInstall($true, $hook_extra, $result)
    {
		global $wp_filesystem;
		
		$pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->slug);
		
		$wp_filesystem->move($result['destination'], $pluginFolder);
		
		$result['destination'] = $pluginFolder;
		
		if ($this->pluginActivated)
		{
		    $activate = activate_plugin($this->slug);
		}

        return $result;
    }
}