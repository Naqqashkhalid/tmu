<?php

function person_data($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/person/'.$tmid, auth_key());
		return json_decode($response->getBody());
	} catch(Exception $e) {
	  return false;
	}
}

function person_social_accounts($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/person/'.$tmid.'/external_ids', auth_key());
		$data = json_decode($response->getBody());
		$social_accounts = $data ?? [];
		$socials = [];
		
		if($social_accounts && $social_accounts->facebook_id) $socials[] = ["platform" => 'Facebook', "url" => 'https://www.facebook.com/'.$social_accounts->facebook_id];
		if($social_accounts && $social_accounts->instagram_id) $socials[] = ["platform" => 'Instagram', "url" => 'https://instagram.com/'.$social_accounts->instagram_id];
		if($social_accounts && $social_accounts->twitter_id) $socials[] = ["platform" => 'X', "url" => 'https://x.com/missnemmanuel'.$social_accounts->twitter_id];
		if($social_accounts && $social_accounts->youtube_id) $socials[] = ["platform" => 'YouTube', "url" => 'https://www.facebook.com/'.$social_accounts->youtube_id];
		
		return serialize($socials);
	} catch(Exception $e) {
	  return false;
	}
}