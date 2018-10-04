<?php

require_once CRYPTERIO_INC_PATH . '/crypterio/graph.php';
require_once CRYPTERIO_INC_PATH . '/crypterio/converter.php';


function crypterio_get_cmc_data($data = 'cryptocurrencies')
{
	$datas = get_option('vcw_storage', array());
	if (!empty($datas)) $datas = unserialize($datas);

	$datas = (!empty($datas['data'])) ? $datas['data'] : '';

	if (!empty($datas)) {
		if (!empty($datas[$data])) {
			$datas = $datas[$data];
		}
	}

	if ($data == 'cryptocurrencies') {
		$cmc = array();
		$rate = crypterio_get_rates();
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$key = $data['name'];
				$cmc[$key] = $data;
				$cmc[$key]['price_usd'] = $rate * $data['price_btc'];
				$cmc[$key]['market_cap_usd'] = $rate * $data['market_cap_btc'];
			}
		}
		$datas = $cmc;
	}

	return $datas;
}

function crypterio_get_rates($crypto = 'USD')
{
	$datas = get_option('vcw_storage', array());
	if (!empty($datas)) $datas = unserialize($datas);

	$datas = (!empty($datas['data'])) ? $datas['data'] : '';

	return (!empty($datas['rates'][$crypto]['rate'])) ? $datas['rates'][$crypto]['rate'] : '';
}

function crypterio_get_crypto_rate($crypto = 'ETH')
{
	$usd = crypterio_get_rates();

	$crypto = crypterio_get_rates($crypto);

	return $usd / $crypto;
}

function crypterio_get_crypto_data($name)
{
	$cryptos = crypterio_get_cmc_data();
	if (!empty($cryptos) and !empty($cryptos[$name])) {
		return $cryptos[$name];
	}

	return array();
}

function crypterio_get_user_crypto()
{
	$currencies = array();

	$crypto = get_theme_mod('crypto');
	if (!empty($crypto)) {
		$currencies = array_filter(explode(', ', $crypto));
	}

	return apply_filters('crypterio_get_user_crypto', $currencies);
}

function crypterio_price_view($price, $symbol = '', $position = 'left', $th_sep = ',', $float_sep = '.', $float = 2)
{
	if (empty($symbol)) $symbol = '$';
	$price = number_format($price, $float, $float_sep, $th_sep);
	$price = ($position == 'left') ? $symbol . $price : $price . $symbol;
	return sanitize_text_field($price);
}

function crypterio_get_format()
{
	return 'price_usd';
}

function crypterio_get_btc_format()
{
	return 'price_btc';
}

function crypterio_get_cmc_data_currency()
{

	$transient = 'crypterio_get_cmc_data_currency';
	$json = get_transient($transient);
	if (false === $json) {
		$path = get_template_directory() . '/assets/ids.json';
		$json = json_decode(file_get_contents($path), true);

		set_transient($transient, $json);
	}

	return $json;
}

function crypterio_display_currency_image($currency)
{
	$cmc = crypterio_get_cmc_data_currency();

	if (!empty($cmc) and !empty($cmc[$currency])) {
		//echo '<img src="https://files.coinmarketcap.com/static/img/coins/32x32/' . $cmc[$currency] . '.png" />';
		echo '<img src="https://s2.coinmarketcap.com/static/img/coins/32x32/' . $cmc[$currency] . '.png" />';
	}
}

function crypterio_white_list_data($descriptions = array(
	'wallet'      => '',
	'front_photo' => '',
	'amount'      => '',
))
{
	$user_data = array(
		'first_name'  => array(
			'label' => esc_html__('First name', 'crypterio'),
			'type'  => 'text',
			'value' => '',
		),
		'last_name'   => array(
			'label' => esc_html__('Last name', 'crypterio'),
			'type'  => 'text',
			'value' => '',
		),
		'email'       => array(
			'label' => esc_html__('Email', 'crypterio'),
			'type'  => 'email',
			'value' => '',
		),
		'amount'      => array(
			'label'       => esc_html__('Expected ETH ICO Participation Amount', 'crypterio'),
			'type'        => 'number',
			'value'       => '',
			'description' => $descriptions['amount']
		),
		'wallet'      => array(
			'label'       => esc_html__('ERC-20 Wallet Address', 'crypterio'),
			'type'        => 'text',
			'value'       => '',
			'description' => $descriptions['wallet']
		),
		'front_photo' => array(
			'label'       => esc_html__('Government-Issued ID Card or Passport', 'crypterio'),
			'type'        => 'file',
			'option_type' => 'image_preview',
			'value'       => '',
			'description' => $descriptions['front_photo']
		),
		'country'     => array(
			'label' => esc_html__('Country', 'crypterio'),
			'show'  => 'hide',
			'type'  => 'text',
			'value' => ''
		),
	);

	return apply_filters('crypterio_white_list_data', $user_data);
}

