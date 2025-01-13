<?php

function schema() {
  $post_type = get_post_type();
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  if (is_home() || is_front_page()) echo schema_home();
  if (is_single()) {
    if ($post_type === 'post') echo article_schema();
    if ($post_type === 'movie') echo schema_movie();
    if ($post_type === 'person') echo schema_person();
    if ($post_type === 'video') echo schema_video();
    if ($post_type === 'tv' || $post_type === 'drama') echo schema_tv();
    if ($post_type === 'drama-episode' || $post_type === 'episode') echo schema_episode();
    if ($post_type === 'season') echo schema_season();
  }
}

add_action('wp_head', 'schema');

function schema_tv(){
  $post_id = get_the_ID();
  $permalink = get_permalink($post_id);
  $title = get_the_title($post_id);
  $img_url = get_the_post_thumbnail_url($post_id, 'full');
  $genres_array = wp_get_object_terms( $post_id, 'genre', array('fields' => 'names') );
  $post_type = get_post_type($post_id);

  global $wpdb;
  $col = get_post_type() === 'tv' ? 'tv_series' : 'dramas';
  $tv = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_{$col} WHERE `ID` = %d", $post_id), ARRAY_A);
  $cast = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_{$col}_cast WHERE $col = %d", $post_id));
  $crew = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_{$col}_crew WHERE $col = %d", $post_id));
  $director = $wpdb->get_var($wpdb->prepare("SELECT person FROM {$wpdb->prefix}tmu_{$col}_crew WHERE $col = %d AND (job='Director' OR job LIKE '%Co-Director%' OR job LIKE '%Assistant Director%') ORDER BY CASE WHEN job='Director' THEN 1 ELSE 2 END LIMIT 1", $post_id));

  $tv['videos'] = isset($tv['videos']) && $tv['videos'] ? @unserialize($tv['videos']) : [];
  $tv['seasons'] = isset($tv['seasons']) && $tv['seasons'] ? @unserialize($tv['seasons']) : [];
  $tmdb_rating['average'] = isset($tv['average_rating']) ? $tv['average_rating'] : '';
  $tmdb_rating['count'] = isset($tv['vote_count']) ? $tv['vote_count'] : '';

  if ($post_type === 'tv' || $post_type === 'drama') {
    $table_col = $post_type === 'tv' ? 'tv_series' : 'dramas';
    $eps = $wpdb->get_results("SELECT ID,average_rating,vote_count FROM {$wpdb->prefix}tmu_{$table_col}_episodes WHERE $table_col = $post_id");
    if ($eps) {
        $ep_rating = $tmdb_rating['average']*$tmdb_rating['count'];
        foreach ($eps as $ep) {
            $ep_comments = get_comments(array('post_id' => $ep->ID, 'status' => 'approve'));
            foreach($ep_comments as $comment):
                $ep_rating += isset($comment->comment_rating) && $comment->comment_rating ? (int)$comment->comment_rating : 0;
                $tmdb_rating['count']++;
            endforeach;
            $ep_rating = $ep_rating+($ep->average_rating*$ep->vote_count);
            $tmdb_rating['count'] += $ep->vote_count;
        }
        $tmdb_rating['average'] = $tmdb_rating['count'] ? number_format(($ep_rating/$tmdb_rating['count']), 1) : 5;
    }
  }

  $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
  $average_ratings = get_average_ratings($comments, $tmdb_rating);

  $organization = isset($tv[ 'production_house' ]) ? $tv[ 'production_house' ] : '';
  $description = get_the_content();
  $release_date = isset($tv['release_date']) ? $tv['release_date'] : '';
  $keywords = wp_get_object_terms( $post_id, 'keyword', array('fields' => 'names') );
  $rating = $average_ratings;
  $reviews = $comments;
  $season = isset($tv['seasons'][0]) ? current_season($post_id, ($tv['last_season'] ? $tv['last_season'] : (isset($tv['seasons'][0]) ? rwmb_meta( 'season_no', '', $tv['seasons'][0] ) : '')), $tv['last_episode'], true) : '';
  $certification = isset($tv['certification']) ? $tv['certification'] : '';
  $trailer = $tv['videos'] ? $tv['videos'][count($tv['videos'])-1] : '';

  $genres_text = ''; $total = count($genres_array); $count = 1;
  $genres = htmlspecialchars_decode(implode(', ', array_map(function($genre) { return '"'.$genre.'"'; }, $genres_array)));
  foreach ($genres_array as $genre) { $genres_text .= $genre.($count !== $total ? ($count === $total-1 ? ' and ' : ', ') : ''); $count++; }

  return '<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": {
        "@type": "TVSeries",
        "url": "'.$permalink.'",
        "name": "'.$title.'",
        "image": "'.$img_url.'",
        "contentRating": "'.$certification.'",
        "genre": [
          '.$genres.'
        ],
        "actor": ['.implode(', ', array_map(function($cast) { return '
        {
          "@type": "Person",
          "url": "'.get_permalink($cast->person).'",
          "name": "'.get_the_title($cast->person).'"
        }'; }, $cast)).'

        ],'.($season ? '
        "containsSeason": [
        {
          "@type": "TVSeason",
          "datePublished": "'.$season['air_date'].'",
          "episode": {
            "@type": "TVEpisode",
            "episodeNumber": "'.$season['episode_no'].'",
            "name": "'.$season['episode_name'].'"
          },
          "name": "'.$season['season_name'].'",
          "numberOfEpisodes": "'.$season['total_episodes'].'"
        }
    ],' : '').'
    '.($director ? '
    "director": [ {
            "@type": "Person",
            "url": "'.get_permalink($director).'",
            "name": "'.get_the_title($director).'"
        } ],' : '').'
        '.($organization ? '      
        "creator": [
          {
            "@type": "Organization",
            "name": "'.stripslashes(htmlspecialchars_decode($organization)).'"
          }
        ],' : '').'
        
        "description": "'.wp_strip_all_tags($description).'",
        "datePublished": "'.$release_date.'",
        "keywords":"'.($keywords && is_array($keywords) ? htmlspecialchars_decode(implode(', ', $keywords)) : '').'",
        '.(isset($trailer) && $trailer ? '"trailer": {
          "@type": "VideoObject",
          "name": "'.get_the_title($trailer).'",
          "description": "Watch the trailer for '.get_the_title($post_id).', a '.$genres_text.' tv show.",
          "thumbnailUrl": "'.(has_post_thumbnail($trailer) ? get_the_post_thumbnail_url($trailer, 'full') : plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif').'",
          "uploadDate": "'.get_the_date( 'Y-m-d\TH:i:s', $trailer ).'+05:00",
          "embedUrl": "'.get_permalink($trailer).'",
          "duration": "PT2M30S"
        },' : '')
        .reviews_schema($reviews).'
        "aggregateRating": {
          "@type": "AggregateRating",
          "bestRating": "10",
          "worstRating": "1",
          "ratingValue": "'.$rating['average'].'",
          "ratingCount": "'.($rating['count'] ? $rating['count'] : 1).'"
        }
      }
    }
    </script>';
}

