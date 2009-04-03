<?php
	Class extension_dynamic_event_redirect extends Extension
	{
		/*-------------------------------------------------------------------------
			Extension definition
		-------------------------------------------------------------------------*/
		public function about()
		{
			return array(
				'name' => 'Dynamic Event Redirect',
				'version'	=> '0.0.5',
				'author'	=> array('name' => 'Max Wheeler',
									'website' => 'http://makenosound.com/',
									'email' => 'max@makenosound.com'),
				'release-date' => '2009-04-01',
			);
		}
		
		/*-------------------------------------------------------------------------
			Delegate
		-------------------------------------------------------------------------*/
		public function getSubscribedDelegates()
		{
			return array(
				array(
					'page'		=> '/blueprints/events/edit/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'add_filter_to_event_editor'
				),				
				array(
					'page'		=> '/blueprints/events/new/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'add_filter_to_event_editor'
				),
				array(
				'page' => '/blueprints/events/new/',
					'delegate' => 'AppendEventFilterDocumentation',
					'callback' => 'add_filter_documentation_to_event'
				),					
				array(
				'page' => '/blueprints/events/edit/',
					'delegate' => 'AppendEventFilterDocumentation',
					'callback' => 'add_filter_documentation_to_event'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'EventPostSaveFilter',
					'callback'	=> 'process_redirect'
				),
			);
		}
		
		/*-------------------------------------------------------------------------
			Delegated functions
		-------------------------------------------------------------------------*/	
		
		public function add_filter_to_event_editor(&$context)
		{
			$context['options'][] = array('dynamic-event-redirect', @in_array('dynamic-event-redirect', $context['selected']) ,'Dynamic Event Redirection');
		}
		
		public function add_filter_documentation_to_event($context)
		{
			if ( ! in_array('dynamic-event-redirect', $context['selected'])) return;
			
			$context['documentation'][] = new XMLElement('h3', 'Dynamic Event Redirect');
			$context['documentation'][] = new XMLElement('p', 'To use, simply add a hidden input field to your form that has the name <code>der-params</code> and its value as a comma separated list of parameters you wish to include. For example, so to pass the variable <code>email</code> on you&#8217;d do something like:');
			$code = '<input name="fields[email]" type="text" />
<input type="hidden" name="der-get-params" value="email,foo:bar"/>';
			$context['documentation'][] = contentBlueprintsEvents::processDocumentationCode($code);
			$context['documentation'][] = new XMLElement('p', 'You can also output values directly by using key:value pairs.');
			$context['documentation'][] = new XMLElement('h3', 'Clean URL Params');
			$context['documentation'][] = new XMLElement('p', 'If you&#8217;re using Rowan&#8217;s <a href="http://overture21.com/forum/comments.php?DiscussionID=795">Clean URL Params</a> extension you can set the output to use clean syntax by adding the following to your form:');
			$code = '<input name="der-format" type="1" />';
			$context['documentation'][] = contentBlueprintsEvents::processDocumentationCode($code);
		}
				
		public function process_redirect($context)
		{
			# Check if in included filters
			if ( ! in_array('dynamic-event-redirect', $context['event']->eParamFILTERS)) return;
			if ( in_array('expect-multiple', $context['event']->eParamFILTERS)) return;
			
			$base_url = $_POST['redirect'];
			$redirect = '';
			
			if(array_key_exists('der-url-params', $_POST)) $this->_do_url_params($redirect, $context);
			if(array_key_exists('der-get-params', $_POST)) $this->_do_get_params($redirect, $context);
			if($base_url) redirect($base_url . $redirect);
		}
		
		/*-------------------------------------------------------------------------
			Actions
		-------------------------------------------------------------------------*/

		public function _do_url_params(&$redirect, $context=NULL)
		{
			$mapping = explode("/", $_POST['der-url-params']);
			$data = array();
			if(isset($mapping) && is_array($mapping))
			{
				foreach ($mapping as $key)
				{
					# Check if we're creating a new entry
					if(isset($context) && $key == 'id')
					{
						$data['id'] = $context['entry']->get('id');
					}
					# If there's a match in the fields[]
					else if(array_key_exists($key, $context['fields']))
					{
						$data[] = $context['fields'][$key];
					}
					# Otherwise check $_POST
					else if(array_key_exists($key, $_POST))
					{
						$data[] = $_POST[$key];
					}
					else {
						$data[] = $key;
					}
				}
				$encoded_data = "";
				foreach ($data as $val)
				{
					$encoded_data[] = urlencode($val);
				}
				$redirect .= implode("/", $encoded_data) . "/";
				return $redirect;
			}
		}
		
		public function _do_get_params(&$redirect, $context=NULL)
		{
			$mapping = explode(",", $_POST['der-get-params']);
			$data = array();
			if(isset($mapping) && is_array($mapping))
			{
				foreach ($mapping as $key)
				{
					# If there's a match, map the value of the match. Else output the value.
					if(isset($context) && $key == 'id')
					{
						$data['id'] = $context['entry']->get('id');
					}
					else if(array_key_exists($key, $context['fields']))
					{
						$data[$key] = $context['fields'][$key];
					}
					else if(array_key_exists($key, $_POST))
					{
						$data[$key] = $_POST[$key];
					}
					else
					{
						$pair = explode(":", $key);
						$data[$pair[0]] = $pair[1];
					}
				}
				$cleanparams = (array_key_exists('der-format', $_POST) && $_POST['der-format'] == 1) ? TRUE : FALSE;
				# Setup cleanurlparams ... or not
				$join = ($cleanparams) ? ":" : "=";
				$separator = ($cleanparams) ? "/" : "&";
				$start = ($cleanparams) ? "" : "?";				
				$encoded_data = "";
				foreach ($data as $key => $val)
				{
					$encoded_data[] = urlencode($key) . $join . urlencode($val);
				}
				$redirect .= $start . implode($separator, $encoded_data);
				return $redirect;
			}
		}
	}