<?php

add_filter( 'the_content', 'single_drama' );
function single_drama($content){
    $post_id = get_the_ID();
    $post_type = get_post_type($post_id);
    if ( !($post_type == 'drama' && is_singular()) ) return $content;
    $content = $content ? $content : get_the_content();

    global $wpdb;
    $drama = $wpdb->get_row($wpdb->prepare("SELECT drama.* FROM {$wpdb->prefix}tmu_dramas as drama WHERE drama.`ID` = %d", $post_id), ARRAY_A);

    $title = get_the_title($post_id);

    $drama['star_cast'] = $star_cast = $drama['star_cast'] ? @unserialize($drama['star_cast']) : [];
    $drama['runtime'] = $drama['runtime'] ? $drama['runtime'].' minutes' : '';
    $drama['images'] = rwmb_meta( 'images', [ 'size' => 'medium' ] );
    $drama['videos'] = $drama['videos'] ? @unserialize($drama['videos']) : [];
    $drama['schedule_day'] = !is_array($drama['schedule_day']) ? @unserialize($drama['schedule_day']) : '';
    $drama['next_episode'] = '';
    $drama['total_episodes'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tmu_dramas_episodes as episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.`dramas` = $post_id AND posts.post_status = 'publish'");

    if ($drama['finished']) {
        $drama['last_episode'] = $wpdb->get_row("SELECT episode.* FROM {$wpdb->prefix}tmu_dramas_episodes as episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE `dramas` = $post_id AND posts.post_status = 'publish' ORDER BY episode.`episode_no` DESC");
    } else {
        $current_timestamp = time() + (4 * 60 * 60);
        $schedule_time_string = ' '.$drama['schedule_time'].':00';
        $drama['last_episode'] = $wpdb->get_row("SELECT episode.* FROM {$wpdb->prefix}tmu_dramas_episodes as episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.`dramas` = $post_id AND unix_timestamp(CONCAT(episode.`air_date`, '{$schedule_time_string}'))<=$current_timestamp AND posts.post_status = 'publish' ORDER BY episode.`episode_no` DESC");
        $drama['next_episode'] = $wpdb->get_row("SELECT episode.* FROM {$wpdb->prefix}tmu_dramas_episodes as episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.`dramas` = $post_id AND unix_timestamp(CONCAT(episode.`air_date`, '{$schedule_time_string}'))>$current_timestamp AND posts.post_status = 'publish' ORDER BY episode.`episode_no` ASC");

        $drama['next_episode_timestamp'] = isset($drama['next_episode']->air_date) ? strtotime($drama['next_episode']->air_date) : '';
    }

    // $tmdb_rating['average'] = $drama['average_rating'];
    // $tmdb_rating['count'] = $drama['vote_count'];

    // $table_col = $post_type === 'tv' ? 'tv_series' : 'dramas';
    // $eps = $wpdb->get_results("SELECT ID,average_rating,vote_count FROM {$wpdb->prefix}tmu_{$table_col}_episodes WHERE $table_col = $post_id");
    // if ($eps) {
    // $ep_rating = $tmdb_rating['average']*$tmdb_rating['count'];
    // foreach ($eps as $ep) {
    //     $ep_comments = get_comments(array('post_id' => $ep->ID, 'status' => 'approve'));
    //     foreach($ep_comments as $comment):
    //         $ep_rating += isset($comment->comment_rating) && $comment->comment_rating ? (int)$comment->comment_rating : 0;
    //         $tmdb_rating['count']++;
    //     endforeach;
    //     $ep_rating = $ep_rating+($ep->average_rating*$ep->vote_count);
    //     $tmdb_rating['count'] += $ep->vote_count;
    // }
    // $tmdb_rating['average'] = $tmdb_rating['count'] ? number_format(($ep_rating/$tmdb_rating['count']), 1) : 5;
    // }

    // $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
    // $average_ratings = get_average_ratings($comments, $tmdb_rating);

    $average_ratings = ['average' => $drama['total_average_rating'], 'count' => $drama['total_vote_count']];

    $cast_table = $wpdb->prefix . 'tmu_dramas_cast';
    $cast = $wpdb->get_results($wpdb->prepare("SELECT * FROM $cast_table WHERE dramas = %d", $post_id));

    $crew_table = $wpdb->prefix . 'tmu_dramas_crew';
    $crew = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$crew_table} WHERE dramas = %d", $post_id));

    $director_array = get_tv_series_credit_by_job($crew, 'Director');
    $director_alt = $director_array ? NULL : $wpdb->get_row($wpdb->prepare("SELECT crew.person,crew.job FROM $crew_table as crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.dramas = %d AND (crew.job LIKE '%Co-Director%' OR crew.job LIKE '%Assistant Director%') AND posts.post_status = 'publish' ORDER BY CASE WHEN crew.job LIKE '%Co-Director%' THEN 1 ELSE 2 END", $post_id));
    $director_array = $director_alt ? ['permalink' => get_permalink($director_alt->person), 'name' => get_the_title($director_alt->person)] : $director_array;

    $producer_array = get_tv_series_credit_by_job($crew, 'Producer');
    $producer_alt = $producer_array ? NULL : $wpdb->get_row($wpdb->prepare("SELECT crew.person,crew.job FROM $crew_table as crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.dramas = %d AND (crew.job LIKE '%Co-Producer%' OR crew.job LIKE '%Executive Producer%' OR crew.job LIKE '%Associate Producer%') AND posts.post_status = 'publish' ORDER BY CASE WHEN crew.job LIKE '%Co-Producer%' THEN 1 ELSE 2 END", $post_id));
    $producer_array = $producer_alt ? ['permalink' => get_permalink($producer_alt->person), 'name' => get_the_title($producer_alt->person)] : $producer_array;

    $writer_array = get_tv_series_credit_by_job($crew, 'Writer');
    $musician_array = get_tv_series_credit_by_job($crew, 'Musician');

    $director = $director_array ? '<a href="'.$director_array['permalink'].'" class="credits-member" title="'.$director_array['name'].'">'.$director_array['name'].'</a>' : '';
    $director_job = $director_alt ? $director_alt->job : 'Director';

    $producer = $producer_array ? '<a href="'.$producer_array['permalink'].'" class="credits-member" title="'.$producer_array['name'].'">'.$producer_array['name'].'</a>' : '';
    $producer_job = $producer_alt ? $producer_alt->job : 'Producer';

    $writer = $writer_array ? '<a href="'.$writer_array['permalink'].'" class="credits-member" title="'.$writer_array['name'].'">'.$writer_array['name'].'</a>' : '';
    $musician = $musician_array ? '<a href="'.$musician_array['permalink'].'" class="credits-member" title="'.$musician_array['name'].'">'.$musician_array['name'].'</a>' : '';

    $related_news = get_related_news($title, 4);

    // echo default_schema($title, $_SERVER['REQUEST_URI']);
    $data = '<link rel="stylesheet" href="'.plugin_dir_url( __DIR__ ).'src/css/single-tv-series.css">';
    $data .= '<div class="movie_sec">';
    $data .= breadcrumb();
    $data .= '<div class="grid_movie_sec">';
    $data .= drama_header($drama, $director, $director_job, $producer, $producer_job, $writer, $musician, $average_ratings);
    if ($drama['star_cast']) {
        $tmp_starcast = the_star_cast($drama['star_cast']);
        if ($tmp_starcast) {
            $data .= '<div class="star_casts">';
            $data .= '<div class="short-heading font-size-36 weight-700">';
            $data .= '<h2 class="images-title weight-700 font-size-36">'.$title.' Star Cast</h2><svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" class="svg-icon icon-lg" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path d="M5.622.631A2.153 2.153 0 0 0 5 2.147c0 .568.224 1.113.622 1.515l8.249 8.34-8.25 8.34a2.16 2.16 0 0 0-.548 2.07c.196.74.768 1.317 1.499 1.515a2.104 2.104 0 0 0 2.048-.555l9.758-9.866a2.153 2.153 0 0 0 0-3.03L8.62.61C7.812-.207 6.45-.207 5.622.63z"></path></svg>';
            $data .= '</div>';
            $data .= '<p class="heading-des">Meet the talented star cast of '.$title.' drama.</p>';
            $data .= $tmp_starcast;
            $data .= '</div>';
        }
    }
    if(!$drama['finished'] && $drama['next_episode']):
        $data .= '<div id="upcoming-episode" class="countdown-box">';
        $data .= '<div class="next-ep-details">';
        $data .= '<div class="calendar-icon">';
        $data .= '<svg version="1.1" fill="#619E14" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="32px" height="32px" viewBox="0 0 610.398 610.398" xml:space="preserve"><g><g><path d="M159.567,0h-15.329c-1.956,0-3.811,0.411-5.608,0.995c-8.979,2.912-15.616,12.498-15.616,23.997v10.552v27.009v14.052 c0,2.611,0.435,5.078,1.066,7.44c2.702,10.146,10.653,17.552,20.158,17.552h15.329c11.724,0,21.224-11.188,21.224-24.992V62.553 V35.544V24.992C180.791,11.188,171.291,0,159.567,0z"/><path d="M461.288,0h-15.329c-11.724,0-21.224,11.188-21.224,24.992v10.552v27.009v14.052c0,13.804,9.5,24.992,21.224,24.992 h15.329c11.724,0,21.224-11.188,21.224-24.992V62.553V35.544V24.992C482.507,11.188,473.007,0,461.288,0z"/><path d="M539.586,62.553h-37.954v14.052c0,24.327-18.102,44.117-40.349,44.117h-15.329c-22.247,0-40.349-19.79-40.349-44.117 V62.553H199.916v14.052c0,24.327-18.102,44.117-40.349,44.117h-15.329c-22.248,0-40.349-19.79-40.349-44.117V62.553H70.818 c-21.066,0-38.15,16.017-38.15,35.764v476.318c0,19.784,17.083,35.764,38.15,35.764h468.763c21.085,0,38.149-15.984,38.149-35.764 V98.322C577.735,78.575,560.671,62.553,539.586,62.553z M527.757,557.9l-446.502-0.172V173.717h446.502V557.9z"/><path d="M353.017,266.258h117.428c10.193,0,18.437-10.179,18.437-22.759s-8.248-22.759-18.437-22.759H353.017 c-10.193,0-18.437,10.179-18.437,22.759C334.58,256.074,342.823,266.258,353.017,266.258z"/><path d="M353.017,348.467h117.428c10.193,0,18.437-10.179,18.437-22.759c0-12.579-8.248-22.758-18.437-22.758H353.017 c-10.193,0-18.437,10.179-18.437,22.758C334.58,338.288,342.823,348.467,353.017,348.467z"/><path d="M353.017,430.676h117.428c10.193,0,18.437-10.18,18.437-22.759s-8.248-22.759-18.437-22.759H353.017 c-10.193,0-18.437,10.18-18.437,22.759S342.823,430.676,353.017,430.676z"/><path d="M353.017,512.89h117.428c10.193,0,18.437-10.18,18.437-22.759c0-12.58-8.248-22.759-18.437-22.759H353.017 c-10.193,0-18.437,10.179-18.437,22.759C334.58,502.71,342.823,512.89,353.017,512.89z"/><path d="M145.032,266.258H262.46c10.193,0,18.436-10.179,18.436-22.759s-8.248-22.759-18.436-22.759H145.032 c-10.194,0-18.437,10.179-18.437,22.759C126.596,256.074,134.838,266.258,145.032,266.258z"/><path d="M145.032,348.467H262.46c10.193,0,18.436-10.179,18.436-22.759c0-12.579-8.248-22.758-18.436-22.758H145.032 c-10.194,0-18.437,10.179-18.437,22.758C126.596,338.288,134.838,348.467,145.032,348.467z"/><path d="M145.032,430.676H262.46c10.193,0,18.436-10.18,18.436-22.759s-8.248-22.759-18.436-22.759H145.032 c-10.194,0-18.437,10.18-18.437,22.759S134.838,430.676,145.032,430.676z"/><path d="M145.032,512.89H262.46c10.193,0,18.436-10.18,18.436-22.759c0-12.58-8.248-22.759-18.436-22.759H145.032 c-10.194,0-18.437,10.179-18.437,22.759C126.596,502.71,134.838,512.89,145.032,512.89z"/></g></g></svg>';
        $data .= '</div>';
        $data .= '<div class="airing-in">';
        $data .= '<div class="next-ep-title">Episode '.$drama['next_episode']->episode_no.' airing on</div>';
        $data .= '<div class="next-ep-release">';
        $data .= date( 'F d, Y', $drama['next_episode_timestamp'] ) . ($drama['schedule_timestamp'] ? ' '.date("h:i A", $drama['schedule_timestamp']) : '');
        $data .= '</div>';
        $data .= '</div>';
        $data .= '</div>';

        $timestamp = strtotime($drama['next_episode']->air_date . " " . $drama['schedule_time']);
        $difference = $timestamp - $current_timestamp;
        $days = str_pad(floor($difference / 86400), 2, '0', STR_PAD_LEFT); $hours = str_pad(floor(($difference % 86400) / 3600), 2, '0', STR_PAD_LEFT); $mins = str_pad(floor(($difference % 3600) / 60), 2, '0', STR_PAD_LEFT); $sec = str_pad($difference % 60, 2, '0', STR_PAD_LEFT);

        $data .= '<div class="countdown">';
        $data .= '<div class="days"><div class="value">'.$days.'</div> <div class="label">days</div></div>';
        $data .= '<div class="hours"><div class="value">'.$hours.'</div> <div class="label">hours</div></div>';
        $data .= '<div class="mins"><div class="value">'.$mins.'</div> <div class="label">mins</div></div>';
        $data .= '<div class="secs"><div class="value">'.$sec.'</div> <div class="label">sec</div></div>';
        $data .= '</div>';
        $data .= '</div>';
    endif;

    if ($drama['last_episode'] || $drama['next_episode']):
        $data .= '<div class="episodes-btn short-heading font-size-36 weight-700">';
        $data .= '<h2 class="images-title weight-700 font-size-36">Episodes</h2><a href="'.get_permalink().'episodes/" class="view-all" title="See All Episodes"><span>See All<i class="arrow right"></i></span></a>';
        $data .= '</div>';
        $data .= '<div class="episodes-container-desktop">';
        $data .= desktop_single_drama_episodes($drama);
        $data .= '</div>';
        $data .= '<div class="episodes-container-mobile">';
        $data .= single_drama_episodes($drama, true);
        $data .= '</div>';
    endif;

    if($content):
        $data .= '<div class="movie-story">';
        $data .= '<div class="short-heading font-size-36 weight-700">';
        $data .= '<h2 class="images-title weight-700 font-size-36">'.$title.' Drama Story</h2><svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" class="svg-icon icon-lg" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path d="M5.622.631A2.153 2.153 0 0 0 5 2.147c0 .568.224 1.113.622 1.515l8.249 8.34-8.25 8.34a2.16 2.16 0 0 0-.548 2.07c.196.74.768 1.317 1.499 1.515a2.104 2.104 0 0 0 2.048-.555l9.758-9.866a2.153 2.153 0 0 0 0-3.03L8.62.61C7.812-.207 6.45-.207 5.622.63z"></path></svg>';
        $data .= '</div>';
        $data .= '<div>'.$content.'</div>';
        $data .= '</div>';
    endif;

    $data .= '<div class="watch_list_desktop">'.where_to_watch($drama).'</div>';
    $data .= '<div class="watch_list_mobile">'.where_to_watch($drama).'</div>';

    $data .= '<div class="drama-videos">'.($drama['videos'] ? display_videos($drama['videos']) : '').'</div>';
    $data .= $drama['images'] ? display_images($drama['images']) : '';

    $data .= '<div class="cast_crew" id="cast-and-crew">';
    $data .= '<div class="short-heading font-size-36 weight-700">';
    $data .= '<h2 class="images-title weight-700 font-size-36">'.$title.' Cast & Crew</h2><svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" class="svg-icon icon-lg" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path d="M5.622.631A2.153 2.153 0 0 0 5 2.147c0 .568.224 1.113.622 1.515l8.249 8.34-8.25 8.34a2.16 2.16 0 0 0-.548 2.07c.196.74.768 1.317 1.499 1.515a2.104 2.104 0 0 0 2.048-.555l9.758-9.866a2.153 2.153 0 0 0 0-3.03L8.62.61C7.812-.207 6.45-.207 5.622.63z"></path></svg>';
    $data .= '</div>';

    $data .= '<p class = "heading-des">Check out the '.$title.' Cast & Crew, showcasing the talented cast and the crew that worked tirelessly to bring the '.$title.' drama to the screen.</p>';

    $data .= '<ul class="nav_tabs">';
    $data .= '<li class="tab_item active">Cast</li>';
    $data .= '<li class="tab_item">Crew</li>';
    $data .= '</ul>';
    $data .= '<div class="full_cast" id="cast">'.tv_series_credits($cast, 'cast').'</div>';
    $data .= '<div class="full_crew" id="crew" style="display: none;">'.tv_series_credits($crew, 'crew').'</div>';
    $data .= '</div>';

    $data .= more_like_this($post_id);

    $data .= drama_details_table($drama, $director, $director_job, $producer, $producer_job, $writer, $musician, $director_array['name'] ?? '', $producer_array['name'] ?? '',$writer_array['name'] ?? '',$musician_array['name'] ?? '');

    if ($related_news):
        $data .= '<div class="related-news">';
        $data .= '<h2 class="short-heading weight-700 font-size-36 news-title">'.$title.' Drama News – Latest Updates and Stories</h2>';
        $data .= '<p class = "heading-des">Stay informed with all the '.$title.' Drama related news. From exclusive insights to breaking updates, plot twists, cast news and more so don’t miss any important story about '.$title.'.</p>';
        $data .= $related_news;
        $data .= '</div>';
    endif;

    $keywords = get_linked_terms('keyword', '');
    if($keywords):
        $data .= '<div class="keywords">';
        $data .= '<div class="heading-2">Keywords:</div>';
        $data .= '<div class="tags">'.$keywords.'</div>';
        $data .= '</div>';
    endif;
    $data .= '</div>';
    $data .= '</div>';

    $data .= cast_crew_tabs_script();
    $data .= script_countdown();

    return $data;
}

