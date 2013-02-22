#AirLST Affiliate Deep Integration Programme (A2DIP)

## tldr;
A2DIP enables portals, content aggregators and affiliate websites to dispatch reservations or bookings to local stores (e.g. restaurants, bars, clubs, venues, etc). AirLST takes care of the full process and transparently handles all communication with hosts and guests. Depending on the requirements of the affiliate partner, the system can be setup so that neither hosts nor guests can see that AirLST handles the communication.

## Short example
Affiliate partner AP runs a website where people can rate the best restaurants in town. AP wants to offer his website visitors the possibility to make reservations in the restaurants he recommends (eventually earning some referral fee for bringing good guests). AP rightfully decides that developing a reservation engine for himself is a tedious task, and integrates AirLST A2DIP to his website in a few minutes instead.

AP adds a few lines of PHP code to his website, which add a customizable reservation button to each of his restaurant review pages.

Here's what happens when website visitor Bob clicks on the reservation button which is displayed on the page of restaurant host Alice:

1. An awesome looking overlay will be displayed, where the Bob may select date, time and amount of people he wants to make a reservation for.

2. Bob enters his name, email and phone number and submits the reservation request.

3. AirLST will contact Alice (the host of the restaurant) on behalf of AP, asking if she wants to accept Bob's reservation request.

4. If Alice accepts the reservation, Bob will be notified by email that his request was accepted. If Alice is booked out on this evening, Bob will of course also be notified.

5. (Optionally) AirLST can send a follow up email to Bob after his visit at Alice's restaurant, asking him to rate his stay or become fan of Alice's facebook page. Alice will be happy to receive feedback from Bob, increasing her ratings and social media presence.

5. (Optionally) Website owner AP can query the AirLST system to know how many reservations he has send to Alice. If he is sending lots of reservations, he might negotiate a small kickback with Alice.

6. (Optionally) As Bob is a frequent visitor of AP's restaurant website, AP knows a lot about Bob, for example if he is an "influencer" in the community. AP can choose to automatically send this hidden information to Alice with the reservation request, letting her know that she should take special care about Bob. AirLST will handle this communication easily.

## Basic integration of widget

Download or clonce repository from GitHub:

	http://github.com/airlst/airlst-affiliate

Load the AirLST library from your PHP code:

	<?php require_once('class.airlst.php') ?>

Load the AirLST Javascript Bridge asynchronously by including the following line in the ``<head>`` part of your page:

	<?php echo AirLST\button::js_async_loader(); ?>
	
To create a special reservation link, create an instance of ``AirLST\button`` and set the parameters as required:

	<?php 
	
	$button = new AirLST\button();
	
	// Setup key and affiliate id (get from AirLST team)
	$button->set_affiliate_id_and_key(1, 'abc');
	
	// Specifiy recipient of reservation request
	$button->set_host('marco@airlst.com', 'Pizzeria');
	
	// Set headline and welcome text of widget
	$button->set_texts('Reservation', 'Welcome to Marco Pizza');
	
	// Add opening hours: Mon-Sun from 12.00h to 15.00h with 15-minute slots
	$button->add_shift_everyday(12, 15, 15);
	
	// Add opening hours: Mon-Sun from 18.00h to 23.00h with 30-minute slots
	$button->add_shift_everyday(18, 23, 30);
	
	// Allow reservations from 2 to 6 persons
	$button->set_pax_allowed(2, 6);
	
	// Send meta data to host along with the reservation. host can see it, guest cannot see it.
	$button->set_meta_public('Guest has high reputation on portal');
	
	// Store meta data in the reservation which can later be retrieved via API call to AirLST (e.g. internal ids, states, ...)
	$button->set_meta_private('user_id=3423,email=bob@gmail.com');
	
	?>
	
Finally, place a link somehwhere in your code:

	<?php echo $button->create_link('Make a reservation'); ?>
	
Please see ``demo/demo.php`` in this repository for a working sample implementation.
	
## Getting data and statistics back from AirLST

To obtain statistics about the amount and status of the reservations you have sent through AirLST, use our easy to use backend library. We currently provide three endpoints:

### /requests_overall
---
##### Description
This call will return a complete list of hosts with the sum of reservations made. It will also contain information about how many reservations really showed up (if the host is using AirLST App for checking people in)
##### Usage
	$api = new AirLST\api();
	
	// Setup key and affiliate id (get from AirLST team)
	$api->set_affiliate_id_and_key(1, 'abc');
	
	// Retrieve info about all reservations
	$res = $api->requests_overall();

##### Returns
PHP array, displayed in json notation here for readability

	[
		{
			"company_id"	: 1,
			"email"			: "marco@pizza.com",
			"requests"		: {
				"total": 23,
				"checked_in": 20
			}
		},
		{
			"company_id"	: 2,
			"email"			: "eduardo@tappas.com",
			"requests"		: {
				"total": 15,
				"checked_in": 14
			}
		},
		...
	]



### /requests_host
---
##### Description
This call will return a detailed list of reservations for a given host. The response will also contain information that was transparently sent through ``meta_public`` and ``meta_private`` attributes.

##### Usage
	$api = new AirLST\api();
	
	// Setup key and affiliate id (get from AirLST team)
	$api->set_affiliate_id_and_key(1, 'abc');
	
	// Retrieve info about all reservations
	$res = $api->requests_host("marco@pizza.com");

##### Returns
PHP array, displayed in json notation here for readability

	[
		{
			"request_id"	: 83882773,
			"email"			: "bob@gmail.com",
			"phone"			: "+1-222-333-222",
			"date_rsvp"		: "2013-05-18 20:00:00",
			"msg_request"	: "I want to sit at the window",
			"meta_public"	: "Guest has high reputation on portal",
			"meta_private"	: "user_id=3423,email=bob@gmail.com",
			"checked_in"	: "y"
		},
		...
	]



### /requests_guest
---
##### Description
This call will return a detailed list of reservations for a given guest. The response will also contain information that was transparently sent through ``meta_public`` and ``meta_private`` attributes.

##### Usage
	$api = new AirLST\api();
	
	// Setup key and affiliate id (get from AirLST team)
	$api->set_affiliate_id_and_key(1, 'abc');
	
	// Retrieve info about all reservations
	$res = $api->requests_guest("bob@gmail.com");

##### Returns
PHP array, displayed in json notation here for readability

	[
		{
			"request_id"	: 83882773,
			"email"			: "bob@gmail.com",
			"phone"			: "+1-222-333-222",
			"date_rsvp"		: "2013-05-18 20:00:00",
			"msg_request"	: "I want to sit at the window",
			"meta_public"	: "Guest has high reputation on portal",
			"meta_private"	: "user_id=3423,email=bob@gmail.com",
			"checked_in"	: "y"
		},
		...
	]



## Requirements
* PHP 5.3+
* mcrypt

## Copyright

2012 - 2013 AirLST.com by LINKS DER ISAR GmbH.

All rights reserved. AirLST is a registered trademark by LINKS DER ISAR GmbH.