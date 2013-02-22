<?php

namespace AirLST;

class button {
	
	public $key 				= '';	// key for symmetric encryption of payload, contact AirLST team to get one
	public $affiliate_id 		= '';	// identifier for affiliate partner, contact AirLST team to get one
		
	public $host_email 			= '';	// email of host or restaurant owner
	public $host_firma			= '';	// company name of host or restaurant owner
	public $host_vorname		= '';	// first name of host or restaurant owner
	public $host_nachname		= '';	// last name of host or restaurant owner
	public $host_local_widget 	= 'n';	// allow host or restaurant owner to configure widget manually thorugh AirLST backend (experimental)
	
	public $widget_title		= '';	// headline of widget frame
	public $widget_desc			= '';	// welcome text on widget start screen
		
	public $pax_min				= 1;	// minimum number of people	
	public $pax_max				= 6;	// maximum number of people
		
	public $meta_public			= '';	// meta information sent to the host (guest cannot see it)
	public $meta_private		= '';	// meta information stored with reservation, only accessible to affiliate partner via API calls (guest and host cannot see it)
	
	public $sandbox				= FALSE;	// enable sandbox mode for local testing on AirLST platform (internal use only)
	
	private $mode 				= 'date';	// use widget for date ("date") or guestlists ("rsvplist")
	
	private $settings = array(
			
		'slots'				=> array('', '', '', '', '', '', ''),
		
		'rsvplist_name'		=> '',
		'rsvplist_desc'		=> '',
		'password_required'	=> 'never',
		'password'			=> '',
		'pax_available'		=> NULL,
	);
	
	function __construct() {
	}
	
	public function set_affiliate_id_and_key($affiliate_id, $key){
		$this->affiliate_id = $affiliate_id;
		$this->key = $key;
	}
	
	public function set_host($email, $firma='', $vorname='', $nachname=''){
		$this->host_email = $email;
		$this->host_firma = $firma;
		$this->host_vorname = $vorname;
		$this->host_nachname = $nachname;
	}
	
	public function set_texts($widget_title='Reservierung', $widget_desc=''){
		$this->widget_title = $widget_title;
		$this->widget_desc = $widget_desc;
	}
	
	private function set_mode($mode){
		$this->mode = ($mode == 'rsvplist') ? 'rsvplist' : 'date';
	}
	
	public function use_rsvplist_mode(){
		$this->set_mode('rsvplist');
	}
	
	public function use_date_mode(){
		$this->set_mode('date');
	}
	
	public function set_meta_private($meta_private=''){
		$this->meta_private = $meta_private;
	}
	
	public function set_meta_public($meta_public=''){
		$this->meta_public = $meta_public;
	}
	
	public function set_rsvplist($rsvplist_name='Gästeliste', $rsvplist_desc='', $pax_min=1, $pax_max=6, $password_required='never', $password=NULL, $pax_available=NULL){
		
		$this->set_mode('rsvplist');
		
		$this->settings['rsvplist_name'] 		= $rsvplist_name;
		$this->settings['rsvplist_desc'] 		= $rsvplist_desc;
		$this->settings['password_required'] 	= $password_required;
		$this->settings['password'] 			= $password;
		$this->settings['pax_available'] 		= $pax_available;
	}
	
	public function set_dates($slots, $pax_min, $pax_max){
		
		$this->set_mode('date');
		
		if(is_array($slots))
			$this->settings['slots'] = $slots;
			
		$this->set_pax_allowed($pax_min, $pax_max);
	}
	
	public function set_pax_allowed($pax_min, $pax_max){
		$this->pax_min = $pax_min;
		$this->pax_max = $pax_max;
	}
	
	public function add_shift($day, $hour_start, $hour_end, $minute_interval = 30){
		
		$this->set_mode('date');
		
		$day 				= intval($day);
		$hour_start 		= intval($hour_start);
		$hour_end 			= intval($hour_end);
		$minute_interval 	= intval($minute_interval);
		
		if($day < 0 || $day > 6)
			return FALSE;
		
		if($hour_start < 0 || $hour_start > 23 || $hour_end < 0 || $hour_end > 23 || $hour_start > $hour_end)
			return FALSE;
		
		if($minute_interval != 0 && $minute_interval != 15 && $minute_interval != 30)
			return FALSE;
		
		
		$addons = array();
		
		for($hour=$hour_start; $hour<$hour_end; $hour++){
			
			$addons[] = ($hour<10?'0':'').$hour.'0000';
			
			if($minute_interval == 15)
				$addons[] =  ($hour<10?'0':'').$hour.'1500';
				
			if($minute_interval == 15 || $minute_interval == 30)
				$addons[] =  ($hour<10?'0':'').$hour.'3000';
				
			if($minute_interval == 15)
				$addons[] =  ($hour<10?'0':'').$hour.'4500';
		}
		
		$this->settings['slots'][$day] = trim($this->settings['slots'][$day].' '.(implode(' ', $addons)));
		
		return TRUE;
	}
	
