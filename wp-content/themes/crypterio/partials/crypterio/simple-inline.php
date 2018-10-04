<?php
$currencies = crypterio_get_user_crypto();
$currencies_info = crypterio_get_cmc_data();

$currency_format = crypterio_get_format();


if (!empty($currencies)): ?>
    <div class="stm_currencies_simple_list">
		<?php foreach ($currencies as $currency_name): ?>
			<?php if (!empty($currencies_info[$currency_name])): ?>
                <div class="scsl__single">
                    <div class="inner">
						<?php $currency_info = $currencies_info[$currency_name];
						$change_1h = ($currency_info['change_1h'] < 0) ? 'minus' : 'plus';
						$change_24h = ($currency_info['change_24h'] < 0) ? 'minus' : 'plus';
						$change_7d = ($currency_info['change_7d'] < 0) ? 'minus' : 'plus';
						$income = ( $currency_info[$currency_format] * $currency_info['change_24h'] ) / 100;
						?>

                        <div class="scsl__name">
                            <span class="scsl__image">
                                <?php crypterio_display_currency_image($currency_name); ?>
                            </span>
							<?php echo sanitize_text_field($currency_info['name']); ?>
                            <span class="scsl__income scsl__change_<?php echo esc_attr($change_24h); ?>"><?php echo crypterio_price_view($income, ' '); ?></span>
                            <div class="scsl__price">
								<?php echo crypterio_price_view($currency_info[$currency_format]); ?>
                                <span class="scsl__change_<?php echo esc_attr($change_24h); ?>"><?php echo sanitize_text_field($currency_info['change_24h']); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
		<?php endforeach; ?>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function(){
            var $ = jQuery;

            $('.stm_currencies_simple_list').simplemarquee({
                speed: 40,
                space: 0,
                handleHover: true,
                handleResize: true
            });
        });
    </script>
<?php endif;