// https://www.youtube.com/embed/'.(rwmb_meta( 'video_data', '', $data['trailer'] )['source']).'

function schema_movie(){

  $post_id = get_the_ID();
  $permalink = get_permalink($post_id);
  $title = get_the_title($post_id);
  $img_url = get_the_post_thumbnail_url($post_id, 'full');
  $genres_array = wp_get_object_terms( $post_id, 'genre', array('fields' => 'names') );

  global $wpdb;
  $movie = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_movies WHERE `ID` = %d", $post_id), ARRAY_A);
  $cast = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_movies_cast WHERE movie = %d", $post_id));
  $crew = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_movies_crew WHERE movie = %d", $post_id));
  $director = $wpdb->get_var($wpdb->prepare("SELECT person FROM {$wpdb->prefix}tmu_movies_crew WHERE movie = %d AND (job='Director' OR job LIKE '%Co-Director%' OR job LIKE '%Assistant Director%') ORDER BY CASE WHEN job='Director' THEN 1 ELSE 2 END LIMIT 1", $post_id));

  $movie['videos'] = $movie['videos'] ? @unserialize($movie['videos']) : [];
  $tmdb_rating['average'] = $movie['average_rating'];
  $tmdb_rating['count'] = $movie['vote_count'];
  $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
  $average_ratings = get_average_ratings($comments, $tmdb_rating);

  $organization = $movie[ 'production_house' ];
  $description = get_the_content();
  $release_date = $movie['release_date'];
  $keywords = wp_get_object_terms( $post_id, 'keyword', array('fields' => 'names') );
  $rating = $average_ratings;
  $reviews = $comments;
  $certification = $movie['certification'];
  $trailer = $movie['videos'] ? $movie['videos'][count($movie['videos'])-1] : '';

  $genres_text = ''; $total = count($genres_array); $count = 1;
  $genres = htmlspecialchars_decode(implode(', ', array_map(function($genre) { return '"'.$genre.'"'; }, $genres_array)));
  foreach ($genres_array as $genre) { $genres_text .= $genre.($count !== $total ? ($count === $total-1 ? ' and ' : ', ') : ''); $count++; }
return '<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": {
        "@type": "Movie",
        "url": "'.$permalink.'",
        "name": "'.$title.'",
        "image": "'.$img_url.'",
        "contentRating": "'.$certification.'",
        "genre": [
          '.$genres.'
        ],
        "actor": ['.implode(', ', array_map(function($cast) { return isset($cast->person) ? '
        {
          "@type": "Person",
          "url": "'.get_permalink($cast->person).'",
          "name": "'.get_the_title($cast->person).'"
        }' : ''; }, $cast)).'

        ],
      '.($director ? '
      "director": [ {
              "@type": "Person",
              "url": "'.get_permalink($director).'",
              "name": "'.get_the_title($director).'"
          } ],' : '').'
          '.($organization ? '
          "creator": [
            {
              "@type": "Organization",
              "name": "'.stripslashes(htmlspecialchars_decode($organization)).'"
            }
          ],' : '').'
          
          "description": "'.wp_strip_all_tags($description).'",
          "datePublished": "'.$release_date.'",
          "keywords":"'.(isset($keywords) && is_array($keywords) ? htmlspecialchars_decode(implode(', ', $keywords)) : '').'",
          '.(isset($trailer) && $trailer ? '"trailer": {
            "@type": "VideoObject",
            "name": "'.get_the_title($trailer).'",
            "description": "Watch the trailer for '.get_the_title($post_id).', a '.$genres_text.' movie.",
            "thumbnailUrl": "'.(has_post_thumbnail($trailer) ? get_the_post_thumbnail_url($trailer, 'full') : plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif').'",
            "uploadDate": "'.get_the_date( 'Y-m-d\TH:i:s', $trailer ).'+05:00",
            "embedUrl": "'.get_permalink($trailer).'",
            "duration": "PT2M30S"
          },' : '')
          .reviews_schema($reviews).'
          "aggregateRating": {
            "@type": "AggregateRating",
            "bestRating": "10",
            "worstRating": "1",
            "ratingValue": "'.$rating['average'].'",
            "ratingCount": "'.($rating['count'] ? $rating['count'] : 1).'"
          }
        }
      }
    </script>';
}