function drama_details_table($drama, $director, $director_job, $producer, $producer_job, $writer, $musician, $director_name, $producer_name,$writer_name,$musician_name){
    $title = get_the_title();
    $release = $drama['release_date'] ? date( 'd F Y', $drama['release_timestamp'] ) : '';
    $starcast = implode(', ', array_map(function ($cast) { return isset($cast['person']) ? get_linked_post($cast['person']) : ''; }, $drama['star_cast']));
    $channel = get_linked_terms('channel');
    $channel_name = get_the_terms( get_the_ID(), 'channel' );
    $channel_name = isset($channel_name[0]->name) ? $channel_name[0]->name : '';
    // $starcast_names = implode(', ', array_map(function ($cast) { return isset($cast['person']) ? get_the_title($cast['person']) : ''; }, $drama['star_cast']));
    $genres = get_the_terms(get_the_ID(), 'genre');
    $genre = $drama['seo_genre'] ? get_term((int)$drama['seo_genre']) : '';
    $genre_linked = $genre ? '<a href="'.get_term_link($genre).'" title="'.$genre->name.' Dramas">'.$genre->name.'</a>' : '';
    $released = strtotime('today GMT') > $drama[ 'release_timestamp' ];
    $all_generes = implode(', ', array_map(fn($genre) => '<a href="'.get_term_link($genre).'" title="'.$genre->name.' Dramas">'.$genre->name.'</a>', $genres));

    $data = '<div>';
    $data .= '<h2 class="details-section-heading heading-2">'.get_the_title().' '.(isset($channel_name) ? $channel_name : '').' Drama: Release Date, Schedule & Timing</h2>';
    $data .= '<p class="movie_intro" style="margin-bottom: 20px;">';
    $data .= $title.( $released ? ' is a '.$genre_linked : ' is an upcoming' ).' Pakistani drama serial'.($producer && $drama[ 'production_house' ] ? ' which is produced by '.$producer.' under the banner of '.$drama[ 'production_house' ] : '').'. '.($writer || $director ? 'The drama has been '.($writer ? 'written by '.$writer : '').($director ? ($writer ? ' and' : '').' directed by '.$director : '').', ' :'').($released ? 'it released on '.$release.' at '.$channel.' Network. ' : $title.' is all set to be aired on '.$channel.' soon. ').($starcast ? $title.' drama cast includes the following star actors '.$starcast.'.' : '');
    $data .= '</p>';
    $data .= '<table class="movie-details-section">';
    $data .= '<tr class="detail-item"><th class="item-title">Status</th><td class="item-value">'.($drama['release_timestamp'] > time() ? 'Upcoming' : ($drama['finished'] ? 'Ended' : 'Ongoing')).'</td></tr>';
    if($drama[ 'release_date' ]) $data .= '<tr class="detail-item"><th class="item-title">Release Date</th><td class="item-value">'.date( 'd F Y', $drama['release_timestamp'] ).'</td></tr>';
    if($drama[ 'original_title' ]) $data .= '<tr class="detail-item"><th class="item-title">Original Title</th><td class="item-value">'.$drama[ 'original_title' ].'</td></tr>';
    if($drama[ 'tagline' ]) $data .= '<tr class="detail-item"><th class="item-title">Tagline</th><td class="item-value">'.$drama[ 'tagline' ].'</div></div>';
    if($linked_langs = get_linked_terms('language')) $data .= '<tr class="detail-item"><th class="item-title">Language</th><td class="item-value"  title="'.get_the_terms(get_the_ID(), 'language')[0]->name.' Dramas">'.$linked_langs.'</td></tr>';
    if($country = get_linked_terms('country')) '<tr class="detail-item"><th class="item-title">Country</th><td class="item-value" title="List of All '.get_the_terms(get_the_ID(), 'country')[0]->name.' Dramas">'.$country.'</td></tr>';
    if($linked_genres = get_linked_terms('genre')) $data .= '<tr class="detail-item"><th class="item-title">Genre</th><td class="item-value">'.$all_generes.'</td></tr>';
    if($channel) $data .= '<tr class="detail-item"><th class="item-title">Channel</th><td class="item-value" title="'.$channel_name.' Dramas">'.$channel.'</td></tr>';
    if($director) $data .= '<tr class="detail-item"><th class="item-title">'.$director_job.'</th><td class="item-value" title="'.$director_name.' Dramas">'.$director.'</td></tr>';
    if($writer) $data .= '<tr class="detail-item"><th class="item-title">Writer</th><td class="item-value" title="'.$writer_name.' Dramas">'.$writer.'</td></tr>';
    if($producer) $data .= '<tr class="detail-item"><th class="item-title">'.$producer_job.'</th><td class="item-value" title="'.$producer_name.' Dramas">'.$producer.'</td></tr>';
    if($drama[ 'runtime' ]) $data .= '<tr class="detail-item"><th class="item-title">Duration</th><td class="item-value">'.$drama[ 'runtime' ].'</td></tr>';
    if($drama[ 'schedule_timestamp' ] && $drama['schedule_day'] && !$drama['finished']) $data .= '<tr class="detail-item"><th class="item-title">Schedule</th><td class="item-value">'.(($total_sd = count($drama['schedule_day'])) === 7 ? 'Daily' : 'Every ').($total_sd === 1 ? $drama['schedule_day'][0] : ($total_sd === 2 ? implode(' and ', $drama['schedule_day']) : ($total_sd !== 7 ?findMissingDaysAndConsecutiveRange($drama['schedule_day']) : ''))).' at '.date("h:i A", $drama['schedule_timestamp']).' PST</div></div>';
    if($drama['total_episodes']) $data .= '<tr class="detail-item"><th class="item-title">No. of Episodes</th><td class="item-value">'.$drama['total_episodes'].'</td></tr>';
    if($drama[ 'production_house' ]) $data .= '<tr class="detail-item"><th class="item-title">Production House</th><td class="item-value" title="'.$drama[ 'production_house' ].' Dramas">'.$drama[ 'production_house' ].'</td></tr>';
    if($musician) $data .= '<tr class="detail-item"><th class="item-title">Music</th><td class="item-value" title="'.$musician_name.' Dramas">'.$musician.'</td></tr>';
    if($drama[ 'certification' ]) $data .= '<tr class="detail-item"><th class="item-title">Certificate</th><td class="item-value">'.$drama[ 'certification' ].'</td></tr>';
    $data .= '</table>';
    $data .= '</div>';
    return $data;
}