function crypterio_get_countries()
{

	$transient_name = 'stm_countries';
	if (false === ($json_file = get_transient($transient_name))) {
		global $wp_filesystem;

		if (empty($wp_filesystem)) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$json_file = get_template_directory() . '/assets/js/countries.json';
		$json_file = json_decode($wp_filesystem->get_contents($json_file), true);

		set_transient($transient_name, $json_file);
	}

	return apply_filters('crypterio_get_countries', $json_file);
}

function crypterio_check_recaptcha($recaptcha_name = 'recaptcha')
{

	$r = true;

	$recaptcha_enabled = get_theme_mod('enable_recaptcha', 0);
	$recaptcha_public_key = get_theme_mod('recaptcha_public_key');
	$recaptcha_secret_key = get_theme_mod('recaptcha_secret_key');

	if (!empty($recaptcha_enabled) and $recaptcha_enabled and !empty($recaptcha_public_key) and !empty($recaptcha_secret_key)) {
		$post_data = http_build_query(
			array(
				'secret'   => $recaptcha_secret_key,
				'response' => $_POST[$recaptcha_name],
				'remoteip' => $_SERVER['REMOTE_ADDR']
			)
		);
		$opts = array('http' =>
						  array(
							  'method'  => 'POST',
							  'header'  => 'Content-type: application/x-www-form-urlencoded',
							  'content' => $post_data
						  )
		);
		$context = stream_context_create($opts);
		$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
		$result = json_decode($response);

		if (!$result->success) {
			$r = false;
		}
	}
	return $r;
}

