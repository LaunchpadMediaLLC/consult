<?php 

    function add_google_fonts(){
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100;200;300;400;500;600;700;800;900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">';
    }
    add_action('wp_head', 'add_google_fonts', 995);

    function my_theme_enqueue_styles() { 

        wp_enqueue_script( 'tailwind-css', 'https://cdn.tailwindcss.com', '', false, false);
        //Alpine JS
        wp_enqueue_script( 'alpine-js', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', array(), '', false );
        //DataTables
        wp_enqueue_script( 'datatables-js', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array(), '', true );
        //Tiny JS
        wp_enqueue_script( 'tiny-js', 'https://cdn.tiny.cloud/1/9z8b16k0px312yt3biduc4135li3pllecz18iio47aq99lz8/tinymce/6/tinymce.min.js', array(), '1.0.0', false );

        wp_enqueue_style( 'datatables-style', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css' );

        wp_enqueue_style( 'consult-style', get_stylesheet_directory_uri() . '/assets/css/app.css' );

        wp_enqueue_script( 'app-js', get_stylesheet_directory_uri() . '/assets/js/app.js', array( 'jquery' ), '', false );

        // Define the ajax_object variable and pass it to the JavaScript code
        wp_localize_script( 'app-js', 'ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce('ajaxnonce')
        ) );

        if(is_page('submit-consult')):
          wp_dequeue_script( 'tiny-js' );
        endif;
    }
    add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles', 999);

    /**
     * Add async attributes to enqueued scripts where needed.
     * The ability to filter script tags was added in WordPress 4.1 for this purpose.
     */
    function my_async_scripts( $tag, $handle, $src ) {
        // the handles of the enqueued scripts we want to async
        $async_scripts = array( 'alpine-js' );

        $refer_scripts = array( 'tiny-js' );

        if ( in_array( $handle, $async_scripts ) ) {
            return '<script id="'.$handle.'" defer src="' . $src . '" ></script>' . "\n";
        }

        if ( in_array( $handle, $refer_scripts ) ) {
            return '<script id="'.$handle.'" referrerpolicy="origin" src="' . $src . '" ></script>' . "\n";
        }

        return $tag;
    }
    add_filter( 'script_loader_tag', 'my_async_scripts', 10, 3 );

    //Write inline scripts directly to the header
    add_action('wp_head', 'theme_inline_header_scripts', 999);
    function theme_inline_header_scripts() { 
          echo '<script>
            tailwind.config = {
              theme: {
                extend: {
                  colors: {
                    main: "#E6EDF4",
                    background: "#F6F6FA",
                    text: "#647280",
                    title: "#445F82",
                    pink: "#D57F7F",
                    "light-pink": "#ECD0CC",
                    yellow: "#f1c40f",
                    "light-yellow": "#FFEECD",
                    blue: "#19468f",
                    "light-blue": "#CCD7EC",
                    green: "#4c9f45",
                    "light-green": "#CFECCC",
                    orange: "#e15a2a"
                  },
                  width: {
                  },
                  maxWidth: {
                  },
                  fontFamily: {
                    sans: ["Roboto", "Arial", "sans-serif"],
                    serif: ["Roboto Slab", "serif"],
                  }
                }
              }
            }
          </script>';
    }

    function remove_jquery_migrate( $scripts ) {
  
      if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
          
        $script = $scripts->registered['jquery'];
        
        if ( $script->deps ) { 
          $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
        }
      }
    }
    add_action( 'wp_default_scripts', 'remove_jquery_migrate' );