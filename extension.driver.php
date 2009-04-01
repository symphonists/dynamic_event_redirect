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
				'version'	=> '0.0.4',
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
					'delegate'	=> 'EventFinalSaveFilter',
					'callback'	=> 'process_redirect'
				),
			);
		}
		
		/*-------------------------------------------------------------------------
			Custom functions
		-------------------------------------------------------------------------*/	
		
		public function add_filter_to_event_editor(&$context)
		{
			$context['options'][] = array('dynamic-event-redirect', @in_array('dynamic-event-redirect', $context['selected']) ,'Dynamic event redirection');
		}
		
		public function add_filter_documentation_to_event($context)
		{
			if ( ! in_array('dynamic-event-redirect', $context['selected'])) return;
			
			$context['documentation'][] = new XMLElement('h3', 'Dynamic Event Redirect');
			$context['documentation'][] = new XMLElement('p', 'To use, simply add a hidden input field to your form that has the name <code>der-params</code> and its value as a comma separated list of parameters you wish to include. For example, so to pass the variable <code>email</code> on you&#8217;d do something like:');
			$code = '<input name="fields[email]" type="text" />
<input type="hidden" name="der-params" value="email,foo:bar"/>';
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
		
			$mapping = explode(",", $_POST['der-params']);
			$redirect = $_POST['redirect'];
			$multiple = (in_array('expect-multiple', $context['event']->eParamFILTERS)) ? TRUE : FALSE;
			$cleanparams = (isset($_POST['der-format']) && $_POST['der-format'] == 1) ? TRUE : FALSE;
			$encoded_data = "";
			
			# Setup cleanurlparams ... or not
			$join = ($cleanparams) ? ":" : "=";
			$separator = ($cleanparams) ? "/" : "&";
			$start = ($cleanparams) ? "" : "?";
			$data = array();
						
			if(isset($mapping) && $redirect)
			{
				if($multiple)
				{
					$fields = $_POST['fields'];
					foreach ($fields as $field) {
						foreach ($field as $key => $value) {
							if(in_array($key, $mapping))
							{
# Need to figure out how to get ID
#								if($key == 'id') $value = $entry;
								$data[$key][] = $value;
							}
						}
					}
					# Do key:pair bits
					# Encode params
				}
				else
				{
					$fields = $context['fields'];
					foreach ($mapping as $key)
					{
						# If there's a match, map the value of the match. Else output the value.
						if(isset($fields[$key]))
						{
							$data[$key] = $fields[$key];
						}
						else if($key == 'id')
						{
							$data['id'] = $context['entry']->get('id');
						}
						else
						{
							$pair = explode(":", $key);
							$data[$pair[0]] = $pair[1];
						}
					}
					foreach ($data as $key => $val)
					{
						$encoded_data[] = urlencode($key) . $join . urlencode($val);
					}
				}
				redirect($redirect . $start . implode($separator, $encoded_data));
			}
			return;
		}
	}