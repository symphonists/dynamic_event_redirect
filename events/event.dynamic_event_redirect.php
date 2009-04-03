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
		
		
		public static function documentation()
		{
		  $docs = array();
			$docs[] = '
<p>Adds an event and an event filter that allows you to build up a combination of both URL and GET parameters from POST data to append to your form redirects.</p>

<h2 id="usage">Usage</h2>

<h3 id="standloneevent">Standlone Event</h3>

<ol>
<li>Attach the &#8216;Dynamic Event Redirection&#8217; event to your desired page.</li>
<li>Add any combination of the options listed below:</li>
</ol>

<h3 id="eventfilter">Event Filter</h3>

<ol>
<li>Attach the &#8216;Dynamic Event Redirection&#8217; filter to your desired event.</li>
<li>Add any combination of the options listed below:</li>
</ol>

<h2 id="options">Options</h2>

<h3 id="urlparameters">URL Parameters</h3>

<p>To use URL parameters in your redirect output, you need to add a hidden input field to your form with the name <code>der-url-params</code> and set its value as a <code>/</code> seperated list of parameters you wish to include. Like so:</p>

<pre><code>&lt;input type="text" name="category" value="books-and-magazines"/&gt;
&lt;input type="text" name="book-id" value="1234"/&gt;
&lt;input type="hidden" name="der-url-params" value="category/book-id"/&gt;
&lt;input type="hidden" name="redirect" value="http://amazon.com/"/&gt;</code></pre>

<p>The example above would result in the following URL: <code>http://amazon.com/books-and-magazines/1234/</code>. If a parameter isn&#8217;t set in the POST data its key (i.e., <code>category</code> in the example above) will be used in its place.</p>

<h3 id="getparameters">GET Parameters</h3>

<p>You can use GET parameters with or without URL parameters. The usage is pretty much the same: add a hidden input field to your form that has the name <code>der-params</code> and set its value to a comma separated list of parameters you wish to include. Like so:</p>

<pre><code>&lt;input type="text" name="category" value="books-and-magazines"/&gt;
&lt;input type="text" name="book-id" value="1234"/&gt;
&lt;input type="hidden" name="der-get-params" value="category,book-id"/&gt;
&lt;input type="hidden" name="redirect" value="http://amazon.com/"/&gt;</code></pre>

<p>The would result in the user being redirected to: <code>http://amazon.com/?category=books-and-magazines&amp;book-id=1234</code>.</p>

<h2 id="thingstonote">Things to note</h2>

<ul>
<li>You&#8217;ll need to specify a redirect URL, else the filter won&#8217;t do anything.</li>
<li>Entry fields have priority over normal POST data. That is, data from the <code>fields[]</code> array will be used in place of identically named indexes from the POST data.</li>
<li>When using as a filter, you can pass on the ID of the entry you&#8217;re creating by adding <code>id</code> to your list of params.</li>
<li>You can also output values directly by using key:value pairs in the <code>der-params</code> value.</li>
<li>Does not work with events with &#8216;Allow multiple&#8217; filters, requires some changes to the core.</li>
<li><p>If you&#8217;re using Rowan&#8217;s <a href="http://overture21.com/forum/comments.php?DiscussionID=795">Clean URL Params</a> extension you can set the output to use clean syntax by adding the following to your form:</p>

<pre><code>&lt;input name="der-format" type="1" /&gt;</code></pre></li>
</ul>
';
			return implode("\n", $docs);
		}
	}