function reviews_schema($reviews){
  $schema = '';
  if ($reviews) {
    $tempCount = 0; $countReviews = count($reviews);
    $schema .= '"review": [';
    foreach($reviews as $review):
      $username = $review->comment_author;
      if (!$username) $countReviews = $countReviews-1;
      if ($username) {
        $schema .= (++$tempCount === 1) ? '' : ', 
          {
            "@type": "Review",
            "datePublished": "'.$review->comment_date.'",
            "dateModified": "'.$review->comment_date.'",
            "author": {
              "@type": "Person",
              "name": "'.$username.'"
            },
            "reviewRating": {
              "@type": "Rating",
              "ratingValue": "'.$review->comment_rating.'",
              "worstRating": "1",
              "bestRating": "10"
            },
            "itemReviewed": {
              "@type": "CreativeWork",
              "url": "'.get_permalink($review->comment_post_id).'",
              "name": "'.$username.'"
            },
            "dateCreated": "'.$review->comment_date.'",
            "inLanguage": "English",
            "reviewBody": "'.$review->comment_content.'"
          }'; 
      }
      
    endforeach;
    $schema .= '],';
  }
  return $schema;
}

function schema_video(){
  global $wpdb;
  $video_id = get_the_ID();
  $video_title = get_the_title($video_id);
  $permalink = get_permalink($video_id);
  $parent_id = $wpdb->get_var($wpdb->prepare("SELECT `post_id` FROM {$wpdb->prefix}tmu_videos WHERE `ID` = '%d'", $video_id));
  $content = get_the_content('', false, $parent_id);
  $video_image = get_the_post_thumbnail_url($video_id);
  $parent_content = $wpdb->get_var("SELECT post_content FROM {$wpdb->prefix}posts WHERE `ID` = $parent_id");
  $parent_content = $parent_content ? stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('"', ' ', wp_trim_words(wp_strip_all_tags($parent_content), 60))))) : '';
  return '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "'.get_the_title($parent_id).' | '.$video_title.'",
  "description": "'.$parent_content.'",
  "thumbnail":{"@type":"ImageObject","contentUrl":"'.$video_image.'"},
  "thumbnailUrl": ["'.$video_image.'"],
  "uploadDate": "'.get_the_time('c', $video_id).'",
  "duration": "PT2M34S",
  "contentUrl": "'.$permalink.'",
  "embedUrl": "'.$permalink.'"
}
</script>';
}