function stm_whitelist_data()
{
	$r = array(
		'has_error' => false,
		'errors'    => [],
		'data'      => [],
		'success'   => ''
	);
	$user_data = crypterio_white_list_data();

	/*Check Recapthca*/
	$recaptcha_pass = crypterio_check_recaptcha();
	if (!$recaptcha_pass) {
		$r['has_error'] = true;
		$r['error_message'] = esc_html__('CAPTCHA verification failed.', 'crypterio');
	}


	foreach ($user_data as $name => $data) {
		$error = '';

		switch ($data['type']) {
			case 'number':
				if (empty($_POST[$name])) {
					$error = esc_html__('Field is required', 'crypterio');
				} elseif (!is_numeric($_POST[$name])) {
					$error = esc_html__('Only numbers', 'crypterio');
				}

				if (empty($error)) {
					$r['data'][$name] = floatval($_POST[$name]);
				}
				break;
			case 'email':
				if (empty($_POST[$name])) {
					$error = esc_html__('Field is required', 'crypterio');
				} elseif (!filter_var($_POST[$name], FILTER_VALIDATE_EMAIL)) {
					$error = esc_html__('Invalid email format', 'crypterio');
				}

				if (empty($error)) {
					$r['data'][$name] = sanitize_text_field($_POST[$name]);
				}
				break;
			case 'file':
				if (empty($_FILES[$name])) {
					$error = esc_html__('Field is required', 'crypterio');
				} else {
					$maximum_size = 2048000;
					if ($_FILES[$name]['size'] > $maximum_size) {
						$error = esc_html__("File size limit is 2 mB", 'crypterio');
					} else {
						$info = getimagesize($_FILES[$name]['tmp_name']);
						if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
							$error = esc_html__("Please use a jpg/png/gif image", 'crypterio');
						}
					}

					if (!$error) {
						$r['data'][$name] = $_FILES[$name];
					}

				}
				break;
			default:
				if (empty($_POST[$name])) {
					$error = esc_html__('Field is required', 'crypterio');
				}

				if (empty($error)) {
					$r['data'][$name] = sanitize_text_field($_POST[$name]);
				}
		}

		if ($error) {
			$r['has_error'] = true;
			$r['errors'][$name] = $error;
		}
	}

	$r = apply_filters('stm_crypto_contract_errors', $r);

	/*If no errors*/
	if (!$r['has_error']) {
		/*Create Post*/

		if (!empty($r['demo'])) {
			$r['success'] = esc_html__('Thank you, your data has been submitted. Email will be sent after approval.', 'crypterio');
			wp_send_json($r);
			die;
		}

		$data = $r['data'];

		$proposal = array(
			'post_title'  => $data['first_name'] . ' ' . $data['last_name'],
			'post_status' => 'pending',
			'post_type'   => 'stm_white_list'
		);

		$post_id = wp_insert_post($proposal);

		foreach ($data as $name => $value) {
			if ($user_data[$name]['type'] === 'file') {
				$value = crypterio_upload_photo($value, $post_id);
			}

			update_post_meta($post_id, $name, $value);
		}

		$r['success'] = esc_html__('Thank you, your data has been submitted. Email will be sent after approval.', 'crypterio');

		$to = get_bloginfo('admin_email');
		$subject = esc_html__('New ICO Participant', 'crypterio');
		$body = esc_html__('Please check new ICO Participant.', 'crypterio');

		wp_mail($to, $subject, $body);

		$to = $data['email'];
		$subject = esc_html__('Application submitted', 'crypterio');
		$body = sprintf(
			__('Dear, %s Your Application has been successfully submitted. <br/> We will contact you once Main ICO has been started.', 'crypterio'),
			$data['first_name'] . ' ' . $data['last_name']
		);

		wp_mail($to, $subject, $body);

	}

	wp_send_json($r);
}

add_action('wp_ajax_stm_whitelist_data', 'stm_whitelist_data');
add_action('wp_ajax_nopriv_stm_whitelist_data', 'stm_whitelist_data');

function crypterio_ico_progress($metas, $divider = '/')
{
	if (empty($metas['raised']) and empty($metas['hardcap']) and empty($metas['softcap'])): ?>
        <div class="stm_ico_goal">
            <span><?php esc_html_e('Goal:', 'crypterio'); ?></span>
			<?php esc_html_e('Not set', 'crypterio'); ?>
        </div>
	<?php elseif (empty($metas['raised']) and (!empty($metas['hardcap']) or !empty($metas['softcap']))):
		$goal = (!empty($metas['softcap'])) ? $metas['softcap'] : $metas['hardcap'];
		?>
        <div class="stm_ico_goal">
            <span><?php esc_html_e('Goal:', 'crypterio'); ?></span>
			<?php echo esc_attr($goal); ?>
        </div>
	<?php else:
		$goal = (!empty($metas['softcap'])) ? $metas['softcap'] : $metas['hardcap'];
		$raised = crypterio_remove_but_numbers($metas['raised']);

		if (!empty($metas['hardcap'])
			and !empty($metas['softcap'])) {
			$softcap = crypterio_remove_but_numbers($metas['softcap']);
			if ($raised > $softcap) {
				$goal = $metas['hardcap'];
			}
		}

		$percent = intval(($raised / crypterio_remove_but_numbers($goal)) * 100);

		?>
        <div class="stm_ico_goal in_progress">
            <span><?php echo esc_attr($metas['raised']); ?></span> <label><?php echo $divider; ?></label>
            <bdo><?php echo esc_attr($goal); ?></bdo></i>
            <span class="percent"><?php echo esc_attr($percent) ?>%</span>
        </div>
	<?php endif;
}

function crypterio_rates_score()
{
	return apply_filters('crypterio_rates_score', array(
		'neutral' => esc_html__('Neutral', 'crypterio'),
		'high'    => esc_html__('High', 'crypterio'),
		'medium'  => esc_html__('Medium', 'crypterio'),
		'low'     => esc_html__('Low', 'crypterio'),
	));
}

