<?php

/**
 * My Account Page — AI Nathan Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiNathan
 * @version 3.5.0
 */

defined('ABSPATH') || exit;
?>

<div class="ma__layout">

	<aside class="ma__sidebar">
		<?php do_action('woocommerce_account_navigation'); ?>
	</aside>

	<div class="ma__content">
		<?php do_action('woocommerce_account_content'); ?>
	</div>

</div>
