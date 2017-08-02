<?php
/**
 * Rejoiner Integration
 *
 * Allows Rejoiner tracking code to be inserted into store pages.
 *
 * @class 		WC_Rejoiner
 * @extends		WC_Integration
 */

class WC_Rejoiner extends WC_Integration {

	public function __construct() {
	
		if( isset( $_COOKIE['wp_woocommerce_session_' . COOKIEHASH ] ) ) {
			
			$this->sess = $_COOKIE['wp_woocommerce_session_' . COOKIEHASH ];
		
		} else {
			
			$this->sess = false;
			
		}
		
		$this->id = 'wc_rejoiner';
		$this->method_title = __( 'Rejoiner', 'woocommerce' );
		$this->method_description = __( 'Find these details on the Implementation page inside your Rejoiner dashboard.', 'woocommerce' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->rejoiner_id = $this->get_option( 'rejoiner_id' );
		$this->rejoiner_domain_name = $this->get_option( 'rejoiner_domain_name' );
		$this->rejoiner_api_key = $this->get_option( 'rejoiner_api_key' );
		$this->rejoiner_api_secret = $this->get_option( 'rejoiner_api_secret' );
		
		// Actions
		add_action( 'woocommerce_update_options_integration_wc_rejoiner', array( $this, 'process_admin_options') );
		add_action( 'wp_loaded', array( $this, 'refill_cart' ) );
		
		// Tracking code
		add_action( 'wp_footer', array( $this, 'rejoiner_tracking_code' ) );
						
		// REST conversion
		add_action( 'woocommerce_payment_complete', array( $this, 'rejoiner_rest_convert' ), 1, 1 );
		
		// JS conversion
		add_action( 'woocommerce_thankyou', array( $this, 'rejoiner_conversion_code' ), 2, 1 );

		// AJAX callback
		add_action( 'wp_ajax_rejoiner_sync', array( $this, 'rejoiner_sync' ) );

	}

	function init_form_fields() {

		$this->form_fields = array(
			
			'rejoiner_id' => array(
				'title' 			=> __( 'Rejoiner Account', 'woocommerce' ),
				'description' 		=> __( 'Enter your unique Site ID', 'woocommerce' ),
				'type' 				=> 'text',
		    	'default' 			=> ''
			),
			'rejoiner_domain_name' => array(
				'title' 			=> __( 'Set Domain Name', 'woocommerce' ),
				'description' 		=> __( 'Enter your domain for the tracking code. Example: .domain.com or .www.domain.com, be sure to include the leading period.', 'woocommerce' ),
				'type' 				=> 'text',
		    	'default' 			=> ''
			),
			'rejoiner_api_key' => array(
				'title' 			=> __( 'Rejoiner API Key', 'woocommerce' ),
				'description' 		=> __( 'Enter your API Key', 'woocommerce' ),
				'type' 				=> 'text',
		    	'default' 			=> ''
			),
			'rejoiner_api_secret' => array(
				'title' 			=> __( 'Rejoiner API Secret', 'woocommerce' ),
				'description' 		=> __( 'Enter your API Secret', 'woocommerce' ),
				'type' 				=> 'text',
		    	'default' 			=> ''
			)
			
		);

    } 
    
	function rejoiner_tracking_code() {
		
		global $rjconverted;
		
		$current_user = wp_get_current_user();
		
		if( $current_user instanceof WP_User && !empty( $current_user->user_email ) )
			$current_user_email = $current_user->user_email;
		else
			$current_user_email = false;
		
		if( ( is_cart() || is_checkout() ) && $rjconverted != true ) {
				
			global $woocommerce;
			
			$items = array();
			$savecart = array();
				
			foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				
				$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					
				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				
					if( $_product->variation_id > 0 && $_thumb_id = get_post_thumbnail_id( $_product->variation_id ) ) {
    				    
    				    $thumb_id = $_thumb_id;

    				} else {
    					
    					$thumb_id = get_post_thumbnail_id( $_product->post->ID );
    					
    				}
					
					$thumb_size = apply_filters( 'wc_rejoiner_thumb_size', 'shop_thumbnail' );
					
					$thumb_url = wp_get_attachment_image_src( $thumb_id, $thumb_size, true );
					
					$product_cats = get_the_terms( $_product->post->ID, 'product_cat');
	
					if( is_array( $product_cats ) ) {
    					
    					foreach( $product_cats as $cat ) {
	    					
		    				$cats[] = $cat->slug;
		    					
	    				}
	    					
	    				$product_cats_json = json_encode( $cats );
	    				
    				} else {
	    				
    				    $product_cats_json = null;

    				}					
	
					if( !empty($thumb_url[0]) ) {
					
						$image = $thumb_url[0];
						
					} else {
					
						$image = wc_placeholder_img( 'shop_thumbnail' );
						
					}
						
					if( $_product->variation_id > 0 ) {		
						
						$variantname = '';
						
						foreach ( $cart_item['variation'] as $name => $value ) {
		  
		                      if ( '' === $value )
		                          continue;
		  
		                      $taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );
		  
		                      if ( taxonomy_exists( $taxonomy ) ) {
		                          $term = get_term_by( 'slug', $value, $taxonomy );
		                          if ( ! is_wp_error( $term ) && $term && $term->name ) {
		                              $value = $term->name;
		                          }
		                          $label = wc_attribute_label( $taxonomy );
		 
		                      } else {
		                         $value = apply_filters( 'woocommerce_variation_option_name', $value );
		                         $product_attributes = $cart_item['data']->get_attributes();
		                         if ( isset( $product_attributes[ str_replace( 'attribute_', '', $name ) ] ) ) {
		                             $label = wc_attribute_label( $product_attributes[ str_replace( 'attribute_', '', $name ) ]['name'] );
		                         } else {
		                             $label = $name;
		                         }
		                     }
							 
							 $variantname.= ', ' . $label . ': ' . $value;
		                     $item_data[$name] = $value;
		                     	                     
		                }
		                
		                $variant = apply_filters( 'wc_rejoiner_cart_item_variant', $variantname );
		                $item_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . $variant;		                
		                
		                $_item = array(
							'product_id' => $_product->post->ID,
							'name' => $this->escape_for_json( apply_filters( 'wc_rejoiner_cart_item_name', $item_name ) ),
							'item_qty' => $cart_item['quantity'],
							'price' => $this->format_money( $_product->get_price() ),
							'qty_price' => $this->format_money( $cart_item['line_total'] ),
							'image_url' => $this->format_image_url( $image ),
							'product_url' => get_permalink( $_product->post->ID ),
							'category' => $product_cats_json,
							'variation_id' => $_product->variation_id					
						);
						
						$attributes = apply_filters( 'wc_rejoiner_cart_item_attributes', null, $item_data );
						
						if( is_array( $attributes ) ) {
							$_item['attribute_name'] = $attributes['attribute_name'];
							$_item['attribute_value'] = $this->escape_for_json( $attributes['attribute_value'] );
						}
		                
		                $items[] = $_item;
		                
	   					$savecart[] = array(
							'product_id' => $_product->post->ID,
							'item_qty' => $cart_item['quantity'],
							'variation_data' => $item_data,
							'variation_id' => $_product->variation_id
						);
	
					} else {
						
						$items[] = array(
							'product_id' => $_product->post->ID,
							'name' => $this->escape_for_json( apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) ),
							'item_qty' => $cart_item['quantity'],
							'price' => $this->format_money( $_product->get_price() ),
							'qty_price' => $this->format_money( $cart_item['line_total'] ),
							'image_url' => $this->format_image_url( $image ),
							'product_url' => get_permalink( $_product->post->ID ),
							'category' => $product_cats_json							
						);
						
						$savecart[] = array(
							'product_id' => $_product->post->ID,
							'item_qty' => $cart_item['quantity']
						);
						
					}
						
				}
				
			}
			
			set_transient( 'rjcart_' . $this->sess, $savecart, 168 * HOUR_IN_SECONDS);
			
			$cartdata = array(
				'cart_value' =>  $this->format_money( $woocommerce->cart->total ),
				'cart_item_count' => $woocommerce->cart->cart_contents_count,
			);
			
			$js = $this->build_rejoiner_push( $items, $cartdata, $current_user_email );
			
		} elseif( $rjconverted != true ) {
			
			$rejoiner_id = $this->rejoiner_id;
			$rejoiner_domain_name = $this->rejoiner_domain_name;
						
			if( !empty( $rejoiner_id ) && !empty( $rejoiner_domain_name ) ) {
				
				$js = <<<EOF
<!-- Rejoiner Tracking - added by WooCommerceRejoiner -->

<script type='text/javascript'>
var _rejoiner = _rejoiner || [];
_rejoiner.push(['setAccount', '{$rejoiner_id}']);
_rejoiner.push(['setDomain', '{$rejoiner_domain_name}']);

(function() {
    var s = document.createElement('script'); s.type = 'text/javascript';
    s.async = true;
    s.src = 'https://cdn.rejoiner.com/js/v4/rejoiner.lib.js';
    var x = document.getElementsByTagName('script')[0];
    x.parentNode.insertBefore(s, x);
})();

EOF;

				if( $current_user_email != false ) {
					$js.= "_rejoiner.push(['setCustomerEmail', { 'email' : '$current_user_email' } ]);";
				}

				if( is_product() ) {
					
					$_product = wc_get_product( get_the_ID() );
					$product_id = $_product->post->ID;
					
					$name = $this->escape_for_json( apply_filters( 'wc_rejoiner_cart_item_name', $_product->post->post_title ) );
					$product_url = get_permalink( $product_id );
										
					$thumb_id = get_post_thumbnail_id( $product_id );
					$thumb_size = apply_filters( 'wc_rejoiner_thumb_size', 'shop_thumbnail' );
					$thumb_url = wp_get_attachment_image_src( $thumb_id, $thumb_size, true );
					$thumb_url = $thumb_url[0];
					
					$product_cats = get_the_terms( $product_id, 'product_cat');
					$product_cats_json = '';
					
					if( is_array( $product_cats ) ) {
    					
    					foreach( $product_cats as $cat ) {
	    					
	    					$cats[] = $cat->slug;
	    					
    					}
    					
    					$product_cats_json = "'category':" . json_encode( $cats ) .',';
    					
    				}
					
					$price = $this->format_money( $_product->get_price() );
					
					$js.= "
	_rejoiner.push(['trackProductView', {
	    'product_id': '$product_id',
	    'name': '$name',
	    $product_cats_json
	    'price': $price,
	    'product_url': '$product_url',
	    'image_url': '$thumb_url'
	}]);					
					";
					
				}
			
			}
			
			$js.= '</script>
			<!-- End Rejoiner Tracking -->';
					
		}
		
		if( isset( $js ) )
			echo $js;
		
	}

	function rejoiner_sync() {
		
		global $woocommerce;

		$cart = array(
			'cart_value' =>  $this->format_money( $woocommerce->cart->total ),
			'cart_item_count' => $woocommerce->cart->cart_contents_count,
		);
		
		$returnUrl = $woocommerce->cart->get_cart_url() . '?rjcart=' . $this->sess;
		$cart['return_url'] = apply_filters( 'rejoiner_returnurl', $returnUrl, $this->sess, $cart );
		
		wp_send_json( $cart );		

	}

	function format_money( $number ) {
		
		$incents = $number * 100;
		$incents = intval( $incents );
		return $incents;
		
	}
	
	function format_description( $text ) {
		
		$text = str_replace( "'", "\'", strip_tags( $text ) );
		$text = str_replace( array("\r", "\n"), "", $text );
		
		return $text;
		
	}

	function format_image_url( $url ) {
		
		if( stripos( $url, 'http' ) === false ) {
			
			$url = get_site_url() . $url;
			
		}
		
		return $url;
		
	}
	
	function escape_for_json( $str ) {
		
		return str_ireplace( "'", "\'", $str );
		
	}
	
	function build_rejoiner_push( $items, $cart, $current_user_email ) {
	
		global $woocommerce, $rjremoved;
		
		$rejoiner_id = $this->rejoiner_id;
		$rejoiner_domain_name = $this->rejoiner_domain_name;
		$ajaxurl = admin_url( 'admin-ajax.php' );
		
		$returnUrl = $woocommerce->cart->get_cart_url() . '?rjcart=' . $this->sess;
		
		$cart['return_url'] = apply_filters( 'rejoiner_returnurl', $returnUrl, $this->sess, $cart );
		
		$cartdata = $this->rejoiner_encode( $cart );
		$cartjs = "_rejoiner.push(['setCartData', $cartdata]);";
		$itemjs = '';
		$emailjs = '';
		
		if( $current_user_email != false )
			$emailjs = "_rejoiner.push(['setCustomerEmail', { 'email' : '$current_user_email' } ]);";
		
		foreach( $items as $item ) {
			
			$data = $this->rejoiner_encode( $item );
			$itemjs.= "_rejoiner.push(['setCartItem', $data]);\r\n";
			
		}
		
		if( !empty( $rejoiner_id ) && !empty( $rejoiner_domain_name ) ) {
				
			$js = <<<EOF
<!-- Rejoiner Tracking - added by WooCommerceRejoiner -->

<script type='text/javascript'>
	var _rejoiner = _rejoiner || [];
	_rejoiner.push(['setAccount', '{$rejoiner_id}']);
	_rejoiner.push(['setDomain', '{$rejoiner_domain_name}']);
	
	(function() {
	    var s = document.createElement('script'); s.type = 'text/javascript';
	    s.async = true;
	    s.src = 'https://cdn.rejoiner.com/js/v4/rejoiner.lib.js';
	    var x = document.getElementsByTagName('script')[0];
	    x.parentNode.insertBefore(s, x);
	})();

    $cartjs
    $itemjs
    $emailjs

	(function ($,r) {
	    var Rejoiner = {
	        removeInProgress: false,
	        init: function() {
	            console.log('initialized Rejoiner');
	            $(document).ready(function(){
	                $( document ).on(
	                    'click',
	                    'td.product-remove > a',
	                    Rejoiner.beginItemRemove 
	                );
	                $( document ).on(
	                    'added_to_cart updated_wc_div updated_shipping_method updated_cart_totals',
	                    Rejoiner.requestUpdates
	                );
	            });
	        },
	        beginItemRemove: function(e) {
	            Rejoiner.removeInProgress = $(this).data('product_id');
	        },
	        requestUpdates: function(e) {
	            if (Rejoiner.removeInProgress !== false) {
	                console.log('removing item '+Rejoiner.removeInProgress+' from cart');
	                r.push(['removeCartItem', {product_id: Rejoiner.removeInProgress}]);
	                Rejoiner.removeInProgress = false;
	                console.log('requesting update setCartData');
	                $.post(
	                    '$ajaxurl', 
	                    {action: 'rejoiner_sync'},
	                    Rejoiner.updateCartData
	                );                 
	            }            
	        },
	        updateCartData: function(data) {
	            r.push(['setCartData', data]);
	            console.log( 'updated cart data with:');
	            console.log( data );
	        }
	    };
	    
	    Rejoiner.init();
	
	})(jQuery,_rejoiner);

</script>
<!-- End Rejoiner Tracking -->
EOF;

		} else {
			
			$js = "\r\n<!-- WooCommerce Rejoiner ERROR: You must enter your details on the integrations settings tab. -->\r\n";	
			
		}
		
		return $js;           
		
	}
	
	function rejoiner_encode( $array ) {
		
		$json = '{';
		
		foreach( $array as $key => $val ) {
			
			if( is_array( json_decode( $val, true ) ) ) 
				$items[]= "'$key' : $val";
			else
				$items[]= "'$key' : '$val'";			
			
		}
		
		$json.= implode( ', ', $items ) . '}';
		
		return $json;		
		
	}

	function rejoiner_conversion_code( $order_id ) {
		
		global $rjconverted;
		
		$rjconverted = true;
		
		$rejoiner_id = $this->rejoiner_id;
		$rejoiner_domain_name = $this->rejoiner_domain_name;

		if ( !$rejoiner_id ) {
			return;
		}

        $order = wc_get_order( $order_id );
        
        $total = $this->format_money( $order->get_total() );
        $item_count = $order->get_item_count();
        
        $promos = $order->get_used_coupons();
        if( is_array( $promos ) && ( count( $promos ) > 1 ) )
            $promo = implode( ',', $promos );
        elseif( is_array( $promos ) )
            $promo = array_pop( $promos );
        
        if( isset( $promo ) )
            $promojs = "'promo': '{$promo}',";
        else 
            $promojs = '';
            
        $returnurl = $order->get_view_order_url();
        
        $line_items = $order->get_items();
        $items = array();

    	foreach ( $line_items as $item ) {
        	
    		$_product = $order->get_product_from_item( $item );
       		
    		$qty = $item['qty'];
    		
    		$linetotal = $order->get_line_total( $item, true, true );
    		
    		$thumb_id = get_post_thumbnail_id( $_product->post->ID );
			
			$thumb_size = apply_filters( 'wc_rejoiner_thumb_size', 'shop_thumbnail' );
			
			$thumb_url = wp_get_attachment_image_src( $thumb_id, $thumb_size, true );				

			if( !empty($thumb_url[0]) ) {
			
				$image = $thumb_url[0];
				
			} else {
			
				$image = wc_placeholder_img( 'shop_thumbnail' );
				
			}
			
			$product_cats = get_the_terms( $_product->post->ID, 'product_cat');

			if( is_array( $product_cats ) ) {
				
				foreach( $product_cats as $cat ) {
					
    				$cats[] = $cat->slug;
    					
				}
					
				$product_cats_json = json_encode( $cats );
				
			} else {
				
			    $product_cats_json = null;

			}
    	
            $_item = array(
				'product_id' => $_product->post->ID,
				'name' => $this->escape_for_json( apply_filters( 'woocommerce_cart_item_name', $_product->get_title() ) ),
				'item_qty' => $qty,
				'price' => $this->format_money( $_product->get_price() ),
				'qty_price' => $this->format_money( $linetotal ),
				'image_url' => $this->format_image_url( $image ),
				'product_url' => get_permalink( $_product->post->ID ),
				'category' => $product_cats_json							
			);
            
            $items[] = $this->rejoiner_encode( $_item );
            
    	}
    	
    	$itemsjs = implode( ',', $items );	
        
		$js = <<<EOF
<!-- Rejoiner JavaScript API Conversion - added by WooCommerce Rejoiner -->

<script type='text/javascript'>
	var _rejoiner = _rejoiner || [];
	_rejoiner.push(['setAccount', '{$rejoiner_id}']);
	_rejoiner.push(['setDomain', '{$rejoiner_domain_name}']);
	_rejoiner.push(['sendConversion', {
        cart_data: {
            'cart_value': {$total},
            'cart_item_count': {$item_count},
            'customer_order_number': '{$order_id}',
            {$promojs}
            'return_url': '{$returnurl}'
        },
        cart_items: [
            $itemsjs        
        ]}
    ]);
	
	(function() {
	    var s = document.createElement('script');
	    s.type = 'text/javascript';
	    s.async = true;
	    s.src = 'https://cdn.rejoiner.com/js/v4/rejoiner.lib.js';
	    var x = document.getElementsByTagName('script')[0];
	    x.parentNode.insertBefore(s, x);
	})();
</script>

<!-- End Rejoiner JavaScript API Conversion -->                         		
EOF;
		
		echo $js;
		
	}
	
	function rejoiner_hash( $api_secret, $http_verb, $request_path, $request_body ) {
		
	    $content = "{$http_verb}\n{$request_path}\n{$request_body}";
	    $hash = hash_hmac( 'sha1', $content, $api_secret, true );
	    return base64_encode( $hash );
	    
	}
	
	function rejoiner_rest_convert( $order_id ) {
		
		global $rjconverted;
		
		$rejoiner_id = $this->rejoiner_id;
		$rejoiner_domain_name = $this->rejoiner_domain_name;
		$rejoiner_api_key = $this->rejoiner_api_key;
		$rejoiner_api_secret = $this->rejoiner_api_secret;
		
		if( !$rejoiner_id || !$rejoiner_api_key || !$rejoiner_api_secret ) {
			return;
		}
		
		$order = new WC_Order( $order_id );
		$email = $order->billing_email;
		$body = '{"email": "' . $email . '"}';
		
		$rejoiner_path = '/api/1.0/site/' . $rejoiner_id . '/lead/convert';		
		$hash = $this->rejoiner_hash( $rejoiner_api_secret, 'POST', $rejoiner_path, $body );
		$auth = 'Rejoiner ' . $rejoiner_api_key . ':' . $hash;
		
		$baseurl = 'https://app.rejoiner.com';
		$posturl = $baseurl . $rejoiner_path;
		 
		$args = array(
		    'body' => $body,
		    'timeout' => '5',
		    'redirection' => '5',
		    'httpversion' => '1.0',
		    'blocking' => true,
		    'headers' => array(
			    'Authorization' => $auth,
			    'Content-Type' => 'application/json'
		    ),
		);
		 
		$response = wp_remote_post( $posturl, $args );
		
		if( !is_wp_error( $response ) )
			$code = $response['response']['code'];
		
		if ( $code == 200 ) {
			
			$rjconverted = true;
	
			if( WP_DEBUG_LOG )
				error_log( "Rejoiner REST Conversion Success / HTTP Response: $code" );
		   
		} else {
			
			$rjconverted = false;
			
			if( is_wp_error( $response ) ) {

				$error_message = $response->get_error_message();
				
				if( WP_DEBUG_LOG )
					error_log( "Rejoiner REST Conversion Error : $error_message" );

			} else {
				
				if( WP_DEBUG_LOG )
					error_log( "Rejoiner REST Conversion Error / HTTP Response: $code" );				
				
			}			
			
		}

		
	}

	function refill_cart() {
		
		if ( isset( $_GET['rjcart'] ) ) {
			
			global $woocommerce;
						
			$this_sess = $_GET['rjcart'];	
			
			$carturl = $woocommerce->cart->get_cart_url();
					  
			$rjcart = get_transient( 'rjcart_' . $this_sess );
									
			if( !empty( $rjcart ) ) {
					
				$woocommerce->cart->empty_cart();
				
				foreach( $rjcart as $product ) {
								
					if( !empty( $product['variation_id'] ) && $product['variation_id'] > 0 ) {
						
						$woocommerce->cart->add_to_cart( 
							$product['product_id'], 
							$product['item_qty'], 
							$product['variation_id'], 
							$product['variation_data']
						);
							
					} else {
						
						$woocommerce->cart->add_to_cart(
							$product['product_id'], 
							$product['item_qty']
						);				
					
					}
			
				}

				$utm_source = ( isset( $_GET['utm_source'] ) ) ? $_GET['utm_source'] : 'rejoiner' ;
				$utm_medium = ( isset( $_GET['utm_medium'] ) ) ? $_GET['utm_medium'] : 'email' ;
				$utm_campaign = ( isset( $_GET['utm_campaign'] ) ) ? $_GET['utm_campaign'] : 'email' ;
				$utm_content = ( isset( $_GET['utm_content'] ) ) ? $_GET['utm_content'] : 'default' ;
				
				header( "location:$carturl?utm_source=$utm_source&utm_medium=$utm_medium&utm_campaign=$utm_campaign&utm_content=$utm_content" );	
				exit;	

			}	
			
		}
	
	}

}