function crypterio_rates_mark()
{
	return apply_filters('crypterio_rates_mark', array(
		'neutral' => 3.5,
		'high'    => 5,
		'medium'  => 4,
		'low'     => 2,
	));
}

function crypterio_rates_mark_reversed()
{
	return apply_filters('crypterio_rates_mark_reversed', array(
		'neutral' => 2.5,
		'high'    => 2,
		'medium'  => 3,
		'low'     => 5,
	));
}

function crypterio_status_score()
{
	return array(
		'live'     => esc_html__('Active', 'crypterio'),
		'upcoming' => esc_html__('Upcoming', 'crypterio'),
		'finished' => esc_html__('Ended', 'crypterio'),
	);
}

function crypterio_ico_rate($metas)
{
	if (!empty($metas['sponsored']) and $metas['sponsored'] == 'on'): ?>
        <div class="stm_ico_rate stm_ico_rate__sponsored">
			<?php esc_html_e('Sponsored', 'crypterio'); ?>
        </div>
	<?php elseif (!empty($metas['crypterio_rate'])):
		$rates = crypterio_rates_score();
		?>
        <div class="stm_ico_rate stm_ico_rate__<?php echo esc_attr($metas['crypterio_rate']); ?>">
			<?php echo esc_attr($rates[$metas['crypterio_rate']]); ?>
        </div>
	<?php else: ?>
        <div class="stm_ico_rate stm_ico_rate__unrated">
			<?php esc_html_e('Not rated', 'crypterio'); ?>
        </div>
	<?php endif;
}

function crypterio_remove_but_numbers($value)
{
	return preg_replace('/[^0-9]/', '', $value);
}

function crypterio_create_wave_road($road)
{ ?>
    <div class="stm_wave_roadmap__road_content">
		<?php if (!empty($road['date'])): ?>
            <div class="stm_wave_roadmap__road_date">
                <div class="stm_wave_roadmap__road_anchor"></div>
				<?php echo esc_attr($road['date']); ?>
            </div>
		<?php endif; ?>
		<?php if (!empty($road['description'])): ?>
            <div class="stm_wave_roadmap__road_description">
				<?php echo esc_attr($road['description']); ?>
            </div>
		<?php endif; ?>
    </div>
<?php }

function crypterio_check_ico_status($start_date, $end_date)
{
	$now = time();

	if (!empty($start_date) and $start_date > $now) {
		$status = 'upcoming';
	} elseif (!empty($start_date)
		and $start_date < $now
		and !empty($end_date)
		and ($end_date > $now)
	) {
		$status = 'live';
	} else {
		$status = 'finished';
	}

	return apply_filters('crypterio_check_ico_status', $status);
}

