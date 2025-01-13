<?php
add_filter( 'the_content', 'single_season' );
function single_season($content){
  if (get_post_type() !== 'season') return $content;
  
  global $wpdb;
  $table_name = $wpdb->prefix.'tmu_tv_series_seasons';
  $season_id = get_the_ID();
  $name = get_the_title($season_id);
  $season = $wpdb->get_row("SELECT * FROM $table_name WHERE ID = $season_id");
  $series_id = $season->tv_series;
  $series_title = get_the_title($series_id);
  $season_no_string = $season->season_no;
  $season_no = ($season_no_string === '0') ? 0 : (is_numeric($season_no_string) ? intval($season_no_string) : false);
  $release_date = $season->air_date;
  $poster_url= has_post_thumbnail($season_id) ? get_the_post_thumbnail_url($season_id) : '';
  $permalink = get_permalink($season_id);
  $series_link = get_permalink($series_id);
  $prev = ($season_no!=0) ? $wpdb->get_var("SELECT season.ID FROM $table_name season JOIN {$wpdb->prefix}posts AS posts ON (season.ID = posts.ID) WHERE season.`tv_series` = $series_id AND season.`season_no` = $season_no-1 AND posts.post_status = 'publish'") : '';
  $next = $wpdb->get_var("SELECT season.ID FROM $table_name season JOIN {$wpdb->prefix}posts AS posts ON (season.ID = posts.ID) WHERE season.`tv_series` = $series_id AND season.`season_no` = $season_no+1 AND posts.post_status = 'publish'");
  $genres = json_encode(wp_get_object_terms( $series_id, 'genre', array('fields' => 'names') ));

  $table_name = $wpdb->prefix.'tmu_tv_series_episodes';
  $sql = "SELECT episode.ID,episode.episode_title,episode.air_date,episode.runtime,episode.overview,episode.episode_no,episode.average_rating,episode.vote_count FROM $table_name episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.`season_no`=$season_no AND episode.`tv_series`=$series_id AND posts.post_status = 'publish' ORDER BY episode.episode_no";
  $episodes = $wpdb->get_results($sql);

  ?>
    <link rel="stylesheet" href="<?= plugin_dir_url( __DIR__ ) ?>src/css/single-season.css">
    <section class="season-header">
      <div class="season-column">
        <a class="season-poster" href="<?= $permalink ?>" title="<?= $name ?>">
          <img <?= ($poster_url ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.$poster_url.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $name ?>" width="100%" height="100%">
        </a>
        <div class="season">
          <div class="title-column">
            <h1 class="season-title">
              <a href="<?= $permalink ?>" title="<?= $name ?>"><?= $name ?></a>
              <?php if ($release_date) { ?><span class="season-release-year">(<?= date('Y', strtotime($release_date)); ?>)</span><?php } ?>
            </h1>
            <a href="<?= $series_link.'seasons/' ?>" class="parent-post" title="Back to season list">Back to season list</a>
          </div>
          <?php social_sharing_buttons($permalink, $name.' Episode List'); ?>
        </div>
      </div>
    </section>
    <section class="season_selector">
      <div class="next_prev">
        <?php next_prev_season($prev, $next); ?>
      </div>
    </section>
    <section class="column_wrapper">
      <div class="content_wrapper">
        <?php
        if ($episodes) {
          ?><h3 class="episodes_title">Episodes <span><?= $season->total_episodes ?></span></h3>
          <div class="episode_list"><?php foreach ($episodes as $episode) season_episode_block($episode); ?></div>
        <?php } ?>
      </div>
    </section>
    <section class="season_selector">
      <div class="next_prev">
        <?php next_prev_season($prev, $next); ?>
      </div>
    </section>

    <?php
}

function season_episode_block($episode){
  if ($episode && get_post_status($episode->ID) == 'publish') {
    $permalink = get_permalink($episode->ID);
    $release_date = ''; $release = $episode->air_date; $release_date = $release ? new DateTime($release) : '';
    $poster_url = has_post_thumbnail( $episode->ID ) ? get_the_post_thumbnail_url($episode->ID,'full') : ''; plugin_dir_url( __DIR__ ).'src/images/no-image.webp';
    $episode->runtime = (int)$episode->runtime; $runtime_hours = $episode->runtime/60; $runtime_hours = $runtime_hours > 1 ? round($runtime_hours) : 0;
    $episode->runtime = $episode->runtime ? ($runtime_hours==0 ? '' : $runtime_hours.' hour ').($episode->runtime%60).' minutes' : '';

    $tmdb_rating = [];
    $tmdb_rating['average'] = $episode->average_rating;
    $tmdb_rating['count'] = $episode->vote_count;
    $comments = get_comments(array('post_id' => $episode->ID, 'status' => 'approve'));
    $average_ratings = get_average_ratings($comments, $tmdb_rating);
    ?>
      <div class="single-episode">
        <a class="image" href="<?= $permalink ?>" title="<?= $episode->episode_title ?>"><img <?= ($poster_url ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp" data-src="'.$poster_url.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp"') ) ?> alt="<?= $episode->episode_title ?>" width="227" height="127"></a>
        <div class="info">
          <div class="title">
            <div class="episode_number"><?= $episode->episode_no ?></div>
            <div class="episode_title">
              <a class="ep-title" href="<?= $permalink ?>" title="<?= $episode->episode_title ?>"><?= $episode->episode_title ?></a>
              <div class="date">
                <span class="release">Air Date: <?= $release_date ? $release_date->format('j F Y') : '' ?></span><?= ($episode->runtime ? ' | <span class="runtime">Runtime: '.$episode->runtime.'</span>' : '') ?>
              </div>
              <div class="item-rating"><svg id="glyphicons-basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="#ffffff" id="star" d="M27.34766,14.17944l-6.39209,4.64307,2.43744,7.506a.65414.65414,0,0,1-.62238.85632.643.643,0,0,1-.38086-.12744l-6.38568-4.6383-6.38574,4.6383a.643.643,0,0,1-.38086.12744.65419.65419,0,0,1-.62238-.85632l2.43744-7.506L4.66046,14.17944A.65194.65194,0,0,1,5.04358,13h7.89978L15.384,5.48438a.652.652,0,0,1,1.24018,0L19.06476,13h7.89978A.652.652,0,0,1,27.34766,14.17944Z"/></svg> <span><?= $average_ratings['average'] ?></span></div>
            </div>
          </div>
          <div class="overview"><?= $episode->overview ?></div>
        </div>
      </div>
    <?php
  }
  // return '{"@type":"TVEpisode","episodeNumber":"'.$episode->episode_no.'","url":"'.$permalink.'","name":"'.$episode->episode_title.'","description":"'.$episode->overview.'","dateCreated":"'.$release.'"}';
}

