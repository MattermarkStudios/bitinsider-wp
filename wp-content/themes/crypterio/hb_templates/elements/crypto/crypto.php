<?php
$config = crypterio_config();

$inline = array(
	'counselor',
	'crypto_blog',
);

$type = in_array($config['layout'], $inline) ? 'inline' : 'list';
get_template_part('partials/crypterio/simple', $type);