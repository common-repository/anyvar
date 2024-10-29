=== Plugin Name ===
Contributors: matt_dev
Donate link: http://www.devspace.com.au/anyvar/
Tags: insert, var, variable, variables, php, tag, tags, post, posts, page, pages, sidebar, widget, search, replace, text
Requires at least: 2.0.0
Tested up to: 2.5.1
Stable tag: 0.1.1

AnyVar is a simple search and replace plugin. It lets you add changeable variables (text snippets) to posts, sidebars, widgets, links & themes.

== Description ==

AnyVar is a simple text search and replace plugin. It lets you add changeable variables (text snippets) anywhere in your Wordpress blog.

AnyVar simplifies any mass changes required on your blog. Easily change affiliate codes, thoughts for the day, ads or anything else throughout your site with a single change in the admin.

== Installation ==

1. Download the plugin
2. Unzip & Upload the `anyvar` directory to the `/wp-content/plugins/` directory
3. Active AnyVar in the Plugins admin section
4. Go to Manage > AnyVar and start using it!

== Frequently Asked Questions ==

= How do I use AnyVar =

All you need to do is create a variable in the admin section, then add the `[variable_tag]` to your HTML code. This tag can go straight into your posts, pages, themes, links, widgets etc. 

= What's the use? I could just change it myself = 

The beauty (and power) of AnyVar comes into play when you have multiple copies of the same piece of code &/or text in more than one place. It's almost like a templating system within a templating system (Wordpress).

= Go on... =

Ok, consider these examples:

You like putting ads inside your posts, but you also like to change advertisers occasionally. So you create a variable called `[post_ad]` which contains the html for the ad. The next month you decide to change the ad, all you do is update the `[post_ad]` variable in the AnyVar admin section, and hey presto, every post you've put your `[post_ad]` tag (could be 5, 50, 100 posts) is automatically instantly change.

You've decide to add a 'thought for the day' quote to your blog. You could manually change the theme each day to reflect this, or your could add a `[daily_thought]` variable and update this each day inside the Wordpress admin, without having to touch the theme.

The usefulness of this plugin is only limited by your imagination... :)

= How does it work =

The non-technical version is that replaces the `[variable_tag]` with the variable text at the end of the process of making the page.

The technical version is that it uses PHP's output buffering to do the search / replace once Wordpress has finished generating that output.

= Doesn't output buffering effect the performance of a PHP script? =

Yes. However, in the relatively small amount of benchmarking I've done, AnyVar has actually slightly lowered the Wordpress page load time (by a few milliseconds). I'd say this is due to cutting down the number of echo / output commands sending data to the browser.

== Screenshots ==

1. AnyVar admin & examples