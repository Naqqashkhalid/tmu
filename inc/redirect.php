<?php

function redirect_url($slug){
	$slug = substr(urldecode($slug), 10);
	$short_slug = substr($slug, 0, 26);
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
	add_action('wp_head', 'meta_inf_redirect');
	get_header(); ?>

	<div <?php generate_do_attr( 'content' ); ?> style="width: 100%;">
		<main <?php generate_do_attr( 'main' ); ?>>
			<?php do_action( 'generate_before_main_content' ); ?>
			<div style="text-align: center;">
				<h1>See you later!</h1>
				<p>You are leaving Eonline and you will be automatically redirected to <span class="text-primary"><?= $short_slug ?>...</span> in a moment.</p>
				<a href="https://www.eonline.live/"><button class="btn-primary" title="Back to Eonline">Back to Eonline</button></a>
			</div>
			<?php do_action( 'generate_after_main_content' ); ?>
		</main>
	</div>

	<?php

	do_action( 'generate_after_primary_content_area' );

	get_footer();
	redirect_javascript($slug);

}

function meta_inf_redirect(){ ?>
		<title>About Us</title>
		<meta name="description" content="About Us" />
        <meta property="og:type"          content="website" />
        <meta property="og:title"         content="About Us" />
        <meta property="og:description"   content="About Us" />
        <meta name="robots" content="nofollow, noindex, max-snippet:-1, max-image-preview:-1, max-image-preview:large"/>
<?php }

function redirect_javascript($url){ ?>
<script type="text/javascript">
	function redirectAfterDelay() {
	  const targetUrl = "<?= $url ?>";
	  const delay = 5000;

	  setTimeout(function() {
	    window.location.href = targetUrl;
	  }, delay);
	}

	redirectAfterDelay();
</script>
<?php }