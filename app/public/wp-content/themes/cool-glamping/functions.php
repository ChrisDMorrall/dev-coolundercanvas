<?php
function pageBanner($args = NULL) {
  if (!$args['title']) {
    $args['title'] = get_the_title();
  }

  if (!$args['subtitle']) {
    $args['subtitle'] = get_field('page_banner_subtitle');
  }

  if (!$args['photo']) {
    if (get_field('page_banner_background_image')) {
      $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
    } else {
      $args['photo'] = get_theme_file_uri('/img/page-banner-default.jpg');
    }
  }

  ?>
  <div class="page-banner">
    <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
    <div class="page-banner__content container container--narrow">
      <h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
      <div class="page-banner__intro">
        <p><?php echo $args['subtitle']; ?></p>
      </div>
    </div>
  </div>
<?php }

// Custom Comments function
function cool_comment($comment, $args, $depth) {
    if ( 'div' === $args['style'] ) {
        $tag       = 'div';
        $add_below = 'comment';
    } else {
        $tag       = 'li';
        $add_below = 'div-comment';
    }?>
    <<?php echo $tag; ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID() ?>"><?php
    if ( 'div' != $args['style'] ) { ?>
        <div id="div-comment-<?php comment_ID() ?>" class="comment-body"><?php
    } ?>
        <div class="comment-author vcard"><?php
            if ( $args['avatar_size'] != 0 ) {
                echo get_avatar( $comment, $size='74' );
            }
             ?>
        </div>
        <div class="reply"><?php
                comment_reply_link(
                    array_merge(
                        $args,
                        array(
                            'add_below' => $add_below,
                            'depth'     => $depth,
                            'max_depth' => $args['max_depth']
                        )
                    )
                ); ?>
            <span><i class="fas fa-reply reply__icon"></i></span>
            <!-- TODO: Work out how to change icon on hover associated with preceding a tag -->
        </div>
        <?php
        if ( $comment->comment_approved == '0' ) { ?>
            <em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.' ); ?></em><br/><?php
        } ?>
        <div class="comment-meta commentmetadata">
            <?php printf( __( '<cite class="comment__cite">%s</cite>' ), get_comment_author_link() ); ?>
            <div class="comment__timestamp-div">
              <a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>" class="comment__timestamp"><?php
                  /* translators: 1: date, 2: time */
                  printf(
                      __('%1$s at %2$s'),
                      get_comment_date(),
                      get_comment_time()
                  ); ?>
              </a>
            </div>
            <?php edit_comment_link( __( 'Edit' ), ' <p> ', '</p>' ); ?>
            <?php comment_text(); ?>
        </div>
        <?php
    if ( 'div' != $args['style'] ) : ?>
        </div><?php
    endif;
}


function coolUnderCanvas_files() {
  wp_enqueue_script('main-js', get_theme_file_uri('/js/scripts-bundled.js'), NULL, '1.0', true);
  wp_enqueue_style('font-awsesome', '//use.fontawesome.com/releases/v5.0.13/css/all.css');
  wp_enqueue_style('google-fonts', '//fonts.googleapis.com/css?family=Montserrat');
  wp_enqueue_style('coolUnderCanvas_main_styles', get_stylesheet_uri());
}

add_action('wp_enqueue_scripts', 'coolUnderCanvas_files');

function cool_features() {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_image_size('pageBanner', 1500, 350, true);
}

add_action('after_setup_theme', 'cool_features');

function wpdocs_custom_excerpt_length( $length ) {
    return 30;
}
add_filter( 'excerpt_length', 'wpdocs_custom_excerpt_length', 999 );

function add_image_class($content){

        $content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML(utf8_decode($content));

        $imgs = $document->getElementsByTagName('img');
        foreach ($imgs as $img) {
           $img->setAttribute('class','generic-content__post-image');
        }

        $html = $document->saveHTML();
        return $html;
}

add_filter('the_content', 'add_image_class');
