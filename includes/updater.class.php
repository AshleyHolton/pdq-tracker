<?php

class Updater
{
	private $slug;
	private $pluginData;
	
	private $username = 'AshleyHolton';
	private $repo = 'pdq-tracker';
	
	private $pluginFile;
	private $githubAPIResult;

	function __construct($pluginFile)
	{
		add_filter("pre_set_site_transient_update_plugins", array($this, "setTransitent"));
		add_filter("plugins_api", array($this, "setPluginInfo"), 10, 3);
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
		if(!empty($this->githubAPIResult))
		{
			return;
		}

		$url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases";
		
		$this->githubAPIResult = wp_remote_retrieve_body(wp_remote_get($url));
		if(!empty($this->githubAPIResult))
		{
			$this->githubAPIResult = @json_decode($this->githubAPIResult);
		}
		
		if(is_array($this->githubAPIResult)){
			$this->githubAPIResult = $this->githubAPIResult[0];
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
		
		$doUpdate = version_compare($this->githubAPIResult->tag_name, $transient->checked[$this->slug]);
		
		//wp_die(var_dump($this->githubAPIResult));
		
		if($doUpdate == 1)
		{
			$package = $this->githubAPIResult->zipball_url;

			$obj = new stdClass();
			$obj->slug = $this->slug;
			$obj->new_version = $this->githubAPIResult->tag_name;
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
		
		if(empty($response->slug)|| $response->slug != $this->slug)
		{
			return false;
		}
		
		$response->last_updated = $this->githubAPIResult->published_at;
		$response->slug = $this->slug;
		$response->name  = $this->pluginData["Name"];
		$response->version = $this->githubAPIResult->tag_name;
		$response->author = $this->pluginData["AuthorName"];
		$response->homepage = $this->pluginData["PluginURI"];
		
		$downloadLink = $this->githubAPIResult->zipball_url;
		
		$response->download_link = $downloadLink;

		require_once(plugin_dir_path(__FILE__). "parsedown.class.php");

		$response->sections = array(
			'description' => $this->pluginData["Description"],
			'changelog' => class_exists("Parsedown")
				? Parsedown::instance()->parse($this->githubAPIResult->body)
				: $this->githubAPIResult->body
		);
		
		$matches = null;
		preg_match("/requires:\s([\d\.]+)/i", $this->githubAPIResult->body, $matches);
		if(!empty($matches))
		{
			if(is_array($matches))
			{
				if(count($matches)> 1)
				{
					$response->requires = $matches[1];
				}
			}
		}
		
		$matches = null;
		preg_match("/tested:\s([\d\.]+)/i", $this->githubAPIResult->body, $matches);
		if(!empty($matches))
		{
			if(is_array($matches))
			{
				if(count($matches)> 1)
				{
					$response->tested = $matches[1];
				}
			}
		}

		return $response;
	}
	
	public function postInstall($true, $hook_extra, $result)
	{
		$this->initPluginData();
		
		$wasActivated = is_plugin_active($this->slug);
		
		global $wp_filesystem;
		$pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->slug);
		$wp_filesystem->move($result['destination'], $pluginFolder);
		$result['destination'] = $pluginFolder;

		if($wasActivated)
		{
			$activate = activate_plugin($this->slug);
		}

		return $result;
	}
}