function schema_person(){
  $post_id = get_the_ID();

  global $wpdb;
  $person = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_people WHERE `ID` = %d", $post_id), ARRAY_A);

  $person['name'] = get_the_title($post_id);
  $person['description'] = get_the_content(null, false, $post_id);
  $person['basic'] = $person['basic'] ? unserialize($person['basic']) : [];
  $person[ 'spouse' ] = isset($person['basic'][ 'spouse' ]) && $person['basic'][ 'spouse' ] ? $person['basic'][ 'spouse' ] : '';
  $person[ 'parents' ] = ['father' => (isset($person['basic'][ 'parents' ]['father']) && $person['basic'][ 'parents' ]['father'] ? $person['basic'][ 'parents' ]['father'] : ''), 'mother' => (isset($person['basic'][ 'parents' ]['mother']) && $person['basic'][ 'parents' ]['mother'] ? $person['basic'][ 'parents' ]['mother'] : '')];
  $person[ 'siblings' ] = isset($person['basic'][ 'siblings' ]) && $person['basic'][ 'siblings' ] ? $person['basic'][ 'siblings' ] : 'will update soon';
  $person['videos'] = unserialize($person['videos']);
  
  switch ($person['profession']) {
     case 'Acting':
       $person['profession'] = $person['gender'] === 'Male' ? 'Actor' : ''; 
       break;

    case 'Directing':
       $person['profession'] = 'Director';
       break;

    case 'Writing':
       $person['profession'] = 'Writer';
       break;
  }

  global $wpdb;
  $table_name = $wpdb->prefix.'tmu_seo_options';
  $description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE post_type = 'people'  AND section = 'single'");
  $description = $description ? replace_tags($description, 'single', 'people', $post_id) : '';
  if(!$description && $person['description']) {
    $matches = explode("\n", wp_strip_all_tags($person['description']));
    $description = isset($matches[0]) ? trim(str_replace('"', ' ', $matches[0])) : '';
  }

  $home_description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'homepage'");
  $home_description = $home_description ? replace_tags($home_description, 'archive', 'homepage') : '';

  $home_url = get_site_url();
  $logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
  $logo = isset($logo[0]) ? esc_url( $logo[0] ) : '';
  $sitename = get_bloginfo();

  $video_schema = [];
  if ($person['videos']) {
    foreach($person['videos'] as $video) {
      $video_image = has_post_thumbnail($video) ? get_the_post_thumbnail_url($video) : '';
      $video_description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE post_type = 'video'  AND section = 'single'");
      $video_description = $video_description ? replace_tags($video_description, 'single', 'video', $video) : '';
      $video_schema[] = '"video":{"@type":"VideoObject","name":"'.get_the_title($video).'","embedUrl":"'.get_permalink($video).'","thumbnail":{"@type":"ImageObject","contentUrl":"'.$video_image.'"},"thumbnailUrl":"'.$video_image.'","url":"'.get_permalink($video).'","description":"'.$video_description.'","duration":"PT2M20S","uploadDate":"'.get_the_time('c', $video).'"}';
    }
  }
  $video_schema = $video_schema ? implode(', ', $video_schema) : '';

return '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "name": "'.$person['name'].'",
  "description": "'.$description.'",
  "image": "'.get_the_post_thumbnail_url($post_id).'",
  "headline": "'.$person['name'].($person['profession'] ? ' - '.$person['profession'] : '').'",
  "author": {
    "@type": "Organization",
    "name": "'.$sitename.'",
    "url" :  "'.$home_url.'"
  },
 "publisher" : {
      "@type": "Organization",
      "@id": "'.$home_url.'",
      "name" : "'.$sitename.'",
      "description" : "'.$home_description.'"'.
      ($logo ? ',"logo": {
         "@type": "ImageObject",
         "url": "'.$logo.'",
         "caption":"'.$sitename.' Logo"
      }' : '').'
  },
  '.($video_schema ? $video_schema.',' : '').'
  "mainEntity": {
    "@context":"https://schema.org",
    "@type":"Person",
    "name": "'.$person['name'].'",
    "description": "'.$description.'",
    "knowsAbout": "'.$description.'",
    "url": "'.get_permalink($post_id).'",
    "image": "'.get_the_post_thumbnail_url($post_id).'",
    "jobTitle": "'.$person['profession'].'",
    "birthDate": "'.$person['date_of_birth'].'",
    "birthPlace": "'.$person['birthplace'].'",
    '.($person['spouse'] ? '"spouse": "'.get_the_title($person['spouse']).'",' : '').'
    "siblings": ['.string_to_quoted_list($person['siblings']).'],
    "parents": ['.(isset($person['parents']['father']) ? '"'.($person['parents']['father']).'"' : '').($person['parents']['mother'] ? (($person['parents']['father'] ? ', ':'').'"'.$person['parents']['mother']).'"' : '').']
  }
}
</script>

