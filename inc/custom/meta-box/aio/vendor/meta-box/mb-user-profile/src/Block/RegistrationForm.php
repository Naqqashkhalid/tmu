<?php
namespace  MetaBox\UserProfile\Block;

use  MetaBox\UserProfile\Helper;
use  MetaBox\UserProfile\Forms\Factory;

class RegistrationForm {
	public function __construct() {
		add_action( 'init', [ $this, 'register_block' ], 99 );
	}

	public function register_block() {
		register_block_type( MBUP_DIR . '/block/registration-form/build', [
			'render_callback' => [ $this, 'render_block' ],
		] );
	}

	public function render_block( $attributes ): string {
		$form = Factory::make( [
			'id'                 => $attributes['meta_box_id'],
			'redirect'           => $attributes['redirect'],
			'form_id'            => $attributes['form_id'],
			'recaptcha_key'      => $attributes['recaptcha_key'],
			'recaptcha_secret'   => $attributes['recaptcha_secret'],
			'label_title'        => $attributes['label_title'],
			'label_username'     => $attributes['label_username'],
			'label_email'        => $attributes['label_email'],
			'label_password'     => $attributes['label_password'],
			'label_password2'    => $attributes['label_password2'],
			'label_submit'       => $attributes['label_submit'],
			'id_username'        => $attributes['id_username'],
			'id_email'           => $attributes['id_email'],
			'id_password'        => $attributes['id_password'],
			'id_password2'       => $attributes['id_password2'],
			'id_submit'          => $attributes['id_submit'],
			'confirmation'       => $attributes['confirmation'],
			'email_confirmation' => Helper::convert_boolean( $attributes['email_confirmation'] ),
			'password_strength'  => $attributes['password_strength'],
			'email_as_username'  => Helper::convert_boolean( $attributes['email_as_username'] ),
			'show_if_user_can'   => $attributes['show_if_user_can'],
			'role'               => $attributes['role'],
			'append_role'        => Helper::convert_boolean( $attributes['append_role'] ),
		], 'register' );
		if ( empty( $form ) ) {
			return '';
		}
		wp_enqueue_style( 'mbup', MBUP_URL . 'assets/user-profile.css', [], MBUP_VER );
		wp_enqueue_style( 'rwmb-password', MBUP_URL . 'assets/password.css', [], MBUP_VER );
		wp_enqueue_script( 'rwmb-password', MBUP_URL . 'assets/password.js', [ 'jquery' ], MBUP_VER, true );

		ob_start();
		$form->render();

		return ob_get_clean();
	}
}
