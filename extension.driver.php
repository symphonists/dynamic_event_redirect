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
				'version'	=> '1.0.0',
				'author'	=> array('name' => 'Max Wheeler',
									'website' => 'http://makenosound.com/',
									'email' => 'max@makenosound.com'),
				'release-date' => '2010-06-08',
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
			$context['documentation'][] = new XMLElement('h4', 'URL Parameters');
			$context['documentation'][] = new XMLElement('p', 'To use URL parameters in your redirect output, you need to add a hidden input field to your form with the name <code>der-url-params</code> and set its value as a <code>/</code> seperated list of parameters you wish to include. Like so:');
			$code = '
<input type="text" name="category" value="books-and-magazines"/>
<input type="text" name="book-id" value="1234"/>
<input type="hidden" name="der-url-params" value="category/book-id"/>
<input type="hidden" name="redirect" value="http://amazon.com/"/>
			';
			$context['documentation'][] = contentBlueprintsEvents::processDocumentationCode($code);
			$context['documentation'][] = new XMLElement('p', 'The example above would result in the following URL: <code>http://amazon.com/books-and-magazines/1234/</code>. If a parameter isn&#8217;t set in the POST data its key (i.e., <code>category</code> in the example above) will be used in its place.');
			
			$context['documentation'][] = new XMLElement('h4', 'GET Parameters');
			$context['documentation'][] = new XMLElement('p', 'You can use GET parameters with or without URL parameters. The usage is pretty much the same: add a hidden input field to your form that has the name <code>der-params</code> and set its value to a comma separated list of parameters you wish to include. Like so:');

			$code = '
<input type="text" name="category" value="books-and-magazines"/>
<input type="text" name="book-id" value="1234"/>
<input type="hidden" name="der-get-params" value="category,book-id"/>
<input type="hidden" name="redirect" value="http://amazon.com/"/>
			';
			$context['documentation'][] = contentBlueprintsEvents::processDocumentationCode($code);
			$context['documentation'][] = new XMLElement('p', 'The would result in the user being redirected to: <code>http://amazon.com/?category=books-and-magazines&amp;book-id=1234</code>.');
			
			$context['documentation'][] = new XMLElement('h4', 'Things to note');
			$context['documentation'][] = new XMLElement('ul', '
			<li>You&#8217;ll need to specify a redirect URL, else the filter won&#8217;t do anything.</li>
			<li>Entry fields have priority over normal POST data. That is, data from the <code>fields[]</code> array will be used in place of identically named indexes from the POST data.</li>
			<li>When using as a filter, you can pass on the ID of the entry you&#8217;re creating by adding <code>id</code> to your list of params.</li>
			<li>You can also output values directly by using key:value pairs in the <code>der-params</code> value.</li>
			<li>Does not work with events with &#8216;Allow multiple&#8217; filters, requires some changes to the core.</li>
			<li><p>If you&#8217;re using Rowan&#8217;s <a href="http://overture21.com/forum/comments.php?DiscussionID=795">Clean URL Params</a> extension you can set the output to use clean syntax by adding the following to your form:</p>
			<pre><code>&lt;input name="der-format" type="1" /&gt;</code></pre></li>');
		}
				
		public function process_redirect($context)
		{
			# Check if in included filters
			if (in_array('dynamic-event-redirect', $context['event']->eParamFILTERS) && ! in_array('expect-multiple', $context['event']->eParamFILTERS) ) {
			
				$base_url = $_POST['redirect'];
				$redirect = '';
			
				if(array_key_exists('der-url-params', $_POST)) $this->_do_url_params($redirect, $context);
				if(array_key_exists('der-get-params', $_POST)) $this->_do_get_params($redirect, $context);
				if($base_url) redirect($base_url . $redirect);
			}
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