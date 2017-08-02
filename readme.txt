=== WooCommerce Rejoiner ===
Contributors: madjax
Tags: woocommerce, rejoiner, abandoned cart, email marketing, remarketing, ecommerce, cart abandonment email
Requires at least: 4.6
Tested up to: 4.7.5
Stable tag: 1.4.5
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==

Drive faster revenue growth with innovative lifecycle email marketing software powered by email marketers who work as part of your team.

Rejoiner helps any WooCommerce customer:

* Maximize lifetime revenue per customer with highly personalized cart & browse abandonment, post-purchase, welcome series, win-back and replenishment email campaigns.
* Reduce your cart abandonment rate up to 15% and uncover hidden revenue with automated multistage email sequences at times you schedule.
* Design & develop high-converting, responsive email templates that match your brand’s look & feel.
* Proactively optimize campaigns with personalization, segmentation, A/B testing and more to consistently maximize revenue from every email campaign.

[Click Here To See A Video Demo of Rejoiner.](http://rejoiner.com/request-a-demo?utm_source=wordpress-plugin-directory&utm_medium=app-store&utm_campaign=woocommerce-listing#tour)

Rejoiner charges a flat monthly subscription, that includes access to our world-class email marketing team that creates, launches and optimizes your lifecycle email campaigns for you.

You also get unlimited email sends, no caps on list size or revenue, and no commission on sales. After requesting a demo and signing up, our team will integrate Rejoiner with your WooCommerce website so you can start capturing valuable data and and start making data-driven decisions.

[Click Here To See A Video Demo of Rejoiner.](http://rejoiner.com/request-a-demo?utm_source=wordpress-plugin-directory&utm_medium=app-store&utm_campaign=woocommerce-listing#tour)


== Installation ==

1. Request a demo at [Rejoiner.com](http://rejoiner.com/?utm_campaign=woocommerce-listing&utm_medium=app-store&utm_source=wordpress-plugin-directory).

2. Upload & activate the plug-in according to [these instructions](http://docs.rejoiner.com/article/50-woocommerce?utm_campaign=woocommerce-listing&utm_medium=app-store&utm_source=wordpress-plugin-directory). 


== Frequently Asked Questions ==

= What is a lifecycle email? =
Lifecycle emails are designed to trigger when your message will have maximum impact for customers. They use demographic, behavioral and purchase data to trigger campaigns that are more relevant and more engaging than standard “batch and blast” newsletter sends. Imagine receiving the perfect email, at the perfect moment, where you can’t resist acting on it. That’s a lifecycle email. Here are some examples:

* Cart Abandonment: Send an email when a customer abandons an eCommerce shopping cart on your site. Hint: You can get them to convert 10-15% of the time!
* Welcome Series: Send a series of emails when a customer makes their first purchase to demonstrate why they should purchase again.
* Win Back: Send an email when an existing customer hasn’t been back to your site to purchase in a while.
* Replenishment: Send an email at the exact moment a customer runs out of a consumable product. 

= How long does integration take? =
The Rejoiner WooCommerce add-on can be installed, configured and tested in 10 minutes or less. However, you’ll need to speak with a member of the Rejoiner team before starting the integration process. Head over to Rejoiner.com and Request a Demo to get started.

= What makes Rejoiner different from other email marketing software? =
There are two key differences between Rejoiner and other email software:

1. Rejoiner is built specifically for the needs of online retailers and eCommerce companies. Conventional “list-based” email software does not have the triggering or segmentation capabilities necessary to do this kind of automation.
2. Rejoiner is more than just software. As a Rejoiner client, you work with a team of eCommerce email marketing experts who act as an extension of your internal marketing team. It’s a “done-with-you” solution where the team collaborates with you on everything from campaign strategy to responsive template development.

= Does Rejoiner provide email design services? =
Yes. Rejoiner provides campaign strategy, custom email design, as well as responsive template development services.

= Which lifecycle emails campaigns should I start with? =
The lifecycle email campaign with the highest ROI targets customers who abandon eCommerce shopping carts. Next, we recommend building a campaign for existing customers who may have purchased in the past but haven’t been back to your site in a while. This is called a win back campaign.

= How are my emails delivered? =
Your email campaigns are delivered through Rejoiner’s rock-solid, authenticated sending infrastructure that delivers millions of emails per month. You don’t have to worry about managing additional cron jobs in Wordpress or the deliverability issues that come with using PHP sendmail. 

= How do I get started? =
Head over to [Rejoiner.com](http://rejoiner.com/?utm_campaign=woocommerce-listing&utm_medium=app-store&utm_source=wordpress-plugin-directory) and [request a demo](http://rejoiner.com/request-a-demo?utm_campaign=woocommerce-listing&utm_medium=app-store&utm_source=wordpress-plugin-directory). A member of our team will be get back to you within the hour. 


== Screenshots ==
1. Dashboard
2. Email Campaigns
3. Email Analytics
4. Campaign Demo
5. Implementation
6. Campaign Results
7. Segmentation
8. Active Test
9. Test Completed
10. A/B Testing
11. Account Monitor


== Changelog ==
= 1.4.5 =
* Use native WC session for unique ID. Props @adamchal

= 1.4.4 =
* Fix escaping attribute_value for JSON

= 1.4.3 =
* Add support for variant images

= 1.4.2 =
* Add filters for passing attribute data to setCartItem

= 1.4.1 =
* Prevent encoding of double quotes in product title for trackProductView

= 1.4 =
* Integrate new Rejoiner API

= 1.3.5 =
* Add product url and category to setCartItem

= 1.3.4 =
* Preserve custom GA utm parameters

= 1.3.3 =
* Add screenshots
* Update readme.txt

= 1.3.2 =
* Move REST API call to woocommerce_payment_complete action

= 1.3.1 =
* Bugfix: prevent empty email parameter for non-logged in users

= 1.3 =
* Integrate Rejoiner REST API for conversion tracking redundancy - visit Settings > Integration and add your API key and secret to take advantage of this new feature.

= 1.2.6 =
* Move refill cart function hook to wp_loaded

= 1.2.5 =
* Add new filters: wc_rejoiner_cart_item_name, wc_rejoiner_cart_item_variant, wc_rejoiner_thumb_size - see included sample-functions.php file
* When user is logged in, set 'email' parameter as part of the setCartData call on cart and checkout, with the customer's email address

= 1.2.4 =
* Undeclared variable bug fix

= 1.2.3 =
* Product name escaping bug fix

= 1.2.2 =
* Remove description from Rejoiner JS
* Better number formatting
* Prevent display of tracking code on thank you page

= 1.2.1 =
* Display tracking only on cart and checkout

= 1.2 =
* Validate image URLs
* Use excerpt for description
* Better description sanitization

= 1.1 =
* Initial public release