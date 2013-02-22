<?php

	require '../lib/class.airlst.php';
	
	$button = new AirLST\button();
	
	// Setup key and affiliate id
	$button->set_affiliate_id_and_key(1, 'abc');
	
	// Specifiy recipient of reservation request
	$button->set_host('tom@nightclub.com', 'Munich Nightclub');
	
	// Set headline and welcome text of widget
	$button->set_texts('Reservations', 'Get on the guestlist of Munich Nightclub.');
	
	// Setup a guestlist for Saturday and allow reservations for 1 to 6 persons
	$button->set_rsvplist('Guestlist Saturday', 'DJ Leo Kane spinning the wheels for you', 1, 6);
	
	// Send meta data to host along with the reservation. host can see it, guest cannot see it.
	$button->set_meta_public('Guest has high reputation on portal');
	
	// Store meta data in the reservation which can later be retrieved via API call to AirLST (e.g. internal ids, states, ...)
	$button->set_meta_private('user_id=3423,email=guest@hotmail.com');
	
?>
<html>
	<head>
		<meta charset="utf-8">
		<?php echo $button->async_js_loader(); ?>
		<title>Nightlife Portal</title>
	</head>
	<body>
		<h1>Welcome to Nightlife Portal</h1>
		<p>If you want to make a reservation at Munich Nightclub, please click on the link below.</p>
		<p><?php echo $button->create_link('Get on the guestlist now'); ?></p>
	</body>
</html>	