/*Submit ICO Logic*/
function stm_submit_ico_fields($enabled = array())
{
	$terms = get_terms('stm_ico_listing_category', array(
		'hide_empty' => false,
	));

	$terms = wp_list_pluck($terms, 'name', 'term_id');

	$fields = array(
		'name'                     => array(
			'label'       => esc_html__('Your name', 'crypterio'),
			'placeholder' => esc_html__('Enter your full name', 'crypterio'),
			'required'    => true,
		),
		'ico_category'             => array(
			'label'       => esc_html__('ICO Category', 'crypterio'),
			'type'        => 'select',
			'choices'     => $terms,
			'placeholder' => esc_html__('Select ICO category', 'crypterio'),
			'required'    => true,
		),
		'email'                    => array(
			'label'       => esc_html__('Your E-mail', 'crypterio'),
			'placeholder' => esc_html__('Enter Your E-mail', 'crypterio'),
			'required'    => true,
		),
		'ico_name'                 => array(
			'label'       => esc_html__('ICO name', 'crypterio'),
			'placeholder' => esc_html__('Enter ICO name', 'crypterio'),
			'required'    => true,
		),
		'softcap'                  => array(
			'label'       => esc_html__('Fundraising Soft Cap Goal', 'crypterio'),
			'placeholder' => esc_html__('Soft Cap value (ETH)', 'crypterio'),
			'required'    => true,
		),
		'hardcap'                  => array(
			'label'       => esc_html__('Fundraising Hard Cap Goal', 'crypterio'),
			'placeholder' => esc_html__('Hard Cap value (ETH)', 'crypterio'),
			'required'    => true,
		),
		'token_price'              => array(
			'label'       => esc_html__('Token Price', 'crypterio'),
			'placeholder' => esc_html__('Enter Token Price (ETH)', 'crypterio'),
			'required'    => true,
		),
		'total_tokens'             => array(
			'label'       => esc_html__('Total Tokens', 'crypterio'),
			'placeholder' => esc_html__('Enter Total Tokens', 'crypterio'),
			'required'    => true,
		),
		'token_type'               => array(
			'label'       => esc_html__('Token Type', 'crypterio'),
			'placeholder' => esc_html__('Enter Token type', 'crypterio'),
			'required'    => true,
		),
		'website'                  => array(
			'label'       => esc_html__('ICO Website', 'crypterio'),
			'placeholder' => esc_html__('Enter ICO Website Url', 'crypterio'),
			'required'    => true,
		),
		'start_date'               => array(
			'label'       => esc_html__('Token sale start date', 'crypterio'),
			'type'        => 'date',
			'placeholder' => esc_html__('Select start date', 'crypterio'),
			'required'    => true,
		),
		'paper-plane'              => array(
			'label'       => esc_html__('Telegram group', 'crypterio'),
			'placeholder' => esc_html__('Enter Telegram group URL', 'crypterio'),
		),
		'end_date'                 => array(
			'label'       => esc_html__('Token sale end date', 'crypterio'),
			'type'        => 'date',
			'placeholder' => esc_html__('Select end date', 'crypterio'),
			'required'    => true,
		),
		'facebook'                 => array(
			'label'       => esc_html__('Facebook account', 'crypterio'),
			'placeholder' => esc_html__('Enter Facebook account', 'crypterio'),
		),
		'twitter'                  => array(
			'label'       => esc_html__('Twitter account', 'crypterio'),
			'placeholder' => esc_html__('Enter Twitter account', 'crypterio'),
		),
		'whitelist'                => array(
			'label'    => esc_html__('Is ICO use Whitelist?', 'crypterio'),
			'type'     => 'radio',
			'required' => true,
		),
		'github'                   => array(
			'label'       => esc_html__('Github account', 'crypterio'),
			'placeholder' => esc_html__('Enter Github account', 'crypterio'),
		),
		'affiliated'               => array(
			'label'    => esc_html__('Are you affiliated with the ICO?', 'crypterio'),
			'type'     => 'radio',
			'required' => true,
		),
		'ticker'                   => array(
			'label'       => esc_html__('Ticker', 'crypterio'),
			'placeholder' => esc_html__('Enter Ticker', 'crypterio'),
		),
		'available_for_token_sale' => array(
			'label'       => esc_html__('Available for token sale', 'crypterio'),
			'placeholder' => esc_html__('Percent of tokens for sale', 'crypterio'),
		),
		'know_your_customer'       => array(
			'label' => esc_html__('Know your customer?', 'crypterio'),
			'type'  => 'radio',
		),
		'min_max_personal_cap'     => array(
			'label'       => esc_html__('Min/Max Personal Cap', 'crypterio'),
			'placeholder' => esc_html__('Enter Min/Max Personal Cap', 'crypterio'),
		),
		'image_url'                => array(
			'label'       => esc_html__('Image Url', 'crypterio'),
			'placeholder' => esc_html__('Enter URL for ICO icon', 'crypterio'),
		),
		'accepts'                  => array(
			'label'       => esc_html__('Accepts', 'crypterio'),
			'placeholder' => esc_html__('Ex. ETH, BTC, USD', 'crypterio'),
		),
		'token_issue'              => array(
			'label'       => esc_html__('Token Issue', 'crypterio'),
			'placeholder' => esc_html__('Enter token issue', 'crypterio'),
		),
		'cant_participate'         => array(
			'label'       => esc_html__('Сant participate', 'crypterio'),
			'placeholder' => esc_html__('Who cant participate', 'crypterio'),
		),
		'number_of_team_members'   => array(
			'label'       => esc_html__('Number of team Members', 'crypterio'),
			'placeholder' => esc_html__('Enter Number of team Members', 'crypterio'),
		),
		'prototype'                => array(
			'label'       => esc_html__('Prototype', 'crypterio'),
			'placeholder' => esc_html__('Enter Prototype name', 'crypterio'),
		),
		'excerpt'                  => array(
			'label'       => esc_html__('Description', 'crypterio'),
			'placeholder' => esc_html__('Enter ICO description', 'crypterio'),
		),
		'whitepaper'               => array(
			'label'       => esc_html__('Whitepaper', 'crypterio'),
			'placeholder' => esc_html__('Enter Whitepaper link', 'crypterio'),
		),
		'team_from'                => array(
			'label'       => esc_html__('Team From', 'crypterio'),
			'placeholder' => esc_html__('Team From', 'crypterio'),
		),
		'image'                    => array(
			'label'       => esc_html__('Image', 'crypterio'),
			'type'        => 'image',
			'placeholder' => esc_html__('Attach Image (Min 760x422px)', 'crypterio'),
		),
	);

	foreach ($fields as $field_key => $field) {
		if (!empty($enabled[$field_key]) and $enabled[$field_key]) {
			unset($fields[$field_key]);
		}
	}

	return apply_filters('stm_submit_ico_fields', $fields);
}


