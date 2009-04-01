# Dynamic Event Redirect #

Version: 0.0.1
Author: Max Wheeler (max@makenosound.com)  
Build Date: 2009-04-01  
Requirements: Symphony 2.0.2 or later

## Description ##

Adds an event filter that allows you to add URL parameters from POST data to your form redirects.

## Usage ##

1. Upload and enable the extension.
2. Attach the 'Dynamic event redirection' filter to your desired event.
3. Add a hidden input field to your form that has the name `der-params` and its value as a comma separated list of parameters you wish to include. For example, so to pass the variable `email` on you'd do something like:

		<input type="text" name="fields[email]" value="user@domain.com"/>
		<input type="hidden" name="der-params" value="email"/>

5. Win!

## Things to note ##

* You'll need to specify a redirect URL, else the filter won't do anything.
* If you want to pass on the ID of the entry you're creating just add `id` to your list of params.
* You can also output values directly by using key:value pairs in the `der-params` value.
* Currently untested on events with 'Allow multiple' filters.
* If you're using Rowan's [Clean URL Params](http://overture21.com/forum/comments.php?DiscussionID=795) extension you can set the output to use clean syntax by adding the following to your form:
	
		<input name="der-format" type="1" /

## To do ##

* Make work with 'Allow multiple'