';
}

function schema_home(){
  global $wpdb;
  $table_name = $wpdb->prefix.'tmu_seo_options';
  $description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'homepage'");
  $description = $description ? replace_tags($description, 'archive', 'homepage') : '';

  $home_url = get_site_url();
  $logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
  $logo = isset($logo[0]) ? esc_url( $logo[0] ) : '';
  $sitename = get_bloginfo();

  $email = get_option( 'tmu_email' );

  return '<script type="application/ld+json">
    {
      "@context" : "https://schema.org",
      "@type" : "WebSite",
      "name" : "'.$sitename.'",
      "url" : "'.$home_url.'",
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "'.$home_url.'/?s={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      },
      "publisher" : {
        "@type": "Organization",
        "@id": "'.$home_url.'#website",
        "name" : "'.$sitename.'",
        "url": "'.$home_url.'",
        "description" : "'.$description.'",
        '.($logo ? '"logo": {
             "@type": "ImageObject",
             "url": "'.$logo.'",
             "caption":"'.$sitename.' Logo"
        },' : '').'
        "contactPoint": { "email": "'.($email ?? '').'" }
      }
    }
</script>';
}

function schema_episode(){
  global $wpdb;
  $post_id = get_the_ID();
  $post_type = get_post_type($post_id);
  $col = $post_type === 'episode' ? 'tv_series' : 'dramas';
  $table_name = $wpdb->prefix.'tmu_'.$col;
  $episode = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name}_episodes WHERE `ID` = %d", $post_id), ARRAY_A);
  $episode['tv_series'] = isset($episode['tv_series']) ? $episode['tv_series'] : $episode['dramas'];
  $genres = json_encode(wp_get_object_terms( $episode['tv_series'], 'genre', array('fields' => 'names') ));
  $episode['overview'] = $episode['overview'] ? stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('"', ' ', wp_trim_words(wp_strip_all_tags($episode['overview']), 60))))) : '';
  if(!$episode['overview']){
    $description = $wpdb->get_var("SELECT description FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = 'episode'  AND section = 'single'");
    $episode['overview'] = $description ? replace_tags($description, 'single', 'episode', $post_id) : '';
  }
  $episode['credits'] = $episode['credits'] ? @unserialize($episode['credits']) : ['cast' => [], 'crew' => [] ];
  $casts = episode_credit($episode['credits']['cast']);

  $episode['season_permalink'] = isset($episode['season_id']) ? get_permalink($episode['season_id']) : '';
  $tmdb_rating['average'] = $episode['average_rating'];
  $tmdb_rating['count'] = $episode['vote_count'];

  $episode['season_no'] = isset($episode['season_no']) ? $episode['season_no'] : 1;

  $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
  $average_ratings = get_average_ratings($comments, $tmdb_rating);

  $director_id = isset($episode['credits'][ 'crew' ]) ? get_credits_ids_by_profession($episode['credits'][ 'crew' ], 'directing', 'Director', 1) : '';
  $producer_id = isset($episode['credits'][ 'crew' ]) ? get_credits_ids_by_profession($episode['credits']['crew'], 'production', 'Producer', 1) : '';

  $director_id = $director_id ? (is_array($director_id) ? $director_id[0] : $director_id) : $wpdb->get_var($wpdb->prepare("SELECT person FROM {$table_name}_crew WHERE $col = %d AND job='Director'", $episode['tv_series']));
  $producer_id = $producer_id ? (is_array($producer_id) ? $producer_id[0] : $producer_id) : $wpdb->get_var($wpdb->prepare("SELECT person FROM {$table_name}_crew WHERE $col = %d AND job='Producer'", $episode['tv_series']));

  return '<script type="application/ld+json">{"@context": "https://schema.org", "@type": "TVEpisode", "name": "'.$episode['episode_title'].'", "description": "'.$episode['overview'].'",
  "url":"'.get_permalink($post_id).'", "dateCreated":"'.$episode['air_date'].'", '.(has_post_thumbnail($post_id) ? '"image": "'.get_the_post_thumbnail_url($post_id, 'full').'",' : '').($genres ? '"genre": '.$genres.',' : '').($director_id ? '"director": [ {"@type": "Person","url": "'.get_permalink($director_id).'", "name": "'.get_the_title($director_id).'"'.(has_post_thumbnail($director_id) ? ',"image":"'.get_the_post_thumbnail_url($director_id, 'full').'"' : '').'} ],' : '').($producer_id ? '"producer": [ {"@type": "Person","url": "'.get_permalink($producer_id).'","name": "'.get_the_title($producer_id).'"'.(has_post_thumbnail($producer_id) ? ',"image":"'.get_the_post_thumbnail_url($producer_id, 'full').'"' : '').'} ],' : '').'"actor":['.$casts['schema'].'],"partOfSeason":{"@type":"TVSeason","name":"Season '.$episode['season_no'].'","seasonNumber":"'.$episode['season_no'].'","url":"'.$episode['season_permalink'].'"},"partOfSeries":{"@type":"TVSeries","name":"'.get_the_title($episode['tv_series']).'","startDate":"'.rwmb_meta( 'release_date', '', $episode['tv_series'] ).'","url":"'.get_permalink($episode['tv_series']).'"},"aggregateRating":{"@type":"AggregateRating","bestRating":"10","ratingCount":'.($average_ratings['count'] ? $average_ratings['count'] : 1).',"ratingValue":"'.$average_ratings['average'].'","worstRating":"0"}}</script>';
}