function stm_submit_ico()
{

	$r = array(
		'errors'    => array(),
		'has_error' => false,
		'message'   => '',
	);

	$fields = stm_submit_ico_fields();


	$recaptcha = crypterio_check_recaptcha('g-recaptcha-response');
	if (!$recaptcha) {
		$r['errors'][] = 'recaptcha';
		$r['has_error'] = true;
		$r['message'] = esc_html__('CAPTCHA verification failed.', 'crypterio');
	}

	foreach ($fields as $field_key => $field) {
		$type = (empty($field['type'])) ? 'text' : $field['type'];
		$required = (!empty($field['required']) and $field['required']) ? true : false;

		switch ($type) {
			case 'select' :
				$value = (!empty($_POST[$field_key])) ? sanitize_text_field($_POST[$field_key]) : '';
				if (!empty($value) and !empty($field['choices'][$value])) {
					$fields[$field_key]['value'] = $value;
				}
				break;
			case 'date' :
				if (empty($_POST[$field_key]) and $required) {
					$r['errors'][] = $field_key;
					$r['has_error'] = true;
				} else {
					$fields[$field_key]['value'] = strtotime(sanitize_text_field($_POST[$field_key]));
				}
				break;
			case 'image':
				if (!empty($_FILES[$field_key])) {
					$maximum_size = 2048000;
					if ($_FILES[$field_key]['size'] > $maximum_size) {
						$r['message'] = esc_html__("File size limit is 2 mB", 'crypterio');
						$r['has_error'] = true;
					} else {
						$info = getimagesize($_FILES[$field_key]['tmp_name']);
						if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
							$r['message'] = esc_html__("Please use a jpg/png/gif image", 'crypterio');
							$r['has_error'] = true;
						}
					}

					if (!$r['has_error']) {
						$fields[$field_key]['value'] = $_FILES[$field_key];
					}

				}
				break;
			default:
				if (empty($_POST[$field_key]) and $required) {
					$r['errors'][] = $field_key;
					$r['has_error'] = true;
				} else {
					$fields[$field_key]['value'] = sanitize_text_field($_POST[$field_key]);
				}
		}
	}

	$r = apply_filters('stm_submit_ico_errors', $r);

	if (!$r['has_error']) {


		if (!empty($r['demo'])) {
			$r['message'] = esc_html__('Thank you, your ICO has been submitted. Email will be sent after approval.', 'crypterio');
			wp_send_json($r);
			die;
		}

		$excerpt = (!empty($_POST['excerpt'])) ? wp_kses_post($_POST['excerpt']) : '';

		$proposal = array(
			'post_title'   => $fields['ico_name']['value'],
			'post_status'  => 'pending',
			'post_type'    => 'stm_ico_listing',
			'post_content' => $excerpt,
		);

		$post_id = wp_insert_post($proposal);

		foreach ($fields as $name => $field) {
			$type = (!empty($field['type'])) ? $field['type'] : 'text';
			if ($type === 'image') {
				$value = crypterio_upload_photo($field['value'], $post_id);
			}
			update_post_meta($post_id, $name, $field['value']);

			do_action('stm_icolisting_field_saved', array($post_id, $name, $field['value']));

		}

		if (!empty($fields['ico_category']['value'])) {
			wp_set_object_terms($post_id, array(intval($fields['ico_category']['value'])), 'stm_ico_listing_category');
		}

		//wp_delete_post($post_id, true);

		$r['message'] = esc_html__('Thank you, your ICO has been submitted.', 'crypterio');

		$to = get_bloginfo('admin_email');
		$subject = esc_html__('New ICO', 'crypterio');
		$body = esc_html__('Please check new ICO.', 'crypterio');

		wp_mail($to, $subject, $body);

	} else {
		$r['message'] = (empty($r['message'])) ? esc_html__('Please, fill all required fields.', 'crypterio') : $r['message'];
	}


	wp_send_json($r);
}

