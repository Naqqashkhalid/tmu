<?php

function meta_keywords($post_type){
    global $wpdb;

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $uri);
    if(isset($segments[1]) && isset($segments[3]) && $segments[1] === 'drama' && ($segments[3] === 'episodes')) return $wpdb->get_var("SELECT keywords FROM {$wpdb->prefix}tmu_seo_options WHERE name = 'drama-episodes'");

    if (is_front_page()) return $wpdb->get_var("SELECT keywords FROM {$wpdb->prefix}tmu_seo_options WHERE name = 'homepage'");


    if (is_tax()) {
        $term = get_queried_object();
        $options = get_options(["tmu_dramas","tmu_movies","tmu_tv_series"]);
        if (is_tax( 'genre' )) {
            return $options['tmu_dramas'] === 'on' ? 'Pakistani '.$term->name.' Dramas, New Pakistani '.$term->name.' dramas, upcoming Pakistani '.$term->name.' dramas Pakistani '.$term->name.' dramas list' : '';
        } elseif (is_tax( 'channel' )) {
            return $options['tmu_dramas'] === 'on' ? 'New '.$term->name.' drama, upcoming '.$term->name.' drama, '.$term->name.' dramas %current_year%, Best '.$term->name.' dramas, top '.$term->name.' dramas, '.$term->name.' drama list, '.$term->name.' drama today' : '';
        } elseif (is_tax( 'network' )) {
            return $options['tmu_dramas'] === 'on' ? 'New '.$term->name.' TV Shows, upcoming '.$term->name.' TV Shows, '.$term->name.' TV Shows %current_year%, Best '.$term->name.' TV Shows, top '.$term->name.' TV Shows, '.$term->name.' TV Shows list, '.$term->name.' TV Shows today, '.$term->name.' new Release' : '';
        } elseif(is_tax( 'by-year' )) {
            return $options['tmu_dramas'] === 'on' ? 'Pakistani drama '.$term->name.', new Pakistani drama '.$term->name.', Pakistani drama '.$term->name.' list, best Pakistani dramas '.$term->name.', top Pakistani dramas '.$term->name : '';
        } elseif (is_tax( 'language' )) {
            // return $options['tmu_dramas'] === 'on' ? '' : '';
        }
    }


    if (is_archive()) return $wpdb->get_var("SELECT keywords FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = '{$post_type}' AND section = 'archive'");
    
    if (is_single() || is_page()) {
        $title = get_the_title();
        $post_id = get_the_ID();
        
        $meta_keywords = $wpdb->get_var("SELECT meta_keywords FROM {$wpdb->prefix}posts WHERE `ID` = {$post_id}");
        if($meta_keywords) return $meta_keywords;
        if ($post_type === 'drama') {
            $channels = get_the_terms( $post_id, 'channel' );
            $channel = is_array($channels) ? array_pop($channels) : '';
            return $title.' drama, '.$title.' drama cast, '.$title.' drama photos, '.$title.' drama reviews, '.$title.' drama release date, '.$title.' drama story, '.$title.' drama schudule, '.$title.' drama OST, '.$title.' drama trailer, '.($channel ? $title.' '.$channel->name.' drama, ' : '').$title.' drama timing, '.$title.' cast, '.$title.' timing, '.$title.' latest episode, '.$title.' total episode, '.$title.' drama youtube, '.$title.' drama dailymotion';
        }

        if ($post_type === 'people') {
            if (post_type_exists( 'drama' )) {
                $keywords = $title.' dramas, '.$title.' upcoming dramas, ';
            } else {
                $keywords = $title.' movies, '.$title.' upcoming movies, '.$title.' tv shows, '.$title.' upcoming tv shows, ';
            }
            $keywords .= $title.' age, '.$title.' height, '.$title.' family, '.$title.' wife, '.$title.' photos, '.$title.' images, '.$title.' net worth, '.$title.' biography, '.$title.' videos, '.$title.' latest news, '.$title.' news';
            
            return $keywords;
        }

        if ($post_type === 'tv') {
            $networks = get_the_terms( $post_id, 'network' );
            $network = is_array($networks) ? array_pop($networks) : '';
            $years = get_the_terms( $post_id, 'by-year' );
            $year = is_array($years) ? array_pop($years) : '';
            return $title.' Tv Show, '.($year ? $title.' '.$year->name.', ' : '').$title.' cast, '.$title.' trailer, '.$title.' release date, '.$title.' reviews, '.($network ? $title.' '.$network->name.', ' : '').$title.' streaming, where to watch '.$title.', '.$title.' seasons, '.$title.' episodes, '.$title.' current season relase date, '.$title.' upcoming season relase date';
        }

        if ($post_type === 'movie') {
            $years = get_the_terms( $post_id, 'by-year' );
            $year = is_array($years) ? array_pop($years) : '';
            return $title.' movie, '.($year ? $title.' '.$year->name.', ' : '').$title.' cast, '.$title.' trailer, '.$title.' reviews, '.$title.' release date, '.$title.' showstimes, '.$title.' box office, watch '.$title;
        }

        if ($post_type === 'episode' || $post_type === 'drama-episode') {
            $section = $post_type === 'episode' ? 'tv_series' : 'dramas';
            $table_name = $wpdb->prefix.'tmu_'.$section.'_episodes';
            $episode = $wpdb->get_row("SELECT episode_no,{$section} FROM {$table_name} WHERE ID = $post_id");
            if ($episode) {
                $title = get_the_title($episode->$section);
                return $title.' drama epsiode '.$episode->episode_no.', watch '.$title.' drama epsiode '.$episode->episode_no.', '.$title.' drama epsiode '.$episode->episode_no.' youtube, '.$title.' drama epsiode '.$episode->episode_no.' dailymotion';
            }
        }

        if ($post_type === 'video') {
            $table_name = $wpdb->prefix.'tmu_videos';
            $video = $wpdb->get_row("SELECT post_id,video_data FROM {$table_name} WHERE ID = $post_id");
            $parent_id = $video->post_id;
            $video_data = $video->video_data ? unserialize($video->video_data) : '';
            $video_keywords = '';

            if ($parent_id) {
                $parent_post_type = $parent_id ? get_post_type($parent_id) : '';
                $parent_title = $parent_id ? get_the_title($parent_id) : '';
                if ($parent_post_type === 'drama' || $parent_post_type === 'movie' || $parent_post_type === 'tv') {
                    if ($video_data['content_type'] === 'Trailer' || $video_data['content_type'] === 'Teaser') $video_keywords .= $parent_title.' '.$parent_post_type.' trailer, '.$parent_title.' '.$parent_post_type.' official trailer, '.$parent_title.' '.$parent_post_type.' teaser, '.$parent_title.' '.$parent_post_type.' video';
                    if ($parent_post_type === 'drama') $video_keywords .= $parent_title.' drama promo';
                    if ($video_data['content_type'] === 'Feature' || $video_data['content_type'] === 'Clip') $video_keywords .= $parent_title.' '.$parent_post_type.' clip, '.$parent_title.' '.$parent_post_type.' shorts, '.$parent_title.' '.$parent_post_type.' video, '.$parent_title.' '.$parent_post_type.' feature';
                    if ($video_data['content_type'] === 'OST') $video_keywords .= $parent_title.' drama OST, '.$parent_title.' drama song, '.$parent_title.' drama OST lyrics';
                }
                if ($parent_post_type === 'people') $video_keywords .= $parent_title.' clip, '.$parent_title.' shorts, '.$parent_title.' video, '.$parent_title.' feature';
            }

            return $video_keywords;
        }
    }
}