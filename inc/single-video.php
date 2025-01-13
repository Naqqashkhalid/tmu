<?php

add_filter( 'the_content', 'single_video' );
function single_video($content){
	if ( !(get_post_type() == 'video' && is_singular()) ) return $content;
	$video_id = get_the_ID();
	$video_title = get_the_title($video_id);
	$video_perma = get_permalink($video_id);
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_videos';
	$result = $wpdb->get_var($wpdb->prepare("SELECT `post_id` FROM $table_name WHERE `ID` = '%d'", $video_id));
	if ($result) {
		$movie = get_post($result);
		$movie_permalink = get_permalink($result);
	}
	$main_title = get_the_title($result);
    var_dump("abc");
	
	return video_style().'<div class="video_details">
		<a href="'.$movie_permalink.'" class="movie_poster" title="'.$main_title.'">'.(has_post_thumbnail($result) ? get_the_post_thumbnail( $result, 'medium' ) : '').'</a>
		<div class="details_section">
			<h1 class="movie_title"><a href="'.$movie_permalink.'" title="'.$main_title.'">'.$main_title.'</a></h1>
			<h2 class="video_title">'.$video_title.'</h2>
			<div class="social-sharing-icons">
				<a class="social-icon facebook" href="https://www.facebook.com/sharer/sharer.php?u='.$video_perma.'" rel="nofollow" title="Share on facebook">
					<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
						<g transform="translate(-16.64,-16.64) scale(1.13,1.13)"><g fill="#0073c2" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M42,3h-34c-2.8,0 -5,2.2 -5,5v34c0,2.8 2.2,5 5,5h34c2.8,0 5,-2.2 5,-5v-34c0,-2.8 -2.2,-5 -5,-5zM37,19h-2c-2.1,0 -3,0.5 -3,2v3h5l-1,5h-4v16h-5v-16h-4v-5h4v-3c0,-4 2,-7 6,-7c2.9,0 4,1 4,1z"></path></g></g></g>
					</svg>
				</a>
				<a class="social-icon twitter" href="https://twitter.com/intent/tweet?text='.$video_title.'&url='.$video_perma.'" rel="nofollow" title="Share on twitter">
					<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
						<g transform="translate(-24.32,-24.32) scale(1.19,1.19)"><g fill="#000000" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M11,4c-3.866,0 -7,3.134 -7,7v28c0,3.866 3.134,7 7,7h28c3.866,0 7,-3.134 7,-7v-28c0,-3.866 -3.134,-7 -7,-7zM13.08594,13h7.9375l5.63672,8.00977l6.83984,-8.00977h2.5l-8.21094,9.61328l10.125,14.38672h-7.93555l-6.54102,-9.29297l-7.9375,9.29297h-2.5l9.30859,-10.89648zM16.91406,15l14.10742,20h3.06445l-14.10742,-20z"></path></g></g></g>
					</svg>
				</a>
				<a class="social-icon whatsapp" href="https://api.whatsapp.com/send?text='.$video_title.': '.$video_perma.'" rel="nofollow" title="Share on whatsapp">
					<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
						<g transform="translate(-29.44,-29.44) scale(1.23,1.23)"><g fill="#45d354" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(8.53333,8.53333)"><path d="M15,3c-6.627,0 -12,5.373 -12,12c0,2.25121 0.63234,4.35007 1.71094,6.15039l-1.60352,5.84961l5.97461,-1.56836c1.74732,0.99342 3.76446,1.56836 5.91797,1.56836c6.627,0 12,-5.373 12,-12c0,-6.627 -5.373,-12 -12,-12zM10.89258,9.40234c0.195,0 0.39536,-0.00119 0.56836,0.00781c0.214,0.005 0.44692,0.02067 0.66992,0.51367c0.265,0.586 0.84202,2.05608 0.91602,2.20508c0.074,0.149 0.12644,0.32453 0.02344,0.51953c-0.098,0.2 -0.14897,0.32105 -0.29297,0.49805c-0.149,0.172 -0.31227,0.38563 -0.44727,0.51563c-0.149,0.149 -0.30286,0.31238 -0.13086,0.60938c0.172,0.297 0.76934,1.27064 1.65234,2.05664c1.135,1.014 2.09263,1.32561 2.39063,1.47461c0.298,0.149 0.47058,0.12578 0.64258,-0.07422c0.177,-0.195 0.74336,-0.86411 0.94336,-1.16211c0.195,-0.298 0.39406,-0.24644 0.66406,-0.14844c0.274,0.098 1.7352,0.8178 2.0332,0.9668c0.298,0.149 0.49336,0.22275 0.56836,0.34375c0.077,0.125 0.07708,0.72006 -0.16992,1.41406c-0.247,0.693 -1.45991,1.36316 -2.00391,1.41016c-0.549,0.051 -1.06136,0.24677 -3.56836,-0.74023c-3.024,-1.191 -4.93108,-4.28828 -5.08008,-4.48828c-0.149,-0.195 -1.21094,-1.61031 -1.21094,-3.07031c0,-1.465 0.76811,-2.18247 1.03711,-2.48047c0.274,-0.298 0.59492,-0.37109 0.79492,-0.37109z"></path></g></g></g>
					</svg>
				</a>
				<a class="social-icon telegram" href="https://t.me/share/url?url='.$video_perma.'&text='.$video_title.'&to=" rel="nofollow" title="Share on telegram">
					<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
						<g transform="translate(-10.24,-10.24) scale(1.08,1.08)"><g fill="#02c8f0" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M25,2c12.703,0 23,10.297 23,23c0,12.703 -10.297,23 -23,23c-12.703,0 -23,-10.297 -23,-23c0,-12.703 10.297,-23 23,-23zM32.934,34.375c0.423,-1.298 2.405,-14.234 2.65,-16.783c0.074,-0.772 -0.17,-1.285 -0.648,-1.514c-0.578,-0.278 -1.434,-0.139 -2.427,0.219c-1.362,0.491 -18.774,7.884 -19.78,8.312c-0.954,0.405 -1.856,0.847 -1.856,1.487c0,0.45 0.267,0.703 1.003,0.966c0.766,0.273 2.695,0.858 3.834,1.172c1.097,0.303 2.346,0.04 3.046,-0.395c0.742,-0.461 9.305,-6.191 9.92,-6.693c0.614,-0.502 1.104,0.141 0.602,0.644c-0.502,0.502 -6.38,6.207 -7.155,6.997c-0.941,0.959 -0.273,1.953 0.358,2.351c0.721,0.454 5.906,3.932 6.687,4.49c0.781,0.558 1.573,0.811 2.298,0.811c0.725,0 1.107,-0.955 1.468,-2.064z"></path></g></g></g>
					</svg>
				</a>
			</div>
			'.$movie->post_content.'
		</div>
	</div>
	<div class="mobile-related">'.related_videos($video_id, $result).'</div>

';
}

