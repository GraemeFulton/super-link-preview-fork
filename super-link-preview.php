<?php
/*
Plugin Name: Super Link Preview
Plugin URI: http://wordpress.org/#
Description: Generate a preview (aka thumbnail) of any external link in the page, similar to Facebook or Google+ snippets.
Author: Daniele Perilli
Version: 1.0.1
Author URI: https://www.behance.net/danieleperilli
*/

if (!class_exists("SuperLinkPreview")):

    class SuperLinkPreview {
		
		/*
		* Configuration
		*/
		private $cf_prefix = ""; //Use for testing - Leave blank
		
		private $plugin_name = "super-link-preview";
		private $plugin_title = "Super Link Preview";
		private $plugin_mce = "superlinkpreview";
		private $shortcode_name = "link-preview";
		
		private $screenshot_services = array(
			array("Wordpress", "http://s.wordpress.com/mshots/v1/")
		);

		private $images_lazy_loading = true;
		
        public function __construct( $options = array() ) {
            add_shortcode($this->shortcode_name, array( $this, 'plugin_shortcode' ) );

            add_action( 'init', array( $this, 'plugin_tinymce_button' ) );
			add_action( 'save_post', array( $this, 'plugin_save_posts' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'plugin_scripts') );
			add_action( 'admin_menu', array($this, 'plugin_menu'));	
			add_action( 'admin_init', array($this, 'plugin_settings'));

        }
		
		/*
		* Settings 
		*/
		public function plugin_menu() {
			add_options_page($this->plugin_title, $this->plugin_title, 'manage_options', $this->plugin_name, array($this, 'plugin_option_page'));
		}
		
		public function plugin_option_page() {
			
			?>
            	<div class="wrap">
					<h2><?php echo $this->plugin_title; ?></h2>
                    <p>
                    	Insert the shortcode <strong>[<?php echo $this->shortcode_name; ?> url="external_link"]</strong> in your post to generate a preview (aka thumbnail) of any external link in the page (similar to Facebook or Google+ snippets).
                    </p>

                    <form method="post" action="options.php">
                        <?php 

							settings_fields($this->plugin_name);
               				do_settings_sections($this->plugin_name);
                            submit_button();
                        ?>
                   </form>
    
               </div>
            <?php
		}
		
		public function plugin_settings() {
			
			add_filter("plugin_action_links_" . plugin_basename(__FILE__), array($this, 'plugin_settings_link'));
			
			add_settings_section($this->plugin_name . '_options', '', '', $this->plugin_name);
			
			register_setting($this->plugin_name, $this->plugin_name . '_auto_embed', '');
			add_settings_field($this->plugin_name . '_auto_embed', "Embed media", array($this, 'plugin_auto_embed'), $this->plugin_name, $this->plugin_name . '_options', $args = array() );
			
			register_setting($this->plugin_name, $this->plugin_name . '_img_og_meta', '');
			add_settings_field($this->plugin_name . '_img_og_meta', "OpenGraph meta", array($this, 'plugin_img_og_meta'), $this->plugin_name, $this->plugin_name . '_options', $args = array() );
			
			register_setting($this->plugin_name, $this->plugin_name . '_img_min_width', '');
			register_setting($this->plugin_name, $this->plugin_name . '_img_min_height', '');
			add_settings_field($this->plugin_name . '_img_min_size', "Minimum image size", array($this, 'plugin_img_min_size'), $this->plugin_name, $this->plugin_name . '_options', $args = array() );
		
			
			register_setting($this->plugin_name, $this->plugin_name . '_img_disallow_ads', '');
			add_settings_field($this->plugin_name . '_img_disallow_ads', "Exclude ads", array($this, 'plugin_img_disallow_ads'), $this->plugin_name, $this->plugin_name . '_options', $args = array() );
			

			register_setting($this->plugin_name, $this->plugin_name . '_shot_service', '');
			add_settings_field($this->plugin_name . '_shot_service', "Screenshot", array($this, 'plugin_shot_service'), $this->plugin_name, $this->plugin_name . '_options', $args = array() );
			
		}
		
		public function plugin_settings_link($links) { 
			$settings_link = '<a href="options-general.php?page=' . $this->plugin_name . '.php">' . __('Settings') . '</a>'; 
			array_unshift($links, $settings_link); 
			return $links; 
		}
		
		
		public function plugin_img_min_size() {
			$w = $this->plugin_name . '_img_min_width';
			$h = $this->plugin_name . '_img_min_height';
			
			echo 'When parsing for most relevant image, ignore image smaller than <input name="' . $w . '" type="number" value="' . get_option($w, '220') . '" style="width:60px" /> px (<strong>min width</strong>) and <input name="' . $h . '" type="number" value="' . get_option($h, '220') . '" style="width:60px" /> px (<strong>min height</strong>)';
		}
		
		public function plugin_img_disallow_ads() {
			$field = $this->plugin_name . '_img_disallow_ads';
			echo '<input name="' . $field . '" type="checkbox" value="1" ' . checked( 1, get_option($field, true), false ) . ' />  <label for="' . $field . '">When parsing for most relevant image, ignore images matching standard ad banner sizes (<a href="https://support.google.com/adsense/answer/6002621" target="_blank">view ad sizes list</a>)</label>';
		}
		
		public function plugin_img_og_meta() {
			$field = $this->plugin_name . '_img_og_meta';
			echo '<input name="' . $field . '" type="checkbox" value="1" ' . checked( 1, get_option($field, true), false ) . ' />  <label for="' . $field . '">When parsing for most relevant image, prefer OpenGraph meta (<strong>og:image</strong> for image source and <strong>og:title</strong> for alt description) </label>';
		}
		
		public function plugin_auto_embed() {
			$field = $this->plugin_name . '_auto_embed';
			echo '<input name="' . $field . '" type="checkbox" value="1" ' . checked( 1, get_option($field, true), false ) . ' /> <label for="' . $field . '">If supported media is available (YouTube and Vimeo videos), embed media player</label>';
		}
		
		public function plugin_shot_service() {
			$field = $this->plugin_name . '_shot_service';
			echo 'If no relevant image is found during parsing, take screenshot with the service <select name="' . $field . '">';
			for ($i = 0; $i < count($this->screenshot_services); $i++) {
				$service = $this->screenshot_services[$i];
				echo '<option value="' . $i . '" ' . selected($i, get_option($field, 0), false) . '>' . $service[0] . '</option>';
			}
			echo '</select><br><br><em>(note that you can use <strong>forceshot=true</strong> attribute in the shortcode to bypass other steps)</em>';
			
		}

		/*
		* Front script
		*/
		public function plugin_scripts() {
    		wp_enqueue_script($this->plugin_name . '-script', plugins_url( 'js/script.js' , __FILE__ ), array( 'jquery' ));
                wp_enqueue_style($this->plugin_name . '-style', plugins_url( 'templates/content-card/content-card.css' , __FILE__ ));

		}

		/*
		* Save Post hook
		*/
		public function plugin_save_posts($post_id) {
			
			global $post;
			if ($post) {

				$urls = $this->shortcodes_url($post->post_content);
				foreach($urls as $url) {
					$this->capture_url($url, $post_id);
				}
			}
			
		}
		
		
        /*
		* Shortcode
		* usage: [link-preview url="link-to-website" width="x" forceshot="false"]
		*/
        public function plugin_shortcode( $attributes, $content = '', $code = '' ) {
            
			global $post;
			
            // Get attributes as parameters
            extract(shortcode_atts( array(
                'url' => '',
                'width' => 0,
		'forceshot' => false,
                'custom_image'=>''
            ), $attributes ));

            // Sanitize
            $width = intval($width);
            $forceshot = ($forceshot === "true"); 
            $url = esc_url( $url );
            $custom_image = $custom_image;
            
            if ($url) {
				
				//Get saved fields
				$slug = sanitize_title($url);
				$real_url = get_post_meta($post->ID, $this->cf_prefix . "url-" . $slug, true);
				
				if (!isset($real_url) || empty($real_url)) {
					//Capture url and images if not already done
					$real_url = $this->capture_url($url, $post->ID, !$forceshot);
				}
				
				$image_url = "";
				if (!$forceshot) {

					$auto_embed = get_option($this->plugin_name . '_auto_embed', true);
					if ($auto_embed) {
						//Embed video - the fast way
						require_once ABSPATH . DIRECTORY_SEPARATOR . 'wp-includes' . DIRECTORY_SEPARATOR . 'class-oembed.php';
						$oembed = _wp_oembed_get_object();
						$embeddable = ($oembed->get_provider($real_url, array('discover' => false)) !== false);
						if ($embeddable) {
							$embed_code = wp_oembed_get($real_url);
							if ($embed_code) 
								return $embed_code;
						}
					}
					
					//Get best parsed image
					$image_url = get_post_meta($post->ID, $this->cf_prefix . "img-" . $slug, true);
			
				}
                                //if we use a custom image url
                                if(!empty($custom_image)){
                                    
                                    $image_url = $custom_image;
 
                                }
				else if (empty($image_url)) {
					//Get screenshot
					$shot_service = get_option($this->plugin_name . '_shot_service', 0);
					$shot_service_url = $this->screenshot_services[intval($shot_service)][1];
					
					$image_url = $shot_service_url . urlencode($real_url);
					if ($width > 0) $image_url .= "?w=" . $width;
				}

				if (!empty($image_url)) {
                                        $img = '<div';
//                                                if ($this->images_lazy_loading)
//                                                        $img .= ' src="" data-src="' . $image_url . '" class="lazy link-img"';
//                                                else 
                                                        $img .= ' style="background-image:url('.$image_url.')" class="link-img"';

                                                if ($width > 0)
                                                        $img .= ' width="' . $width . '"';

                                                $alt = get_post_meta($post->ID, $this->cf_prefix . "alt-" . $slug, true);
                                                if (isset($alt) && !empty($alt))
                                                        $img .= ' alt="' . $alt . '" title="' . $alt . '"';

                                        $img .= '> </div>';
                                        //set title
                                        $title= $alt;
                                        //set desc
                                        $desc= get_post_meta($post->ID, $this->cf_prefix . "desc-" . $slug, true);
                                        //root url
                                        $parsedUrl = parse_url($url);
                                        $root = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/';
                                        
                                        ob_start();
                                        include plugin_dir_path(__FILE__).'templates/content-card/content-card.php';
                                        $html = ob_get_clean();
                                        
					return $html;
					
				}
			
                
                return '';
                
            }
		
        }

	
		/*
		* List all shortcodes url in a string
		*/
		private function shortcodes_url($content) {

			$urls = [];
			$pattern = get_shortcode_regex();
		
			if (preg_match_all( '/'. $pattern .'/s', $content, $matches) && array_key_exists( 2, $matches )) {
				
				$my_shortcode = array_keys($matches[2], $this->shortcode_name);
				if (!empty($my_shortcode)) {
					foreach($my_shortcode as $sc) {
					  $attrs = explode(' ', trim($matches[3][$sc]));
					  foreach($attrs as $attr) {
						  $kv = explode('=', trim($attr));
						  if (count($kv) > 0) {
							if (strtolower($kv[0]) == 'url')
								$urls[] = esc_url($kv[1]);  
						}
					  }
					}
				}
			}
			return $urls;
		}
	
	
		/*
		* Parse remote document for real url and images
		*/
		private function capture_url($url, $post_id, $parse_images = true) {

			//Create request
			$request = curl_init();
            curl_setopt_array($request, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HEADER => FALSE,
				CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                //CURLOPT_CAINFO => 'cacert.pem',
                CURLOPT_FOLLOWLOCATION => TRUE,
            ));
            $response = curl_exec($request);
			$real_url = curl_getinfo($request, CURLINFO_EFFECTIVE_URL); //Throw away shorten url
            curl_close($request);

			//Save real url in a custom field
			$slug = sanitize_title($url);
			update_post_meta($post_id, $this->cf_prefix . "url-" . $slug, $real_url);

			if($response && $parse_images) {
            	//Create DOM document
            	$document = new DOMDocument();

            	//Load response into document, if we got any
                libxml_use_internal_errors(true);
                $document->loadHTML($response);
                libxml_clear_errors();
				
				//Disable warnings	
				//error_reporting(E_ERROR | E_PARSE);
				
				$image_url = "";
				$image_alt = "";
                                $image_desc = "";
				
				//Get OpenGraph image and title
				$parse_og_meta = get_option($this->plugin_name . '_img_og_meta', true);
				if ($parse_og_meta) {
					foreach($document->getElementsByTagName('meta') as $meta_tag) {
						
						if (empty($image_url)) {
							if ($meta_tag->getAttribute('property') == 'og:image'){ 
								$image_url = $meta_tag->getAttribute('content');
							}
						}
						
						if (empty($image_alt)) {
							if ($meta_tag->getAttribute('property') == 'og:title'){ 
								$image_alt = $meta_tag->getAttribute('content');
							}
						}
                                                if (empty($image_desc)) {
							if ($meta_tag->getAttribute('property') == 'og:description'){ 
								$image_desc = $meta_tag->getAttribute('content');
							}
						}

						if (!empty($image_url) && !empty($image_alt) && !empty($image_desc))
							break;
					}
	
				}
				
				if (!$this->has_right_size($image_url)) {
					
					//Get Base url
					$base = "";
            		foreach($document->getElementsByTagName('base') as $base_tag) {
						$base = $base_tag->getAttribute('href'); 	
						break;
					}
						
					//Parse images
					foreach($document->getElementsByTagName('img') as $img_tag) {
						
						$image_src = $this->absolute_url($img_tag->getAttribute('src'), $base);
						if ($this->has_right_size($image_src)) {
							$image_url = $image_src;
							break;
						}
					}
						
				} 
					
					
				if (!empty($image_url)) {
					
					//Save image url in a custom field
					update_post_meta($post_id, $this->cf_prefix . "img-" . $slug, $image_url);
						
				}
				
				if (!empty($image_alt)) {
					
					//Strip URLS from alt
					$image_alt = strip_tags($this->strip_URL($image_alt));
	
					//Save alt in a custom field
					update_post_meta($post_id, $this->cf_prefix . "alt-" . $slug, $image_alt);
						
				}
				if (!empty($image_desc)) {
					
					//Strip URLS from desc
					$image_desc = strip_tags($this->strip_URL($image_desc));
	
					//Save desc in a custom field
					update_post_meta($post_id, $this->cf_prefix . "desc-" . $slug, $image_desc);
						
				}
            }
			
			return $real_url;

		}
	
		/* 
		* Check for allowed images size
		*/
		private function has_right_size($image_url) {
			
			$images_min_width = get_option($this->plugin_name . '_img_min_width', 220);
			$images_min_height = get_option($this->plugin_name . '_img_min_height', 220);
			$images_disallow_ads = get_option($this->plugin_name . '_img_disallow_ads', true);
			
			if ($image_url && !empty($image_url) && is_string($image_url)) {
				
				if ($images_min_width > 0 || $images_min_height > 0 || !$this->images_allow_ads || !$this->images_allow_square) {
					
					$size_data = @getimagesize($image_url);
					if (!$size_data) 
						return false;
					
					list($width, $height) = $size_data;
				 
				 	if ($width < $images_min_width || $height < $images_min_height)
				 		return false;
		
					 if ($images_disallow_ads) {
						 
						 //Check ads domains
						 $ads_domains = array(
						 	'doubleclick.net',
						 	'admob.com',
						 	'advertising.apple.com'
						);
						 foreach ($ads_domains as $domain) {
							if (strpos(	$image_url, $domain) !== false)
								return false;
						}
						 
						 //Check ads standard sizes
						 //Sizes taken from https://support.google.com/adsense/answer/6002621
						$ads_sizes = array(
							array(300, 250),
							array(336, 228),
							array(728, 90),
							array(300, 600),
							array(320, 100),
							array(320, 50),
							array(468, 60),
							array(234, 60),
							array(120, 600),
							array(120, 240),
							array(160, 600),
							array(300, 1050),
							array(970, 90),
							array(970, 250),
							array(250, 250),
							array(200, 200),
							array(180, 150),
							array(125, 125)
						 );
						 foreach ($ads_sizes as $size) {
							if ($width == $size[0] && $height == $size[1])
								return false;
						}
					 }
				}
				
				return true;
			}
			
			return false;
				
		}
		
		/*
     	* Get absolute url
     	* Based on code by Torleif Berger (http://www.geekality.net/2011/05/12/php-dealing-with-absolute-and-relative-urls/)
     	*/
		private function absolute_url($url, $base) {
      
            if(!$url) return $base;

            // Already absolute URL
            if(parse_url($url, PHP_URL_SCHEME) != '') return $url;

            // Only containing query or anchor
            if($url[0] == '#' || $url[0] == '?') return $base . $url;

            // Parse base URL and convert to local variables: $scheme, $host, $path
            extract(parse_url($base));

            // If no path, use /
            if(!isset($path)) $path = '/';

            // Remove non-directory element from path
            $path = preg_replace('#/[^/]*$#', '', $path);

            // Destroy path if relative url points to root
            if($url[0] == '/') $path = '';

            // Dirty absolute URL
            $abs = "$host$path/$url";

            // Replace '//' or '/./' or '/foo/../' with '/'
            $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
            for($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {}

            return $scheme . '://' . $abs;
        }

		/*
		* Remove URLs from given string
		*/
		private function strip_URL($string) {
	
		  $U = explode(' ', $string);
		
		  $W =array();
		  foreach ($U as $k => $u) {
			if (stristr($u,'http') || (count(explode('.',$u)) > 1)) {
			  unset($U[$k]);
			  return $this->strip_URL( implode(' ',$U));
			}
		  }
		  return implode(' ',$U);
		}

		
        /*
		* TinyMCE Button
		* shortcode.js required
		*/
		public function plugin_tinymce_button() {

            // Capabilities check
            if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
                return;

            if ( get_user_option('rich_editing') == 'true' ) {
                add_filter( 'mce_external_plugins', array( $this, 'plugin_add_button' ) );
                add_filter( 'mce_buttons', array( $this, 'plugin_register_button' ) );
            }
        }
		
        public function plugin_register_button( $buttons ) {
            array_push( $buttons, "|", $this->plugin_mce);

            return $buttons;
        }

        public function plugin_add_button( $plugin_array ) {
            $plugin_array[$this->plugin_mce] = plugins_url( 'js/mce.js' , __FILE__ );

            return $plugin_array;
        }

        
    }

    $SuperLinkPreview = new SuperLinkPreview();

endif;

?>