	public function add_shift_everyday($hour_start, $hour_end, $minute_interval=30){
		
		for($day=0; $day<7; $day++)
			$this->add_shift($day, $hour_start, $hour_end, $minute_interval);
	}
	
	public function populate_testdata(){
		
		// Setup key and affiliate id
		$this->set_affiliate_id_and_key(1, 'abc');
		
		// Specifiy recipient of reservation request
		$this->set_host('marco@airlst.com', 'Pizzeria');
		
		// Set headline and welcome text of widget
		$this->set_texts('Reservation', 'Welcome to Marco Pizza');
		
		// Add opening hours: Mon-Sun from 12.00h to 15.00h with 15-minute slots
		$this->add_shift_everyday(12, 15, 15);
		
		// Add opening hours: Mon-Sun from 18.00h to 23.00h with 30-minute slots
		$this->add_shift_everyday(18, 23, 30);
		
		// Allow reservations from 2 to 6 persons
		$this->set_pax_allowed(2, 6);
		
		// Send meta data to host along with the reservation. host can see it, guest cannot see it.
		$this->set_meta_public('Guest has high reputation on portal');
		
		// Store meta data in the reservation which can later be retrieved via API call to AirLST (e.g. internal ids, states, ...)
		$this->set_meta_private('user_id=3423,email=guest@hotmail.com');
	}
	
	protected function create_payload(){
		
		if($this->mode == 'date'){
			
			$res = array(
				'host'	=> array(
					'email'			=> $this->host_email,
					'firma'			=> $this->host_firma,
					'vorname'		=> $this->host_vorname,
					'nachname'		=> $this->host_nachname,
					'local_widget'	=> $this->host_local_widget,
				),
				'mode'			=> 'date',
				'widget_title'	=> $this->widget_title,
				'widget_desc'	=> $this->widget_desc,
				'pax_min'		=> $this->pax_min,
				'pax_max'		=> $this->pax_max,
				'meta_public'	=> $this->meta_public,
				'meta_private'	=> $this->meta_private,
			);
			
			for($i=0; $i<7; $i++)
				$res['slot'.($i+1)] = $this->settings['slots'][$i].'';
				
		}
		elseif($this->mode == 'rsvplist'){
			
			$res = array(
				'host'	=> array(
					'email'			=> $this->host_email,
					'firma'			=> $this->host_firma,
					'vorname'		=> $this->host_vorname,
					'nachname'		=> $this->host_nachname,
					'local_widget'	=> $this->host_local_widget,
				),
				'mode'			=> 'rsvplist',
				'widget_title'	=> $this->widget_title,
				'widget_desc'	=> $this->widget_desc,
				'meta_public'	=> $this->meta_public,
				'meta_private'	=> $this->meta_private,
				'rsvplists'		=> array(
					array(
						'pax_min'			=> $this->pax_min,
						'pax_max'			=> $this->pax_max,
						'name'				=> $this->settings['rsvplist_name'],
						'desc'				=> $this->settings['rsvplist_desc'],
						'password_required'	=> $this->settings['password_required'],
						'password'			=> $this->settings['password'],
						'pax_available'		=> $this->settings['pax_available'],
					)
				)
			);
			
		}
		else{
			return FALSE;
		}
		
		return urlencode(base64_encode(encryption::encrypt_data(json_encode($res), $this->key)));
	}
	
	public function create_link_attributes(){
		
		$res = array(
			'data-affiliate-id'			=> $this->affiliate_id,
			'data-affiliate-payload'	=> $this->create_payload(),
			'class'						=> 'airlst-button',
		);
		
		if($this->sandbox)
			$res['data-sandbox'] = 1;
			
		return $res;
	}
	
	public function create_link($title='Jetzt reservieren'){
		
		$attributes = $this->create_link_attributes();
		
		$s  = '<a href="#" ';
		
		foreach($attributes as $key => $value)
			$s .= $key.'="'.$value.'" ';
			
		$s .= '>'.$title.'</a>';
		
		return $s;
	}
	
	public static function async_js_loader(){
		
		$s = "	<script type=\"text/javascript\">
					(function() {
						var al = document.createElement('script'); al.type = 'text/javascript'; al.async = true;
						al.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'www.airlst.com/widget/v2/airlst-button.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(al, s);
					})();
				</script>
		";
				
		return $s;
	}
}

/**
 * A class to handle secure encryption and decryption of arbitrary data
 * URL: http://stackoverflow.com/questions/5089841/php-2-way-encryption-i-need-to-store-passwords-that-can-be-retrieved
 *
 * Note that this is not just straight encryption.  It also has a few other
 *  features in it to make the encrypted data far more secure.  Note that any
 *  other implementations used to decrypt data will have to do the same exact
 *  operations.  
 *
 * Security Benefits:
 *
 * - Uses Key stretching
 * - Hides the Initialization Vector
 * - Does HMAC verification of source data
 *
 */
class encryption {

    /**
     * @var string $cipher The mcrypt cipher to use for this instance
     */
    protected $cipher = '';