function related_videos($video_id, $post_id) {
// 	$video_title = get_the_title($video_id);
// 	$video_perma = get_permalink($video_id);
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_videos';
	$related_videos = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `post_id` = '%d' AND `ID` != '%d' LIMIT 3", $post_id, $video_id));

	$data = '';
	if ($related_videos):
		$data .= '<h3 class="related-videos-title short-heading font-size-36 weight-700">Related Videos</h3>';
		$data .= '<div class="related-videos">';
		foreach($related_videos as $video):
			$title = get_the_title($video->ID);
			$video_meta = unserialize($video->video_data);
			$featured_image = has_post_thumbnail($video->ID) ? get_the_post_thumbnail_url($video->ID) : 'https://i.ytimg.com/vi/'.extractYouTubeId($video_meta['source']).'/maxresdefault.jpg';
			$data .= '<a href="'.get_permalink($video->ID).'" class="related-video">
					<div class="video-image video-single">
						<img src="'.$featured_image.'" alt="'.$title.'">
						<span class="play-icon absolute bottom-0 left-0 z-30 flex items-center gap-x-6 p-10 text-white">
							<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="PlayCircleOutlinedIcon" class="svg-icon icon-md" height="1em" width="1em">
								<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2.5-3.5 7-4.5-7-4.5v9z"></path>
							</svg>
							<span class="capitalize">'.$video_meta['content_type'].'</span>
						</span>
						<div class="blend"></div>
					</div>
					<h3>'.$title.'</h3>
			</a>';
		endforeach;
		$data .= '</div>';
	endif;
	return $data;
}

function extractYouTubeId($url) {
  if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
    return $match[1];
  }
  return $url;
}

function video_style(){
	return '<style>
.site-main, .entry-content { margin:0!important }
.site-main {display:flex;flex-wrap:wrap;}
.content-wrapper {width: 70%}
.video_sidebar {
	width: 30%;
	background: white;
}
.video_details {
	display: flex;
	padding-right: 0.375rem;
  gap: 15px;
  margin-top: 20px
}
.movie_poster {
	position: relative;
	display: block;
	width: 9.5rem;
	aspect-ratio: 471 / 707;
	flex-shrink: 0;
	height: 228px;
}
.movie_poster img {
	object-fit: cover;
	width: 100%;
	height: 100%;
	border-radius: 0.25rem;
}
.movie_title a {
	display: block;
	font-size: 24px;
	font-weight: 600;
	line-height: 1.3;
	margin-bottom: 0.6rem;
	text-decoration: underline!important;
}
.video_title {
	font-size: 18px;
    font-weight: 500;
	line-height: 1.3;
	margin-bottom: 0.5rem;
}
.social-sharing-icons {
	display: flex;
	gap: 10px;
	margin: 20px 0;
	padding: 10px 0;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
}
.social-icon {
	display: block;
	height: 28px;
    width: 28px;
}

.social-icon svg {
	transition: height 0.3s ease-out, width 0.3s ease-out;
}

.social-icon svg:hover {
	height: 26px;
	width: 26px;
}

.video_sidebar {
	padding: 20px;
}

.related-videos-title {
	padding-left: 15px;
    padding-bottom: 10px;
}

.related-video h3 {
    margin: 7px 0 15px;
    font-weight: 600;
	text-decoration: underline;
}

.related-video .video-image { border-radius: 10px; }

@media screen and (max-width: 1023.9px) {
	.movie_poster { display: none; }
	.content-wrapper, .video_sidebar {width: 100%}
}

@media screen and (min-width: 768px) {
	.mobile-related { display: none }
}

@media screen and (max-width: 768px) {
	.video_sidebar { display: none }
}
</style>';
}