function article_schema(){
  $post_id = get_the_ID();
  $content = get_the_content(null, false, $post_id);
  $attachment_id = has_post_thumbnail($post_id) ? get_post_thumbnail_id( $post_id ) : '';
  if ($attachment_id) {
    $attachment = get_post( $attachment_id );
    $caption = $attachment->post_excerpt;
    $description = $attachment->post_content;
    $image_url = $attachment->guid;
    $image_attributes = wp_get_attachment_image_src( $attachment_id, 'full' );

  }

  // $author_id = get_the_author_meta( 'ID' );
  // $author_name = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';
  // $author_url = $author_id ? get_author_posts_url( $author_id ) : '';
  // $autho_img = $author_id ? get_avatar_url($author_id, ['size' => '40']) : '';

  // $social_meta = get_user_meta($author_id);

  // $author_social = []; $author_social_links = ''; $additional_social = [];

  // if (isset($social_meta["twitter"][0])) {
  //   if ($social_meta["twitter"][0]) $author_social[] = $social_meta["twitter"][0];
  // }
  // if (isset($social_meta["facebook"][0])) {
  //   if ($social_meta["facebook"][0]) $author_social[] = $social_meta["facebook"][0];
  // }
  // if (isset($social_meta["additional_profile_urls"][0])) {
  //   if ($social_meta["additional_profile_urls"][0]) $additional_social = explode(' ', $social_meta["additional_profile_urls"][0]);
  //   $author_social = array_merge($author_social,$additional_social);
  // }

  // if ($author_social) {
  //   $temp_links_array = array_map(function($link){ return '"'.$link.'"';  }, $author_social);
  //   $author_social_links = implode(',', $temp_links_array);
  // }

  $categories = get_the_category($post_id);
  if ($categories) {
    $cat_description = category_description($categories[0]->term_id);
    $categories = array_map(function($category){ return '"'.$category->name.'"'; }, $categories);
    $categories = implode(',', $categories);
  }

  $home_url = get_site_url();
  $logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
  $logo = isset($logo[0]) ? esc_url( $logo[0] ) : '';
  $sitename = get_bloginfo();

  global $wpdb;
  $table_name = $wpdb->prefix.'tmu_seo_options';
  $description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'homepage'");
  $description = $description ? replace_tags($description, 'archive', 'homepage') : '';

  $article_type = get_post_meta($post_id, 'article_type', true);
  $post_title = get_the_title();
  $content = $article_type === 'NewsArticle' ? stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('"', ' ', wp_strip_all_tags($content))))) : stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('"', ' ', wp_trim_words(wp_strip_all_tags($content), 60)))));

  return '<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "'.($article_type === 'NewsArticle' ? 'NewsArticle' : 'Article').'",
        "mainEntityOfPage": {
          "@type": "WebPage",
          "@id": "'.get_permalink().'"
          '.($article_type === 'Article' ? ',"name": "'.$post_title.'"' : '').'
        },
        '.($article_type === 'NewsArticle' ? '"headline": "'.$post_title.'",' : '').'
        "'.($article_type === 'NewsArticle' ? 'articleBody' : 'description').'": "'.$content.'",
        '.($attachment_id ? '"image":{
            "@type": "ImageObject",
            "url": "'.$image_url.'",
            "contentUrl": "'.$image_url.'",
            "creditText": "'.$caption.'",
            "description": "'.$description.'",
            "height": "'.$image_attributes[2].'",
            "width": "'.$image_attributes[1].'"
        },' : '').'
        "datePublished": "'.get_the_time('c').'",
        "dateModified": "'.get_the_modified_time('c').'",
        "author": {
          "@type": "Organization",
          "name": "'.$sitename.'",
          "url" :  "'.$home_url.'"
        },
       "publisher" : {
          "@type": "Organization",
          "@id": "'.$home_url.'",
          "name" : "'.$sitename.'",
          "description" : "'.$description.'",
          '.($logo ? '"logo": {
             "@type": "ImageObject",
             "url": "'.$logo.'",
             "caption":"'.$sitename.' Logo"
          }' : '').'
        },
        "articleSection":['.(isset($categories) ? $categories : '').']
  }
