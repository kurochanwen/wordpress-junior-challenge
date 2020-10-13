<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

get_header();
?>

<main id="site-content" role="main">
<?php
	//sorting categories from all the post
	//CHALLENGE - 1
	$categories = get_categories();
	foreach($categories as $category) {
		echo '<div><h3>' . $category->name . '</h3></div>';
		$allpost = get_posts( array(
			'post_type' => 'post',
			'cat' => $category->term_id,
			'meta_key'			=> 'order',
			'orderby'			=> 'meta_value',
			'order'				=> 'ASC'
		) );
		foreach($allpost as $post) {
			echo '<div><a href="' . get_post_permalink($post->ID) . '" onClick="addRedirectCount(' . $post->ID .' , ' . $post->redirect_count . ')">' . $post->post_title . ' - order ' . $post->order 
				. ' redirect count ' . $post->redirect_count .'</a></div>';
		};
		echo '<br>';
	}
	?>
	<!--
		END OF CHALLENGE 1
	-->	
	<?php
		//search for desired post 
		//CHALLENGE - 2
		if($_POST['submitsearchpost'] && $_POST['postName'] != '') {
			$postNameFormated = str_replace(' ', '-', strtolower($_POST['postName']));
			$findPost = get_posts( array(
				'post_type' => 'post',
				'name' => $postNameFormated,
				));
			if($findPost[0]->post_title) {				
				update_post_meta( $findPost[0]->ID, 'redirect_count', $findPost[0]->redirect_count + 1);
				echo ("<script>window.location.href = '".get_post_permalink($findPost[0]->ID)."'</script>");
			} else {
				echo '<h3 style="color: red;" >Post does not exist!</h3>';
			};
		};
	?>	
	<form id="formid" action="" method="POST" style="padding: 50px 0px 50px 0px;">
		<h1 class='site-title'>search post</h1>
		<input type="text" name="postName" value="" />
		<input type="submit" name="submitsearchpost" value="submit"/>
	</form>
	<!--
		END OF CHALLENGE 2
	-->		

	<!--
		Create post 
		CHALLENGE - 3
	-->	
	<form id="createpostform" action="" method="POST" enctype="multipart/form-data">
		<label><?php _e('Select Featured Image:', 'Your text domain here');?></label>
		<input type="file" name="image">
		<h1 class='site-title'>post title</h1>
		<input type="text" name="postTitle" value="" />
		<h1 class='site-title'>content</h1>
		<input type="text" name="postContent" value="" />
		<h1 class='site-title'>order</h1>
		<input type="text" name="postOrder" value="" />
		<input type="submit" name="submitcreatepost" value="submit" />
	</form>

	<?php 
		if($_POST['submitcreatepost']){
			$newPost = array(
				'post_title'    => wp_strip_all_tags( $_POST['postTitle'] ),
				'post_content'  => $_POST['postContent'],
				'meta_input'    => array(
					'order'     => $_POST['postOrder'],
					'redirect_count' => 0,
				),
			  );
			$post_id = wp_insert_post( $newPost );
			$upload = wp_upload_bits($_FILES["image"]["name"], null, file_get_contents($_FILES["image"]["tmp_name"]));
			if ( ! $upload_file['error'] ) {
				$filename = $upload['file'];
				$wp_filetype = wp_check_filetype($filename, null);
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title' => sanitize_file_name($filename),
					'post_content' => '',
					'post_status' => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment, $filename, $post_id );
	
				if ( ! is_wp_error( $attachment_id ) ) {
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
					wp_update_attachment_metadata( $attachment_id, $attachment_data );
					set_post_thumbnail( $post_id, $attachment_id );
				}
			}
			echo ("<script>window.location.href = '".get_post_permalink($post_id)."'</script>");
		}
	?>	
	<!--
		END OF CHALLENGE 3
	-->	
	<?php

	$archive_title    = '';
	$archive_subtitle = '';

	if ( is_search() ) {
		global $wp_query;

		$archive_title = sprintf(
			'%1$s %2$s',
			'<span class="color-accent">' . __( 'Search:', 'twentytwenty' ) . '</span>',
			'&ldquo;' . get_search_query() . '&rdquo;'
		);

		if ( $wp_query->found_posts ) {
			$archive_subtitle = sprintf(
				/* translators: %s: Number of search results. */
				_n(
					'We found %s result for your search.',
					'We found %s results for your search.',
					$wp_query->found_posts,
					'twentytwenty'
				),
				number_format_i18n( $wp_query->found_posts )
			);
		} else {
			$archive_subtitle = __( 'We could not find any results for your search. You can give it another try through the search form below.', 'twentytwenty' );
		}
	} elseif ( is_archive() && ! have_posts() ) {
		$archive_title = __( 'Nothing Found', 'twentytwenty' );
	} elseif ( ! is_home() ) {
		$archive_title    = get_the_archive_title();
		$archive_subtitle = get_the_archive_description();
	}

	if ( $archive_title || $archive_subtitle ) {
		?>

		<header class="archive-header has-text-align-center header-footer-group">

			<div class="archive-header-inner section-inner medium">

				<?php if ( $archive_title ) { ?>
					<h1 class="archive-title"><?php echo wp_kses_post( $archive_title ); ?></h1>
				<?php } ?>

				<?php if ( $archive_subtitle ) { ?>
					<div class="archive-subtitle section-inner thin max-percentage intro-text"><?php echo wp_kses_post( wpautop( $archive_subtitle ) ); ?></div>
				<?php } ?>

			</div><!-- .archive-header-inner -->

		</header><!-- .archive-header -->

		<?php
	}

	if ( have_posts() ) {

		$i = 0;

		while ( have_posts() ) {
			$i++;
			if ( $i > 1 ) {
				echo '<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />';
			}
			the_post();

			get_template_part( 'template-parts/content', get_post_type() );
			get_post_meta($post->ID, 'redirect_count', true);
		    get_post_meta($post->ID, 'order', true);
		}
		
	} elseif ( is_search() ) {
		?>

		<div class="no-search-results-form section-inner thin">

			<?php
			get_search_form(
				array(
					'label' => __( 'search again', 'twentytwenty' ),
				)
			);
			?>
		</div><!-- .no-search-results -->

		<?php
	}
	?>
	<?php get_template_part( 'template-parts/pagination' ); ?>
	
	
</main><!-- #site-content -->
<script src="http://code.jquery.com/jquery-1.11.2.min.js" type="text/javascript"></script>
<script type="text/javascript">
// script to test out on click to add redirect, not part of the challenge!
var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	function addRedirectCount(postId, newValue) {
		jQuery(document).ready(function($){
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'redirect',
					postId: postId,
					newValue: newValue,
				},
				success: function(response) {
					console.log(response)
				},
			})
		})
	}
</script>
<?php get_template_part( 'template-parts/footer-menus-widgets' ); ?>

<?php
get_footer();
