<?php

require_once dirname(__FILE__).'/TriggerCloseApi.php';

/**
 * @author Carl Helmertz <helmertz@gmail.com>
 */
class TriggerClosePlugin extends MantisPlugin {

	private $api;

	/**
	 * Stupid method that sometimes closes issues, to not execute a
	 * possibly heavy query on each page load.
	 *
	 * Crude and ugly alternative for a proper cronjob.
	 */
	function maybe_close_issues() {
		if(!plugin_config_get('maybe_close_active')) {
			return false;
		}
		if(mt_rand(1, 4) != 3) {
			return;
		}
		$this->api->auto_close();
	}

	/**
	 * @return array
	 */
	function config() {
		// config() is run prior to init(), thus: init stuff here
		$this->api = new TriggerCloseApi();
		return $this->api->config();
	}

	function hooks() {
		return array(
			// any event that gets called late, EVENT_PLUGIN_INIT
			// is too early and will make plugin_config_get() fail
			'EVENT_LAYOUT_PAGE_FOOTER' => 'maybe_close_issues'
		);
	}

	function register() {
		$this->name = 'TriggerClose';
		$this->description = 'Automatically closes issues based on terms such as "feedback has been the last status for two months and no changes has been made"';
		$this->version = 0.1;
		$this->requires = array(
			'MantisCore' => '2.0.0'
		);
		$this->page = 'config';

		$this->author = 'Francisco Mancardi';
		$this->contact = 'francisco.mancardi@gmail.com';
		$this->url = 'https://github.com/fmancardi/mantis-triggerclose';
	}
}