function drama_header($drama, $director, $director_job, $producer, $producer_job, $writer, $musician, $average_ratings){
    $genres = get_linked_terms('genre');
    $last = count($drama['videos'])-1;
    $post_id = get_the_ID();
    global $wpdb;

    $watch_trailer = isset($drama['videos'][$last]) && $drama['videos'][$last] ? '<a href="'.get_permalink($drama['videos'][$last]).'" title="Watch Drama Trailer">WATCH TRAILER</a>' : '';

    $data = '<section class="movie-trailer">';
    $data .= '<div class="feature-image" data-gallery-id="img-1" onclick="featureImage(this)">';
    $data .= '<img '.(has_post_thumbnail() ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url(get_the_ID(), 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title().'" width="100%" height="100%">';
    $data .= '<div class="watch-trailer-btn trailer-desktop">'.$watch_trailer.'</div>';
    $data .= '</div>';
    $data .= '<div class="movie_details">';
    $data .= '<h1 class="font-size-36">'.get_the_title().'</h1>';
    $data .= '<div class="rating-box">'.get_post_rating($average_ratings).'<div class="rate-this">'.rate_this().'</div></div>';
    $data .= '<div class="release">'.($drama['release_timestamp'] ? date( 'Y', $drama['release_timestamp'] ).' <span class="sep">|</span> ' : '').($drama['certification'] ? $drama['certification'].' <span class="sep">|</span> ' : '').($drama['runtime'] ? $drama['runtime'].' <span class="sep">|</span> ' : '').($genres ? '<span class="genres">'.$genres.'</span>' : '').'</div>';
    $data .= '<div class="credits_main">';
    if ($director) $data .= '<div'.(!$producer ? ' class="width-100"' : '').'>'.$director.'<div class="font-size-13">'.$director_job.'</div></div>';
    if ($producer) $data .= '<div'.(!$director ? ' class="width-100"' : '').'>'.$producer.'<div class="font-size-13">'.$producer_job.'</div></div>';
    $data .= '</div>';

    $data .= '<div class="watch-trailer-btn trailer-mobile">'.$watch_trailer.'</div>';

    if ($drama['last_episode'] || $drama['next_episode']) {
        $data .= '<div class="episodes-container">';
        $data .= single_drama_episodes($drama);
        $data .= '</div>';
    }

    $data .= '</div>';
    $data .= '</section>';

    return $data;
}

function single_drama_episodes($drama, $mobile=false) {
    $data = '';
    if ($drama['last_episode']):
        $title = get_the_title($drama['last_episode']->ID);
        $data .= '<div class="episode" id="last-episode">';
        if(!$mobile) $data .= '<p class="episode-head">Last Aired Episode</p>';
        $data .= '<a href="'.get_permalink($drama['last_episode']->ID).'" title="'.$title.'">';
        $data .= '<img '.(has_post_thumbnail($drama['last_episode']->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($drama['last_episode']->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">';
        $data .= '<div class="episode-details">';
        if($mobile) $data .= '<div class="episode-head">Last Aired Episode</div>';
        $data .= '<div class="ep-air-date">AIRED ON '.date( 'd/m/Y', strtotime($drama['last_episode']->air_date) ).'</div>';
        $data .= '<div class="ep-no">Episode '.$drama['last_episode']->episode_no.'</div>';
        $data .= '</div>';
        $data .= '</a>';
        $data .= '</div>';
    endif;

    if(!$drama['finished'] && $drama['next_episode']):
        $title = get_the_title($drama['next_episode']->ID);
        $data .= '<div class="episode" id="next-episode">';
        if(!$mobile) $data .= '<p class="episode-head">Next Episode</p>';
        $data .= '<a href="'.get_permalink($drama['next_episode']->ID).'" title="'.$title.'">';
        $data .= '<img '.(has_post_thumbnail($drama['next_episode']->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($drama['next_episode']->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">';
        $data .= '<div class="episode-details">';
        if($mobile) $data .= '<div class="episode-head">Next Episode</div>';
        $data .= '<div class="ep-air-date">AIRED ON '.date( 'd/m/Y', $drama['next_episode_timestamp'] ).'</div>';
        $data .= '<div class="ep-no">Episode '.$drama['next_episode']->episode_no.'</div>';
        $data .= '</div>';
        $data .= '</a>';
        $data .= '</div>';
    endif;

    return $data;
}

function desktop_single_drama_episodes($drama) {
    $data = '';
    if ($drama['last_episode']):
        $title = get_the_title($drama['last_episode']->ID);
        $episode_plot = $drama['last_episode']->overview ? wp_trim_words(stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('"', ' ', wp_strip_all_tags($drama['last_episode']->overview))))), 20) : get_drama_episode_plot_dessc($drama['last_episode']->ID);
        $data .= '<a href="'.get_permalink($drama['last_episode']->ID).'" title="'.$title.'" class="episode" id="last-episode">
			<div class="episode-section">
				<div class="episode-img">
					<img '.(has_post_thumbnail($drama['last_episode']->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($drama['last_episode']->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">
				</div>
				<div class="episode-meta">
					<p class="episode-head">'.($drama['last_episode']->episode_type === 'finale' ? 'Last' : 'Previous').' Episode</p>
					<div class="ep-air-date">'.date( 'D, M d, Y', strtotime($drama['last_episode']->air_date) ).'</div>
				</div>
			</div>
			<div class="episode-details">
				<div class="ep-title">'.$title.'</div>
				<div class="ep-description">'.$episode_plot.'</div>
			</div>
		</a>';
    endif;

    if(!$drama['finished'] && $drama['next_episode']):
        $title = get_the_title($drama['next_episode']->ID);
        $episode_plot = $drama['next_episode']->overview ? wp_trim_words(stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('"', ' ', wp_strip_all_tags($drama['last_episode']->overview))))), 20) : get_drama_episode_plot_dessc($drama['next_episode']->ID);
        $data .= '<a href="'.get_permalink($drama['next_episode']->ID).'" class="episode" id="next-episode">
			<div class="episode-section">
				<div class="episode-img">
					<img '.(has_post_thumbnail($drama['next_episode']->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($drama['next_episode']->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">
				</div>
				<div class="episode-meta">
					<p class="episode-head">Next Episode</p>
					<div class="ep-air-date">'.date( 'D, M d, Y', $drama['next_episode_timestamp'] ).'</div>
				</div>
			</div>
			<div class="episode-details">
				<div class="ep-title">'.$title.'</div>
				<div class="ep-description">'.$episode_plot.'</div>
			</div>
		</a>';
    endif;
    return $data;
}

function where_to_watch($drama){
    $data = '';
    if ($drama['where_to_watch']) {
        $drama['where_to_watch'] = is_array($drama['where_to_watch']) ? $drama['where_to_watch'] : unserialize($drama['where_to_watch']);
        if (isset($drama['where_to_watch'][0]['channel']) && $drama['where_to_watch'][0]['channel']) {
            $data .= '<div class="short-heading font-size-36 weight-700"><h2 class="images-title weight-700 font-size-36">Where to Watch '.get_the_title().'</h2></div><div class="watch-list-container">';
            foreach ($drama['where_to_watch'] as $item) {
                $channel_name = get_term_by('id', (int) $item['channel'], 'channel')->name;
                $name = explode(' ', $channel_name);
                $name = count($name) > 2 ? $name[0].' '.$name[1] : $channel_name;
                $logo = wp_get_attachment_image_url(get_term_meta( $item['channel'], 'logo', true ), 'full');
                $data .= '<a href="'.$item['url'].'" class="watch-item" rel="nofollow" title="'.$name.'">
							<div class="watch-icon"><img '.($logo ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-background.svg" data-src="'.$logo.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-background.svg"')).' alt="'.get_the_title().'" width="100%" height="100%"></div>
							<div class="watch-title">'.$name.'</div>
						</a>';
            }
            $data .= '</div>';
        }
    }
    return $data;
}

function script_countdown() {
    return "<script>
function startCountdown() {
  const upcomingEpisode = document.getElementById('upcoming-episode');
  if (upcomingEpisode) {
  	const countdown = upcomingEpisode.querySelector('.countdown');
	  const daysElement = countdown.querySelector('.days .value');
	  const hoursElement = countdown.querySelector('.hours .value');
	  const minutesElement = countdown.querySelector('.mins .value');
	  const secondsElement = countdown.querySelector('.secs .value');
	  const dateText = document.querySelector('.next-ep-release').innerHTML;

	  const targetDate = new Date(dateText);
	  console.log(dateText);
	  console.log(targetDate);
	  let interval;

	  function updateCountdown() {
	    const now = new Date();
	    const difference = targetDate - now;

	    if (difference <= 0) {
	      clearInterval(interval);
	      upcomingEpisode.style.display = 'none'; // Hide or remove the element
	      return;
	    }

	    const days = String(Math.floor(difference / (1000 * 60 * 60 * 24))).padStart(2, '0');
	    const hours = String(Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
	    const minutes = String(Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
	    const seconds = String(Math.floor((difference % (1000 * 60)) / 1000)).padStart(2, '0');

	    daysElement.textContent = days;
	    hoursElement.textContent = hours;
	    minutesElement.textContent = minutes;
	    secondsElement.textContent = seconds;
	  }

	  updateCountdown();
	  interval = setInterval(updateCountdown, 1000);
  }
}

startCountdown();
</script>";
}

function findMissingDaysAndConsecutiveRange($days) {
    // Create an array of all possible days with keys for easier comparison
    $allDays = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];

    // Convert the input days to an array of integers representing their indices
    $dayIndices = array_map(function ($day) use ($allDays) {
        return array_search($day, $allDays);
    }, $days);

    // Sort the indices for easier comparison
    sort($dayIndices);

    // Check if there's only one missing day
    if (count($dayIndices) === 6) {
        // Find the missing index
        $missingIndex = array_diff(range(0, 6), $dayIndices);
        sort($missingIndex);
        $missingIndex = $missingIndex[0];


        // Determine the start and end days
        $startDay = $allDays[$missingIndex+1 === 7 ? 0 : $missingIndex+1];
        $endDay = $allDays[($missingIndex + 6) % 7];

        return $startDay . ' to ' . $endDay;
    }

    // Check if the missing days are in consecutive order
    $isConsecutive = true;
    for ($i = 1; $i < count($dayIndices); $i++) {
        if ($dayIndices[$i] - $dayIndices[$i - 1] !== 1) {
            $isConsecutive = false;
            break;
        }
    }

    if ($isConsecutive) {
        // Find the start and end days
        $startDay = $allDays[$dayIndices[0]];
        $endDay = $allDays[$dayIndices[count($dayIndices) - 1]];

        return $startDay . ' to ' . $endDay;
    }

    // If the missing days are not in consecutive order, return the original array
    return implode(', ', $days);
}

function get_drama_episode_plot_dessc($post_id){
    global $wpdb;
    $table_name = $wpdb->prefix.'tmu_seo_options';
    $description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE post_type = 'drama-episode'  AND section = 'single'");
    return $description ? replace_tags($description, 'single', 'drama-episode', $post_id) : '';
}

