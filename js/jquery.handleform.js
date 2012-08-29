/*!
 * jQuery $.handleForm
 * Original author: @t1mmen
 * Licensed under the MIT license
 * 
 * @url : https://github.com/t1mmen/jquery-handleform/
 * @date : Nov 29th, 15:43 2011
 */

;(function ( $, window, document, undefined ) {


	var functions = {
				 
		/************************************************
		 * Handles the submit button
		 ***********************************************/
		toggleButton : function(form) {

			// Find the submit button in the form			
			var $button = $(form).find('input[type=submit]');
						
			// Remember the text on the submit button..
			if (!$button.attr('data-default-text')) {
				//$button.data('default-text',$button.val()); // why .data no work?
				$button.attr('data-default-text', $button.val()); // but this works?
			}
			
		
			// If the button is disabled already
			if ($button.hasClass('disabled')) {
			
				// Enable the button
				//$button.removeClass('disabled').val($button.data('default-text')).removeAttr('disabled');
				$button.removeClass('disabled').val($button.attr('data-default-text')).removeAttr('disabled');
			
			// If button is disabled.
			} else {
			
				// Disable the button and set text as options.loadingText
				$button.addClass('disabled').val(handleFormOptions.loadingText).attr('disabled', 'disabled');
				
			}
									
		},
		/************************************************
		 * Validation errors function
		 ***********************************************/		
		handleValidationErrors : function(errors, form) {
		
			var errorTitle = handleFormOptions.errorMessage;
			
			// If the JSON response also has a messageFlash.message
			// as generated by ValidationMessageComponent, always show that.
			if (!jQuery.isEmptyObject(errors.messageFlash)) {
				errorTitle = errors.messageFlash.message;
			}
		
			// Build our error template
			var tmpl = '<div class="alert alert-block '+ handleFormOptions.errorClass +'">';
				tmpl += '<p>';
				tmpl += '<h4 class="alert-heading">' + errorTitle + '</h4>'; //@todo if set.
				tmpl += '<ul>';
				
					// Loop through the errors 
					$.each(errors.validationErrors, function(i, value) { 
					
						// And add the error to the list.
						$.each(value, function(j, val){
							tmpl += '<li>' + value[j] + '</li>';
						});
						//tmpl += '<li>' + value[0] + '</li>';
						// Let's guesstimate the input that gave us an error
						var $inputField = $('[name*="'+i+'"]');

						// .. and error classes to the input field, it's parent div, and the div above that.
						// This is a quick & dirty workaround since my DOM differs a bit compared to Twitter Bootstrap's.						
						if ($inputField.length) {
							//$($inputField).addClass('error').parent('div.input').addClass('error').parent('div.input').addClass('error');
							$($inputField).closest('.control-group').addClass('error');
						}
												
					});					
			
				tmpl += '</ul>';
				tmpl += '</p>';
				tmpl += '</div>';

      		// If the target block doesn't exist..
			if (!$(handleFormOptions.errorContainer).length) {
			
					// Create it.
      				$(form).prepend('<div class="' + handleFormOptions.errorContainer.substr(1) +'" style="display:none"></div>');
      		}
      		
      		// Empty any previous error messages, insert the new errors and slide it in to view.
      		$(handleFormOptions.errorContainer)
      			.empty()
      			.html(tmpl)
      			.slideDown(handleFormOptions.transitionTime)
      			//.delay(handleFormOptions.transitionTime * 20)
      			//.slideUp(handleFormOptions.transitionTime);			
		},
		/************************************************
		 * How to handle a successful request
		 ***********************************************/		
		handleSuccess : function(response, form) {	
		
		
			// If we're forwarding somewhere on success
			if (handleFormOptions.successUrl) {
			
				// Send the user along..
			    setTimeout(function () {
			      location.href = handleFormOptions.successUrl;
			    }, handleFormOptions.successUrlDelay)

			}
			
					
			// Do we want to close the modal?
			if (handleFormOptions.closeModal) {
				
				var $modal = $('.modal');
				
				// If the .modal dom element exists,
				if ($modal.length) {
				
					// .. and it has the id of #facebox (https://github.com/defunkt/facebox)
					if ($modal.attr('id') == 'facebox') {
						
						jQuery(document).trigger('close.facebox')
						
					// If we're not using Facebox, we assume it's twitters modal script
					} else {
						$(form).closest('.modal').modal('hide');
						$(form).each(function(){
					        this.reset();
						});
					}
				}
				
			}
			
			// If we have a target set for the result
			if (handleFormOptions.target) {
				
				// If the container exists.
				if ($(handleFormOptions.target).length) {
				
					// And put response into .target using .targetMethod
					$(handleFormOptions.target)[handleFormOptions.targetMethod](response); 
				} else {
					console.log('No target container exists');
				}
				
				
			}
			
			// If we're setting a successmessage (as in, it's != false)
			if (handleFormOptions.successMessage) {
			
				 // Do we have a place to put it?
				if (!$(handleFormOptions.successContainer).length) {

						// Create it.
	      				$(form).prepend('<div class="' + handleFormOptions.successContainer.substr(1) +'" style="display:none"></div>');
	      		}
	      		
				// Build our success template
				var tmpl = '<div class="alert alert-block '+ handleFormOptions.successClass +'"">';
					tmpl += '<p>';
					tmpl += '<h4 class="alert-heading">' + handleFormOptions.successMessage + '</h4>';
					tmpl += '</p>';
					tmpl += '</div>';
							
	      		// Empty any previous error messages, insert the new errors and slide it in to view.
	      		$(handleFormOptions.successContainer)
	      			.empty()
	      			.html(tmpl)
	      			.slideDown(handleFormOptions.transitionTime)
	      			.delay(handleFormOptions.transitionTime * 10)
	      			.slideUp(handleFormOptions.transitionTime);
	      		$(form).each(function(){
			        this.reset();
				});
			}
			

			
		
		},	
	};
  

    // 
	/************************************************
	 * Plugin defaults.
	 ***********************************************/	    
    var pluginName = 'handleForm',
        defaults = {
            
            // When should the form submit? onBlur, etc
            trigger 		: 'submit', 
            
            // slide in/out errors how fast? in ms.
            transitionTime 	: 250,
            
            // What should the submit button read while request is working?
            // can overwrite with data-loading-text="Please wait.." on submit button.
            loadingText 	: 'Saving...',
            
             // Which container to place this content in? id or .classname
            target 			: false,
            
            // prepend, append, etc. What happens to the target dom?
            targetMethod	: 'replaceWith', 
            
            // If set to true, will find $.closest .modal and close it
            closeModal 		: false, 
            
             // If we're forwarding on success
            successMessage 	: 'Great success!',  
            
            // Which of the .alert-message substyles to use on success? NO PERIOD!
            successClass : 'alert-info',
            
            // Want to send the user somewhere on successfull request?
            successUrl 		: false,
            
            // Delay the URL forward?
            successUrlDelay	: 0,
			
			// Where do you want the good news?
			successContainer: '.success-message-container', 
						
			// What should read above the validation errors?            
            errorMessage	: 'Uh oh, something went wrong skipper:', // @todo, new name for above
            
            // Which of the .alert-message substyles to use? NO PERIOD!
            errorClass		: 'alert-error',
            
            // Want to send the user somewhere on failed request?
            errorUrl : false, 
            
            // set to .classname or #id. Prepends to <form> if not existing. WITH PERIOD.
            errorContainer 	: '.error-message-container', 
            
            
            /**
             * Callbacks
             * 
             * Specify as functions. Takes data as param.
             * function(data) { alert(data); }
             */ 
            
            // Specify callback for any JSON response that isn't validationErrors
            onJsonSuccess 	: false, 
            
            // Spesicy _AN ADDITIONAL_ callback to run upon completion.
            onSuccess		: false,
            
            // Spesicy _AN ADDITIONAL_ callback to run if an error occours.
            onError			: false,
            
        };


    // The actual plugin constructor
    function Plugin( form, options ) {
        this.element = form;

        // merge defaults and options.
        this.options = $.extend( {}, defaults, options) ;

        this._defaults = defaults;
        this._name = pluginName;

        this.init();

        /**
         * Let's start the form request
         */
         
        $(form).on(this.options['trigger'], function(e) {

			// Don't follow the link as per usual.
			e.preventDefault();
				
			// Let's do the AJAX thing, baby:
			var jqxhr = $.ajax({
			
				// Where are we sending this?
				url: $(form).attr('action'),
				
				// data to send
				data : $(form).serialize(),
				
				// How are we sending this?
				type: $(form).attr('method'),
				
				// Before sending the request..
				beforeSend : function(jqXHR, settings) {
					
					// Handle the submit button states
					functions.toggleButton($(form));
					
					// Hide any errorContainers:
					$(handleFormOptions.errorContainer).slideUp(handleFormOptions.transitionTime);
					
					// Hide any successContainers:
					$(handleFormOptions.successContainer).slideUp(handleFormOptions.transitionTime);

				},
				
				// If request is successful:
				success : function(data, textStatus, jqXHR) {
					
					// Handle the submit button states
					functions.toggleButton($(form));
					
					// What sort of response are we getting from the server?
					var $responseHeader = jqxhr.getResponseHeader('Content-Type');
					
					/**
					 * Handle JSON responses
					 */
					if ($responseHeader == 'application/json') {
					
						// If we're getting errors:
						if (!jQuery.isEmptyObject(data.validationErrors)) {
						
							// Handle them exclusively
							return functions.handleValidationErrors(data, $(form));	
						}
						

						// Do we have a onJsonSuccess callback function? 
 						if($.isFunction(handleFormOptions.onJsonSuccess)){
 							
 							// Sweet, let's run it:
					    	handleFormOptions.onJsonSuccess.call(this, data);
					    }						    				
					    				    
						
					/**
					 * Any other type of response
					 */
					} else {
					
						// Do we have a callback function? 
 						if($.isFunction(handleFormOptions.onSuccess)){
 							
 							// Sweet, let's run it:
					    	handleFormOptions.onSuccess.call(this, data);
					    }		
											
					}
										
					// Take care of successful request
					return functions.handleSuccess(data, $(form));					
					
	
					
				},
				
				// If request failed:
				error : function(jqXHR, textStatus, errorThrown) {
				
					// Do we have a callback function? 
 					if($.isFunction(handleFormOptions.onError)){
 							
 						// Sweet, let's run it:
					   	handleFormOptions.onError.call(this, data);
					}	
				
				}
				
			});
		
		}); // end AJAXIfy   
        
    }

    Plugin.prototype.init = function () {
        // Place initialization logic here
        // You already have access to the DOM element and
        // the options via the instance, e.g. this.element
        // and this.options
        
        
        // Make options avail. to whole script.
        // @todo Am I doing this right?
        handleFormOptions = this.options;
        
        
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new Plugin( this, options ));
            }
        });
        
    }

})( jQuery, window, document );