function next_prev_season($prev, $next){
  if ($prev) { ?>
    <span class="previous">
        <a class="previous_s" title="Previous Season" alt="Previous Season" href="<?= get_permalink($prev) ?>"><span class="arrow-thin-left" style="background-image: url('<?= plugin_dir_url( __DIR__ ) ?>src/icons/left-arrow.svg');"></span> <span class="hover"><?= rwmb_meta( 'season_name', '', $prev ) ?></span></a>
    </span>
  <?php }

  if ($next) { ?>
    <span class="next<?= $prev ? '' : ' leftitem'?>">
        <a class="next_s" title="Next Season" alt="Next Season" href="<?= get_permalink($next) ?>"><span class="hover"><?= rwmb_meta( 'season_name', '', $next ) ?></span> <span class="arrow-thin-right" style="background-image: url('<?= plugin_dir_url( __DIR__ ) ?>src/icons/right-arrow.svg');"></span></a>
    </span>
  <?php } 
}

function social_sharing_buttons($current_page, $title){
?>
  <div class="social-sharing-icons">
    <a class="social-icon facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?= $current_page ?>" title="Share to facebook">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
        <g transform="translate(-16.64,-16.64) scale(1.13,1.13)"><g fill="#0073c2" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M42,3h-34c-2.8,0 -5,2.2 -5,5v34c0,2.8 2.2,5 5,5h34c2.8,0 5,-2.2 5,-5v-34c0,-2.8 -2.2,-5 -5,-5zM37,19h-2c-2.1,0 -3,0.5 -3,2v3h5l-1,5h-4v16h-5v-16h-4v-5h4v-3c0,-4 2,-7 6,-7c2.9,0 4,1 4,1z"></path></g></g></g>
      </svg>
    </a>
    <a class="social-icon twitter" href="https://twitter.com/intent/tweet?text=<?= $title ?>&url=<?= $current_page ?>" title="Share to twitter">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
        <g transform="translate(-24.32,-24.32) scale(1.19,1.19)"><g fill="#000000" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M11,4c-3.866,0 -7,3.134 -7,7v28c0,3.866 3.134,7 7,7h28c3.866,0 7,-3.134 7,-7v-28c0,-3.866 -3.134,-7 -7,-7zM13.08594,13h7.9375l5.63672,8.00977l6.83984,-8.00977h2.5l-8.21094,9.61328l10.125,14.38672h-7.93555l-6.54102,-9.29297l-7.9375,9.29297h-2.5l9.30859,-10.89648zM16.91406,15l14.10742,20h3.06445l-14.10742,-20z"></path></g></g></g>
      </svg>
    </a>
    <a class="social-icon whatsapp" href="https://api.whatsapp.com/send?text=<?= $title ?>: <?= $current_page ?>" title="Share to whatsapp">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
        <g transform="translate(-29.44,-29.44) scale(1.23,1.23)"><g fill="#45d354" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(8.53333,8.53333)"><path d="M15,3c-6.627,0 -12,5.373 -12,12c0,2.25121 0.63234,4.35007 1.71094,6.15039l-1.60352,5.84961l5.97461,-1.56836c1.74732,0.99342 3.76446,1.56836 5.91797,1.56836c6.627,0 12,-5.373 12,-12c0,-6.627 -5.373,-12 -12,-12zM10.89258,9.40234c0.195,0 0.39536,-0.00119 0.56836,0.00781c0.214,0.005 0.44692,0.02067 0.66992,0.51367c0.265,0.586 0.84202,2.05608 0.91602,2.20508c0.074,0.149 0.12644,0.32453 0.02344,0.51953c-0.098,0.2 -0.14897,0.32105 -0.29297,0.49805c-0.149,0.172 -0.31227,0.38563 -0.44727,0.51563c-0.149,0.149 -0.30286,0.31238 -0.13086,0.60938c0.172,0.297 0.76934,1.27064 1.65234,2.05664c1.135,1.014 2.09263,1.32561 2.39063,1.47461c0.298,0.149 0.47058,0.12578 0.64258,-0.07422c0.177,-0.195 0.74336,-0.86411 0.94336,-1.16211c0.195,-0.298 0.39406,-0.24644 0.66406,-0.14844c0.274,0.098 1.7352,0.8178 2.0332,0.9668c0.298,0.149 0.49336,0.22275 0.56836,0.34375c0.077,0.125 0.07708,0.72006 -0.16992,1.41406c-0.247,0.693 -1.45991,1.36316 -2.00391,1.41016c-0.549,0.051 -1.06136,0.24677 -3.56836,-0.74023c-3.024,-1.191 -4.93108,-4.28828 -5.08008,-4.48828c-0.149,-0.195 -1.21094,-1.61031 -1.21094,-3.07031c0,-1.465 0.76811,-2.18247 1.03711,-2.48047c0.274,-0.298 0.59492,-0.37109 0.79492,-0.37109z"></path></g></g></g>
      </svg>
    </a>
    <a class="social-icon telegram" href="https://t.me/share/url?url=<?= $current_page ?>&text=<?= $title ?>&to=" title="Share to telegram">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
        <g transform="translate(-10.24,-10.24) scale(1.08,1.08)"><g fill="#02c8f0" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M25,2c12.703,0 23,10.297 23,23c0,12.703 -10.297,23 -23,23c-12.703,0 -23,-10.297 -23,-23c0,-12.703 10.297,-23 23,-23zM32.934,34.375c0.423,-1.298 2.405,-14.234 2.65,-16.783c0.074,-0.772 -0.17,-1.285 -0.648,-1.514c-0.578,-0.278 -1.434,-0.139 -2.427,0.219c-1.362,0.491 -18.774,7.884 -19.78,8.312c-0.954,0.405 -1.856,0.847 -1.856,1.487c0,0.45 0.267,0.703 1.003,0.966c0.766,0.273 2.695,0.858 3.834,1.172c1.097,0.303 2.346,0.04 3.046,-0.395c0.742,-0.461 9.305,-6.191 9.92,-6.693c0.614,-0.502 1.104,0.141 0.602,0.644c-0.502,0.502 -6.38,6.207 -7.155,6.997c-0.941,0.959 -0.273,1.953 0.358,2.351c0.721,0.454 5.906,3.932 6.687,4.49c0.781,0.558 1.573,0.811 2.298,0.811c0.725,0 1.107,-0.955 1.468,-2.064z"></path></g></g></g>
      </svg>
    </a>
  </div>
<?php
}