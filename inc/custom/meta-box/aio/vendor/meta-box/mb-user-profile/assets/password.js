( function ( $ ) {
	'use strict';

	function toggle() {
		const $this = $( this ),
			$input = $( this ).parent().siblings( 'input' );
		$input.attr( 'type', $input.attr( 'type' ) === 'password' ? 'text' : 'password' );
		$input.attr( 'type' ) === 'password' ?
			$this.removeClass( 'hide-icon' ).addClass( 'show-icon' ) :
			$this.removeClass( 'show-icon' ).addClass( 'hide-icon' );
	}

	$( document ).on( 'click', '.password-icon', toggle );
} )( jQuery );