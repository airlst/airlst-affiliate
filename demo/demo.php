<?php

	require '../lib/class.airlst.php';
	
	$button = new AirLST\button();
	
	// Setup key and affiliate id
	$button->set_affiliate_id_and_key(1, 'abc');
	
	// Specifiy recipient of reservation request
	$button->set_host('marco@pizza.com', 'Marcos Pizza Ristorante');
	
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
	$button->set_meta_private('user_id=3423,email=guest@hotmail.com');
	
?>
<html>
	<head>
		<meta charset="utf-8">
		<?php echo $button->async_js_loader(); ?>
		<title>Restaurant Portal</title>
	</head>
	<body>
		<h1>Welcome to Restaurant Portal</h1>
		<p>If you want to make a reservation at Marcos Pizza, please click on the link below.</p>
		<p><?php echo $button->create_link(); ?></p>
	</body>
</html>	