    /**
     * @var int $mode The mcrypt cipher mode to use
     */
    protected $mode = '';

    /**
     * @var int $rounds The number of rounds to feed into PBKDF2 for key generation
     */
    protected $rounds = 100;

    /**
     * Constructor!
     *
     * @param string $cipher The MCRYPT_* cypher to use for this instance
     * @param int    $mode   The MCRYPT_MODE_* mode to use for this instance
     * @param int    $rounds The number of PBKDF2 rounds to do on the key
     */
    public function __construct($cipher=MCRYPT_BlOWFISH, $mode=MCRYPT_MODE_CBC, $rounds = 100) {
        $this->cipher = $cipher;
        $this->mode = $mode;
        $this->rounds = (int) $rounds;
    }
	
	/*
	 *	Static: convenience method to encrypt data using a key
	 *	Params: data, key
	 */
	static function encrypt_data($data, $key){
		$e = new encryption();
		return $e->encrypt($data, $key);
	}
	
	/*
	 *	Static: convenience method to decrypt data using a key
	 *	Params: data, key
	 */
	static function decrypt_data($data, $key){
		$e = new encryption();
		return $e->decrypt($data, $key);
	}

    /**
     * Decrypt the data with the provided key
     *
     * @param string $data The encrypted datat to decrypt
     * @param string $key  The key to use for decryption
     * 
     * @returns string|false The returned string if decryption is successful
     *                           false if it is not
     */
    public function decrypt($data, $key) {
        $salt = substr($data, 0, 128);
        $enc = substr($data, 128, -64);
        $mac = substr($data, -64);

        list ($cipherKey, $macKey, $iv) = $this->getKeys($salt, $key);

        if ($mac !== hash_hmac('sha512', $enc, $macKey, true)) {
             return false;
        }

        $dec = mcrypt_decrypt($this->cipher, $cipherKey, $enc, $this->mode, $iv);

        $data = $this->unpad($dec);

        return $data;
    }

    /**
     * Encrypt the supplied data using the supplied key
     * 
     * @param string $data The data to encrypt
     * @param string $key  The key to encrypt with
     *
     * @returns string The encrypted data
     */
    public function encrypt($data, $key) {
        $salt = mcrypt_create_iv(128, MCRYPT_DEV_URANDOM);
        list ($cipherKey, $macKey, $iv) = $this->getKeys($salt, $key);

        $data = $this->pad($data);

        $enc = mcrypt_encrypt($this->cipher, $cipherKey, $data, $this->mode, $iv);

        $mac = hash_hmac('sha512', $enc, $macKey, true);
        return $salt . $enc . $mac;
    }

    /**
     * Generates a set of keys given a random salt and a master key
     *
     * @param string $salt A random string to change the keys each encryption
     * @param string $key  The supplied key to encrypt with
     *
     * @returns array An array of keys (a cipher key, a mac key, and a IV)
     */
    protected function getKeys($salt, $key) {
        $ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
        $keySize = mcrypt_get_key_size($this->cipher, $this->mode);
        $length = 2 * $keySize + $ivSize;

        $key = $this->pbkdf2('sha512', $key, $salt, $this->rounds, $length);

        $cipherKey = substr($key, 0, $keySize);
        $macKey = substr($key, $keySize, $keySize);
        $iv = substr($key, 2 * $keySize);
        return array($cipherKey, $macKey, $iv);
    }

    /**
     * Stretch the key using the PBKDF2 algorithm
     *
     * @see http://en.wikipedia.org/wiki/PBKDF2
     *
     * @param string $algo   The algorithm to use
     * @param string $key    The key to stretch
     * @param string $salt   A random salt
     * @param int    $rounds The number of rounds to derive
     * @param int    $length The length of the output key
     *
     * @returns string The derived key.
     */
    protected function pbkdf2($algo, $key, $salt, $rounds, $length) {
        $size   = strlen(hash($algo, '', true));
        $len    = ceil($length / $size);
        $result = '';
        for ($i = 1; $i <= $len; $i++) {
            $tmp = hash_hmac($algo, $salt . pack('N', $i), $key, true);
            $res = $tmp;
            for ($j = 1; $j < $rounds; $j++) {
                 $tmp  = hash_hmac($algo, $tmp, $key, true);
                 $res ^= $tmp;
            }
            $result .= $res;
        }
        return substr($result, 0, $length);
    }

    protected function pad($data) {
        $length = mcrypt_get_block_size($this->cipher, $this->mode);
        $padAmount = $length - strlen($data) % $length;
        if ($padAmount == 0) {
            $padAmount = $length;
        }
        return $data . str_repeat(chr($padAmount), $padAmount);
    }

    protected function unpad($data) {
        $length = mcrypt_get_block_size($this->cipher, $this->mode);
        $last = ord($data[strlen($data) - 1]);
        if ($last > $length) return false;
        if (substr($data, -1 * $last) !== str_repeat(chr($last), $last)) {
            return false;
        }
        return substr($data, 0, -1 * $last);
    }
}

?>