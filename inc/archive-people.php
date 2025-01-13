<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once plugin_dir_path( __DIR__ ) . 'modules/celebrities.php';
require_once plugin_dir_path( __DIR__ ) . 'modules/birthday-today.php';

$post_type = get_post_type();
$site_url = get_site_url();
$permalink = $site_url.$_SERVER['REQUEST_URI'];

$posts = celebrities_with_filters();
$schema = default_schema(ucfirst($post_type), $permalink).$posts['schema'];
add_action('wp_head', function() use ($schema) { echo $schema; });
get_header();
$is_drama = get_option("tmu_dramas") == 'on';

?>
  
  <link rel="stylesheet" href="<?= plugin_dir_url( __DIR__ ) ?>src/css/archive-people.css">
  <div <?php generate_do_attr( 'content' ); ?>>
    <main <?php generate_do_attr( 'main' ); ?>>
      <?php do_action( 'generate_before_main_content' ); ?>
      <section>
      	<div class="archive-header">
      		<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
      			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="<?= $site_url ?>" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>
      			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <meta itemprop="item" content="<?= $permalink ?>" /><span itemprop="name"><?= ucfirst($post_type) ?></span><meta itemprop="position" content="2" /></li>
      		</ul>

      		<h1><?= $is_drama ? 'Pakistani Drama Actors & Actresses' : 'All About Actors' ?></h1>

      		<div class="actors-container">
      			
      			<div class="block-seperate">
	      			<div class="heading">
	      				<!-- <h2>Actors</h2> -->
	      			</div>
	      			<?= $posts['data'] ?>
	      		</div>

		      	<?php $birthday_today = birthday_today();
		      	if($birthday_today): ?>
		      		<div class="block-seperate scrollable-section" data-scroll-target="#today-birthday">
			      		<div class="heading birthdays">
			    				<h2>Today's Birthdays</h2><div class="scroll-btns"><button class="scroll-btn scroll-today-birthday-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-today-birthday-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
			    			</div>
			    			<div class="date-today">People Born on <?= date('d F') ?></div>
			    			<div class="today-birthday-flex scrollable-content birthdays-today" id="today-birthday">
			    				<?= $birthday_today ?>
			    			</div>
			    		</div>
		    		<?php endif; ?>

		    		<main class="block-seperate exp--more--secv2">
		      		<div class="heading"><h2>Explore More</h2></div>
		      		<?php explore_more(); ?>
	      		</main>

      			<!-- <div class="heading"><h2>Actors</h2></div>
      			<div class="latest-actors">
      				<?php // latest_actors($post_type); ?>
      			</div> -->
      		</div>

      	</div>
      </section>
    </main>
  </div>

<script>
function scrollRelease(button) {
  const scrollContainer = button.closest('.scrollable-section');
  const scrollTarget = scrollContainer.dataset.scrollTarget;
  const direction = button.dataset.direction ? parseInt(button.dataset.direction) : 1; // Default right scroll

  const scrollElement = document.querySelector(scrollTarget);

  if (scrollElement) {
    scrollElement.scrollLeft += direction * 1000; // Adjust scroll distance as needed
  } else {
    console.warn('Scroll element not found with selector:', scrollTarget);
  }
}
</script>

<?php
do_action( 'generate_after_primary_content_area' );

generate_construct_sidebars();

get_footer();

function latest_actors($post_type){
	global $wpdb;
	$section = $post_type == 'people' ? 'people' : '';
	$table_name = $section ? $wpdb->prefix.'tmu_'.$section : '';
	if ($table_name) {
		$results = $wpdb->get_results("SELECT t.`ID`,t.`net_worth` FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE posts.post_status = 'publish' ORDER BY t.`ID` DESC LIMIT 15");

  	foreach ($results as $result) {
  		$title = get_the_title($result->ID);
  		$permalink = get_permalink($result->ID);
  		?>
  			<a class="actor-box" href="<?= $permalink ?>" title="<?= $title ?>">
	  			<div class="actor-poster">
	  				<img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
	  			</div>
	  			<div class="actor-details">
	  				<h3><?= $title ?></h3>
	  				<?php if ($result->net_worth) { ?> <p class="net-worth">NET WORTH: <?= $result->net_worth ?></p> <?php } ?>
	  			</div>
	  		</a>
  		<?php
  	}
	}
}





function button_left(){
  ?>
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-left">
	  <defs><style> .cls-1 { fill: #fff; } </style> </defs>
	  <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30"/>
	  <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-1" d="M22.885,22.916,16.7,16.722l6.181-6.194-1.9-1.9-8.1,8.1,8.1,8.1Z" transform="translate(-4.084 -1.625)"/>
	</svg>
  <?php
}

function button_right(){
  ?>
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-right">
	  <defs><style>.cls-1 {fill: #fff;}</style></defs>
	  <g id="Group_276" data-name="Group 276" transform="translate(-1290 -476)">
	    <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30" transform="translate(1290 476)"/>
	    <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-1" d="M12.885,22.916l6.181-6.194-6.181-6.194,1.9-1.9,8.1,8.1-8.1,8.1Z" transform="translate(1288.314 474.375)"/>
	  </g>
	</svg>
  <?php
}