</script>';
}

function string_to_quoted_list($text) {
  // Split the string based on commas or whitespace (considering extra spaces).
  $quoted_elements = preg_split('/(?<!\w)\s+|,/s', $text);
  return substr(implode(' ', array_map(function($elem) {
      return $elem ? '"' . trim($elem) . '",' : '';
    }, $quoted_elements)), 0, -1);
}

function schema_season(){
  global $wpdb;
  $season_id = get_the_ID();
  $name = get_the_title($season_id);
  $permalink = get_permalink($season_id);
  $season = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}tmu_tv_series_seasons WHERE ID = $season_id");
  $release_date = $season->air_date;
  $season_no_string = $season->season_no;
  $season_no = ($season_no_string === '0') ? 0 : (is_numeric($season_no_string) ? intval($season_no_string) : false);
  $poster_url= has_post_thumbnail($season_id) ? get_the_post_thumbnail_url($season_id) : '';
  $series_id = $season->tv_series;
  $genres = json_encode(wp_get_object_terms( $series_id, 'genre', array('fields' => 'names') ));
  $series_title = get_the_title($series_id);
  $series = $wpdb->get_row("SELECT star_cast,release_date FROM {$wpdb->prefix}tmu_tv_series WHERE ID=$series_id");
  $casts = $series->star_cast ? unserialize($series->star_cast) : '';

  $director = $wpdb->get_var($wpdb->prepare("SELECT person FROM {$wpdb->prefix}tmu_tv_series_crew WHERE tv_series = %d AND job='Director'", $series_id));
  $producer = $wpdb->get_var($wpdb->prepare("SELECT person FROM {$wpdb->prefix}tmu_tv_series_crew WHERE tv_series = %d AND job='Producer'", $series_id));

  $episodes = $wpdb->get_results("SELECT ID,episode_title,air_date,runtime,overview,episode_no,average_rating,vote_count FROM {$wpdb->prefix}tmu_tv_series_episodes WHERE `season_no`=$season_no AND `tv_series`=$series_id ORDER BY air_date");

  $casts_schema = $casts ? implode(',', array_map(function ($cast) { return '{"@type":"Person","name":"'.get_the_title($cast['person']).'","sameAs":"'.get_permalink($cast['person']).'"'.(has_post_thumbnail($cast['person']) ? ',"image":"'.get_the_post_thumbnail_url($cast['person'], 'full').'"' : '').'}'; }, $casts)) : '';
  $episodes_schema = $episodes ? implode(',', array_map(fn($episode) => '{"@type":"TVEpisode","episodeNumber":"'.$episode->episode_no.'","url":"'.get_permalink($episode->ID).'","name":"'.$episode->episode_title.'","description":"'.$episode->overview.'","dateCreated":"'.$episode->air_date.'"}', $episodes)) : '';

  $schema = default_schema($name, $_SERVER['REQUEST_URI']);

  $schema .= '<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "TVSeason",
    "name": "'.$name.'",
    "url":"'.$permalink.'",
    "dateCreated":"'.$release_date.'",
    '.($poster_url ? '"image": "'.$poster_url.'",' : '').'
    '.($genres ? '"genre": '.$genres.',' : '').'
    "partOfSeries":{"@type":"TVSeries","name":"'.$series_title.'","startDate":"'.$series->release_date.'","url":"'.get_permalink($series_id).'"},
    '.($casts_schema ? '"actor":[
        '.$casts_schema.'
      ],' : '').'
    '.($director ? '"director": [ {
        "@type": "Person",
        "url": "'.get_permalink($director).'",
        "name": "'.get_the_title($director).'"
        '.(has_post_thumbnail($director) ? ',"image":"'.get_the_post_thumbnail_url($director, 'full').'"' : '').'
      } ],' : '').'
    '.($producer ? '"producer": [ {
        "@type": "Person",
        "url": "'.get_permalink($producer).'",
        "name": "'.get_the_title($producer).'"
        '.(has_post_thumbnail($producer) ? ',"image":"'.get_the_post_thumbnail_url($producer, 'full').'"' : '').'
      } ],' : '').'
    "episode":['.$episodes_schema.']
  };
  </script>';
}

