# Dynamic Event Redirect #

Version: 0.0.5
Author: Max Wheeler ([max@makenosound.com](max@makenosound.com))  
Build Date: 2009-04-01  
Requirements: Symphony 2.0.2 or later

## Description ##

Adds an event and an event filter that allows you to build up a combination of both URL and GET parameters from POST data to append to your form redirects.

## Usage ##
### Standlone Event ###

1. Attach the 'Dynamic Event Redirection' event to your desired page.
2. Add any combination of the options listed below:

### Event Filter ###
1. Attach the 'Dynamic Event Redirection' filter to your desired event.
2. Add any combination of the options listed below:

## Options ##
### URL Parameters ###

To use URL parameters in your redirect output, you need to add a hidden input field to your form with the name `der-url-params` and set its value as a `/` seperated list of parameters you wish to include. Like so:

		<input type="text" name="category" value="books-and-magazines"/>
		<input type="text" name="book-id" value="1234"/>
		<input type="hidden" name="der-url-params" value="category/book-id"/>
		<input type="hidden" name="redirect" value="http://amazon.com/"/>

The example above would result in the following URL: `http://amazon.com/books-and-magazines/1234/`. If a parameter isn't set in the POST data its key (i.e., `category` in the example above) will be used in its place.

### GET Parameters ###

You can use GET parameters with or without URL parameters. The usage is pretty much the same: add a hidden input field to your form that has the name `der-params` and set its value to a comma separated list of parameters you wish to include. Like so:

		<input type="text" name="category" value="books-and-magazines"/>
		<input type="text" name="book-id" value="1234"/>
		<input type="hidden" name="der-get-params" value="category,book-id"/>
		<input type="hidden" name="redirect" value="http://amazon.com/"/>

The would result in the user being redirected to: `http://amazon.com/?category=books-and-magazines&book-id=1234`.

## Things to note ##

* You'll need to specify a redirect URL, else the filter won't do anything.
* Entry fields have priority over normal POST data. That is, data from the `fields[]` array will be used in place of identically named indexes from the POST data.
* When using as a filter, you can pass on the ID of the entry you're creating by adding `id` to your list of params.
* You can also output values directly by using key:value pairs in the `der-params` value.
* Does not work with events with 'Allow multiple' filters, requires some changes to the core.
* If you're using Rowan's [Clean URL Params](http://overture21.com/forum/comments.php?DiscussionID=795) extension you can set the output to use clean syntax by adding the following to your form:
	
		<input name="der-format" type="1" />

## To do ##

* Make work with 'Allow multiple'