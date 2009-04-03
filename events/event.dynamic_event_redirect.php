<?php
	
	Class eventDynamic_event_redirect extends Event
	{	
		public static function about()
		{
			return array(
						 'name' => 'Dynamic Event Redirection',
						 'author' => array('name' => 'Max Wheeler',
										   'website' => 'http://makenosound.com/',
										   'email' => 'max@makenosound.com'),
						 'version' => '0.0.5',
						 'release-date' => '2009-04-02',
					);
		}
				
		public function __construct(&$parent)
		{
			parent::__construct($parent);
			$this->_driver = $this->_Parent->ExtensionManager->create('dynamic_event_redirect');
		}
		
		public function load()
		{
			if(array_key_exists('der-get-params', $_POST) OR array_key_exists('der-url-params', $_POST)) return $this->__trigger();
			return NULL;
		}
		
		protected function __trigger()
		{
			$base_url = $_POST['redirect'];
			$redirect = '';
			
			if(array_key_exists('der-url-params', $_POST)) $this->_driver->_do_url_params($redirect);
			if(array_key_exists('der-get-params', $_POST)) $this->_driver->_do_get_params($redirect);
			if($base_url) redirect($base_url . $redirect);
		}		
	}