add_action('wp_ajax_stm_submit_ico', 'stm_submit_ico');
add_action('wp_ajax_nopriv_stm_submit_ico', 'stm_submit_ico');

function crypterio_display_posttimeline($key, $post, $counter)
{
	extract($post);
	/**
	 * @var $id
	 * @var $title
	 * @var $image
	 * @var $year
	 * @var $excerpt
	 * @var $url
	 */
	$post_classes = array(
		'stm_posttimeline__post',
		'stm_posttimeline__post_' . $id,
	);
	$post_classes[] = (has_post_thumbnail($id)) ? 'has_thumb' : 'no_thumb';
	$post_classes[] = ($key === 0) ? 'main_year' : 'has_year';
	$post_classes[] = get_post_format($id);
	?>
<div class="<?php echo esc_attr(implode(' ', $post_classes)); ?>" data-related="<?php echo intval($year); ?>"
     data-key="<?php echo intval($counter); ?>">
	<?php if (!empty($post['url'])): ?>
    <a href="<?php echo esc_url($url); ?>"
<?php else: ?>
    <div
<?php endif; ?>
    class="stm_posttimeline__post_inner no_deco ttc">
	<?php if (in_array('main_year', $post_classes)): ?>
    <div class="stm_posttimeline__year heading_font" data-year="<?php echo intval($year); ?>">
        <span><?php echo intval($year) ?></span>
    </div>
<?php endif; ?>
	<?php if (!empty($image)): ?>
    <div class="stm_posttimeline__post_image mbc_b">
		<?php echo html_entity_decode($image); ?>
    </div>
<?php endif; ?>

    <div class="stm_posttimeline__post_info heading_font">
        <div
                class="stm_posttimeline__post_info-date mtc"><?php echo sanitize_text_field(get_the_date('j F', $id)); ?></div>
        <div class="stm_posttimeline__post_info-author"
             data-content="<?php esc_html_e('By', 'crypterio'); ?>"><?php the_author(); ?>
            <span><?php echo get_avatar(get_the_author_meta('email'), 174); ?></span></div>
    </div>

    <div class="stm_posttimeline__post_title">
        <h5><?php echo sanitize_text_field($title); ?></h5>
    </div>
    <div class="stm_posttimeline__post_excerpt"><?php echo sanitize_text_field($excerpt); ?></div>
	<?php if (!empty($post['url'])): ?>
    </div>
<?php else: ?>
    </div>
<?php endif; ?>
    </div>
<?php }

function crypterio_parts_config()
{
	$parts = array(
		'default'     => array(
			'date_or_logo' => 'date',
			'ico_grid'     => 'stm_ico_grid',
			'categories'   => false
		),
		'ico_listing' => array(
			'date_or_logo' => 'logo',
			'ico_grid'     => 'stm_ico_listing_grid',
			'categories'   => true
		)
	);

	$config = crypterio_config();
	$layout = $config['layout'];


	return (!empty($parts[$layout])) ? $parts[$layout] : $parts['default'];
}