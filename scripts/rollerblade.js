jQuery( function( $ ) {
	
	$.feedback( {
		postBrowserInfo: true,
		postHTML: false,
		postURL: false,
		highlightElement: false,
        ajaxURL: window.rollerblade_ajax_url,
        ajaxNonce: window.rollerblade_nonce,
        feedbackButton: '#rollerblade-button',
        tpl: {
			description:	'<div id="feedback-welcome"><p>Feedback lets you send us suggestions about our products. We welcome problem reports, feature ideas and general comments.</p><p>Start by writing a brief description:</p><p>Next we\'ll let you identify areas of the page related to your description.</p><button id="feedback-welcome-next" class="feedback-next-btn feedback-btn-gray">Next</button><div id="feedback-welcome-error">Please enter your description</div><div class="feedback-box-top"><p>····</p></div><div class="feedback-wizard-close"></div></div>',
			overview:		'<div id="feedback-overview"><div id="feedback-overview-description"><div id="feedback-overview-description-text"><div id="feedback-overview-error">Please enter your description</div><h3 class="feedback-additional">Additional info</h3><div id="feedback-additional-none"><span>None</span></div><div id="feedback-browser-info"><span>Browser Info</span></div><div id="feedback-page-info"><span>Page Info</span></div><div id="feedback-page-structure"><span>Page Structure</span></div></div></div><div id="feedback-overview-screenshot"><h3>Screenshot</h3></div><div class="feedback-buttons"><button id="feedback-overview-back" class="feedback-back-btn feedback-btn-gray">cancel</button><button id="feedback-submit" class="feedback-submit-btn feedback-btn-blue">submit</button></div><div class="feedback-box-top"><p></p></div><div class="feedback-wizard-close"></div></div>',
			submitSuccess:	'<div id="feedback-submit-success"><div id="success-rb-close-icon"></div><div id="success-rb-icon"></div><div id="rb-success-message">Your comment was<br />submitted as Ticket<div id="rb-ticket-id"></div></div><a href="#" id="rb-success-ticket-link" target="_blank">View on Rollerblade</a></div>',
			submitError:	'<div id="feedback-submit-error"><p>An error occured while sending your feedback. Please, use the beacon at the bottom right corner of the screen to send us an error report or search our docs.</p><button class="feedback-close-btn feedback-btn-blue">OK</button><div class="feedback-box-top"><p></p></div><div class="feedback-wizard-close"></div></div>'
		},
		onClose: function() {
			
			//hide mouse tip as well
			$( '#mouse-tip' ).hide();
			
			$( 'body' ).removeClass( 'rollerblade-active' );
			
		}
    } );
	
	$( '#rollerblade-button-wrapper' ).draggable( {		//make rollerblade button draggable
		handle: '#rb-button-drag-area',
		cursor: 'pointer'
	} );
	
	//make rollerblade button and comment box draggable without breaking the click event
	$( '#rb-button-drag-area' ).click( function( event ) {
		
		event.stopPropagation();
		
	} );
	
	//check if user is signed in or no
	var rb_panel_width = 400,
		rb_panel_height = 255;
	
	if ( $( window ).width() < 500 ) {	//for mobile panel is a bit more narrow
			
		rb_panel_width = 300;
			
	}
	
	//check if user is logged in already
	window.rb_api_token = rb_get_cookie( 'RB_api_token' );
	
	window.rb_remote_user_display_name = rb_get_cookie( 'RB_remote_user_display_name' );
	
	$( '#rb-remote-panel-button' ).click( function( event ) {		//click opens/closes remote panel
		
		event.preventDefault();
		
		if ( $( '#rollerblade-button-wrapper' ).hasClass( 'stay-visible' ) ) {		//if panel is visible, hide it
			
			rb_hide_remote_panel();
			
		} else {		//if panel is hidden, show it
			
			if ( ! window.rb_api_token.length ) {	//if user is not logged in, show log in form
				
				rb_show_non_logged_in_panel();
				
			} else {		//show user display name if user is logged in
				
				rb_show_logged_in_panel();
				
			}
			
		}
		
	} );
	
	$( '.rb-log-in-field-wrapper input' ).click( function( event ) {	//remove placeholders and red border if user tries to re-enter username/pass
		
		event.preventDefault;
		
		$( '.rb-log-in-field-wrapper input' ).css( { borderColor: 'rgba(0, 0, 0, 0.1)' } ).attr( 'placeholder', '' );
		
	} ).keydown( function( event ) {
		
		if ( event.which == 13 ) {		//if enter is pressed, simulate clicking on "Log In" button
			
			$( '#rb-remote-log-in' ).click();
			
		} else if ( ( event.which == 9 ) && ( $( this ).attr( 'id' ) === 'rb-remote-user-pass' ) ) {		//if tab is pressed on password field, move focus to "Log In" button
			
			event.preventDefault();
			
			$( '#rb-remote-log-in' ).focus();
			
		}
		
	} );
	
	$( '#rb-remote-log-in' ).click( function( event ) {	//send remote sign-in request
		
		event.preventDefault();
		
		var request_data = {},
			rb_login_fields_validation = true;
		
		request_data.user_login = $( '#rb-remote-user-login' ).val();
		
		request_data.pass = $( '#rb-remote-user-pass' ).val();
		
		if ( ! request_data.user_login.length ) {		//if one of the fields is empty, show a warning
			
			$( '#rb-remote-user-login' ).css( { borderColor: 'red' } ).attr( 'placeholder', 'This field can\'t be empty!' );
			
			rb_login_fields_validation = false;
			
		}
		
		if ( ! request_data.pass.length ) {
			
			$( '#rb-remote-user-pass' ).css( { borderColor: 'red' } ).attr( 'placeholder', 'This field can\'t be empty!' );
			
			rb_login_fields_validation = false;
			
		}
		
		if ( ! rb_login_fields_validation ) {
			
			return;
			
		}
		
		$( 'body' ).append( '<div id="rb-loading-overlay"></div><div id="rb-loading">Signing you in...</div>' );
		
		$( '#rb-loading' ).css( { left: window.innerWidth/2 - 148 + 'px', top: window.innerHeight/2 - 54 + 'px' } );
		
		$.ajax( {
			
			url: rollerblade_ajax_url,
			
			dataType: 'json',
			
			type: 'POST',
			
			data: {
				action: 'send_rb_request',
				security: rollerblade_nonce,
				request_data: request_data,
				request_type: 'user_signin'
			},
			
			success: function( response ) {
				
				//remove "loading" spinner
				$( '#rb-loading, #rb-loading-overlay' ).remove();
				
				if ( response && response.status == 'ok' ) {
					
					document.cookie = 'RB_api_token=' + response.api_token + '; path=/';
					
					document.cookie = 'RB_remote_user_display_name=' + response.user_display_name + '; path=/';
					
					window.rb_api_token = response.api_token;
					
					window.rb_remote_user_display_name = response.user_display_name;
					
					//clear input fields and error messages
					$( '#rb-remote-user-login, #rb-remote-user-pass' ).val( '' );
					
					$( '#rb-remote-log-in-error-message' ).html( '' ).css( { display: 'none' } );
					
					rb_show_logged_in_panel();
					
					//automatically close remote panel in 3 seconds
					setTimeout( function() {
						
						jQuery( '#rb-remote-panel-button' ).click();
						
					}, 3000 );
					
					console.log( response );
					
				} else if ( response && response.user_message ) {		//some error detected, send details to helpscout and show user message
					
					//fill in and show error message block with animation
					$( '#rb-remote-log-in-error-message' ).html( response.user_message ).css( { display: 'block' } );
					
					rb_animate_log_in_error_message();
					
					console.log( response );
					
				} else {		//AJAX call returned null
					
					$( '#rb-remote-log-in-error-message' ).html( 'Oops! An AJAX call has failed, which usually indicates an issue with this site.' ).css( { display: 'block' } );
					
					rb_animate_log_in_error_message();
					
					console.log( response );
					
				}
				
			},
			
			error: function() {
				
				$( '#rb-remote-log-in-error-message' ).html( 'Oops! An AJAX call has failed, which usually indicates an issue with this site.' ).css( { display: 'block' } );
				
				rb_animate_log_in_error_message();
				
			}
			
		} );
		
	} );
	
	$( '#rb-remote-log-out' ).click( function( event ) {		//log out
		
		event.preventDefault();
		
		rb_delete_cookie( 'RB_api_token' );
		
		rb_delete_cookie( 'RB_remote_user_display_name' );
		
		window.rb_remote_user_display_name = '';
		
		window.rb_api_token = '';
		
		//show "non logged in" block
		$( '#rb-remote-panel' ).stop().clearQueue().animate( {
			height: '1px'
		}, 'fast', function() {
			
			rb_show_non_logged_in_panel();
			
			setTimeout( function() {
				
				//if user has not started entering credentials, automatically close remote panel in 5 seconds
				if ( ! jQuery( '#rb-remote-user-login:focus' ).length && ! jQuery( '#rb-remote-user-pass:focus' ).length ) {
				
					jQuery( '#rb-remote-panel-button' ).click();
				
				}
				
			}, 5000 );
			
		} );
		
	} );
	
	//save page visited in the cookie
	var RB_pages_visited;
	
	RB_pages_visited = rb_get_cookie( 'RB_pages_visited' );
	
	if ( RB_pages_visited ) {
		
		RB_pages_visited = JSON.parse( RB_pages_visited ); 
		
	} else {
		
		RB_pages_visited = [];
		
	}
	
	RB_pages_visited.unshift( document.location.href );
	
	if ( RB_pages_visited.length > 10 ) {
		
		RB_pages_visited.pop();
		
	}
	
	document.cookie = 'RB_pages_visited' + '=' + JSON.stringify( RB_pages_visited ) + '; path=/';
	
	function rb_show_logged_in_panel() {
		
		$( '#rb-remote-user-display-name' ).text( rb_remote_user_display_name );
		
		$( '#rb-remote-panel' ).addClass( 'rb-remote-logged-in' );
		
		$( '#rb-remote-log-in-credentials-wrapper' ).css( { display: 'none' } );
		
		$( '#rb-remote-user-details-wrapper' ).css( { display: 'block' } );
		
		rb_panel_height = 50;
		
		rb_show_remote_panel();
		
	}
	
	function rb_show_non_logged_in_panel() {
		
		$( '#rb-remote-panel' ).removeClass( 'rb-remote-logged-in' );
		
		$( '#rb-remote-user-details-wrapper' ).css( { display: 'none' } );
		
		$( '#rb-remote-log-in-credentials-wrapper' ).css( { display: 'block' } );
		
		rb_panel_height = 255;
		
		rb_show_remote_panel();
		
	}
	
	function rb_show_remote_panel() {
		
		$( '#rollerblade-button-wrapper' ).addClass( 'stay-visible' );
		
		$( '#rb-remote-panel' ).css( { display: 'block' } ).stop().clearQueue().animate( {
			width: rb_panel_width + 'px',
			left: '-' + rb_panel_width + 'px',
		}, 'slow', function() {
			
			$( '#rb-remote-panel' ).stop().clearQueue().animate( {
				height: rb_panel_height + 'px'
			}, 'slow' );
			
		} );
		
	}
	
	function rb_hide_remote_panel() {
		
		$( '#rb-remote-panel' ).stop().clearQueue().animate( {
			height: '1px'
		}, 'slow', function() {
			
			$( '#rb-remote-panel' ).stop().clearQueue().animate( {
				width: 0,
				left: 0,
			}, 'slow', function() {
				
				$( '#rb-remote-panel' ).css( { display: 'none' } );
				
				$( '#rollerblade-button-wrapper' ).removeClass( 'stay-visible' );
				
			} );
			
		} );
		
	}
	
	function rb_animate_log_in_error_message() {
		
		$( '#rb-remote-log-in-error-message' ).animate( {
			marginRight: '5px',
		}, 80, function() {
			$( '#rb-remote-log-in-error-message' ).animate( {
				marginRight: '0',
			}, 80, function() {
				$( '#rb-remote-log-in-error-message' ).animate( {
					marginRight: '2px',
				}, 80, function() {
					$( '#rb-remote-log-in-error-message' ).animate( {
						marginRight: '0',
					}, 80 );
				} );
			} );
		} );
		
	}
	
} );

function rb_get_cookie( cname ) {
	
    var name = cname + '=';
    
    var ca = document.cookie.split( ';' );
    
    for ( var i = 0; i < ca.length; i++ ) {
    	
        var c = ca[ i ];
        
        while ( c.charAt(0)==' ' ) c = c.substring( 1 );
        
        if ( c.indexOf( name ) == 0 ) return c.substring( name.length,c.length );
        
    }
    
    return '';
    
}

function rb_delete_cookie( name ) {
	
	  document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
	  
}
