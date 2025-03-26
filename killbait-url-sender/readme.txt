=== KillBait URL Sender ===
Contributors: KillBait  
Tags: api, links, news, aggregator, linkbuilding  
Requires at least: 5.2
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://ko-fi.com/killbait

This plugin sends URLs of new posts to KillBait news aggregator for selected categories.

== Description ==  
Easily send URLs to Killbait news aggregator for selected categories and languages. 
This plugin integrates seamlessly with WordPress to automate the process.

== Installation ==  
1. Download and upload the plugin to /wp-content/plugins/.
2. Activate it in the admin panel.
3. Register at https://killbait.com/auth/login and get your API Key in your user profile.
4. Configure the selected categories, language and API Key in Settings > KillBait URL Sender.

== Changelog ==  
= 1.0 =  
* Initial version.  


== Frequently Asked Questions ==
1. What is KillBait URL Sender?
KillBait URL Sender is a WordPress plugin that allows you to send website URLs to Killbait for analysis, helping detect and prevent malicious activity.

2. How do I get my API Key?
You can obtain your API Key by registering at https://killbait.com/auth/login and accessing your user profile.

3. Does this plugin slow down my website?
No, the plugin operates in the background and does not affect your site's performance.

4. Is there a free version of Killbait?
Yes, Killbait offers free API access with certain limitations. Visit https://killbait.com/api/doc for more details.

5. What if I use Polylang plugin?
If you use the Polylang plugin, the post will be sent to the English or Spanish version of Killbait based on the language specified by Polylang. If the post is in any other language, it will be sent to the English version by default

== Upgrade Notice ==
= 1.0 =
* Initial version.  


== Screenshots ==

1. Plugin Settings Page – https://cdn.killbait.com/static/killbait-url-sender.png


== External services ==

This plugin connects to the KillBait API to send the URLs of the posts from your site that you want to be published on KillBait.  
Each time a post is sent to KillBait, the URL of your post, the language code ("en" or "es") in which you want your post to be shared on KillBait, and your API key for authentication with the KillBait API are sent.  
For more detailed information, refer to the [KillBait API documentation](https://killbait.com/api/doc).  
This service is offered under the following [terms of use](https://killbait.com/legal/legal-advise) and [privacy policy](https://killbait.com/legal/privacy-policy).


