<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i" rel="stylesheet">
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-127643496-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-127643496-1');
    </script>
	<?php wp_head(); ?>
    <?php do_action('crypterio_head_end'); ?>
</head>
<body <?php body_class(); ?> ontouchstart="true">
<?php do_action('crypterio_body_start'); ?>
<div id="wrapper">
    <div id="fullpage" class="content_wrapper">

        <header id="header">
			<?php
			if (defined('STM_HB_VER')) {
				do_action('stm_hb', array('header' => 'stm_hb_settings'));
			} else {
				get_template_part('hb_templates/main');
			}
			?>
        </header>

        <div id="main" <?php if (get_theme_mod('footer_show_hide', false)): ?>class="footer_hide"<?php endif; ?>>
			<?php get_template_part('partials/title_box'); ?>
            <div class="container">