function default_schema($title, $permalink, $post_type=NULL, $tax=false){
  global $wpdb;
  $table_name = $wpdb->prefix.'tmu_seo_options';
  $home_description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'homepage'");
  $home_description = $home_description ? replace_tags($home_description, 'archive', 'homepage') : '';

  $post_type = $post_type ?? get_post_type();
  $sec = $tax ? 'taxonomy' : 'archive';
  $seo = $wpdb->get_row("SELECT title,description FROM $table_name WHERE post_type = '{$post_type}'  AND section = '{$sec}'");
  $seo_title = isset($seo->title) ? replace_tags($seo->title, 'archive', $post_type) : '';
  $seo_description = isset($seo->description) ? replace_tags($seo->description, 'archive', $post_type) : '';

  $home_url = get_site_url();
  $logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
  $logo = isset($logo[0]) ? esc_url( $logo[0] ) : '';
  $sitename = get_bloginfo();

  $email = get_option( 'tmu_email' );

  $data = '<script type="application/ld+json">';
    $data .= '{';
      $data .= '"@context" : "https://schema.org",';
      $data .= '"@type" : "WebSite",';
      $data .= '"name" : "'.$seo_title.'",';
      $data .= '"description" : "'.$seo_description.'",';
      $data .= '"url" : "'.$permalink.'",';
      $data .= '"potentialAction": {';
        $data .= '"@type": "SearchAction",';
        $data .= '"target": { "@type": "EntryPoint", "urlTemplate": "'.$home_url.'/?s={search_term_string}" },';
        $data .= '"query-input": "required name=search_term_string"';
      $data .= '},';
      $data .= '"publisher" : {';
        $data .= '"@type": "Organization",';
        $data .= '"@id": "'.$home_url.'#website",';
        $data .= '"name" : "'.$sitename.'",';
        $data .= '"url": "'.$home_url.'",';
        $data .= '"description" : "'.$home_description.'",';
        $data .= $logo ? '"logo": { "@type": "ImageObject", "url": "'.$logo.'", "caption":"'.$sitename.' Logo" },' : '';
        $data .= '"contactPoint": { "email": "'.($email ?? '').'" }';
      $data .= '}';
    $data .= '}';
  $data .= '</script>';

  return $data;
}