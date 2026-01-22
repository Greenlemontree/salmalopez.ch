<?php
/**
 * Project Meta Box - Gallery (Images & Videos)
 *
 * Adds custom fields for project media gallery
 * supporting both images and videos in the same carousel.
 *
 * @package portfolio-salma
 */

/**
 * Register meta box for project media
 */
function portfolio_salma_project_meta_box() {
	add_meta_box(
		'project_media',
		__( 'Project Media', 'portfolio-salma' ),
		'portfolio_salma_project_media_callback',
		'project',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portfolio_salma_project_meta_box' );

/**
 * Meta box callback - render the fields
 */
function portfolio_salma_project_media_callback( $post ) {
	// Add nonce for security
	wp_nonce_field( 'portfolio_salma_project_media', 'portfolio_salma_project_media_nonce' );

	// Get existing values
	$gallery_ids     = get_post_meta( $post->ID, '_project_gallery', true );
	$video_ids       = get_post_meta( $post->ID, '_project_videos', true );
	$project_year    = get_post_meta( $post->ID, '_project_year', true );
	$project_category = get_post_meta( $post->ID, '_project_category', true );
	$project_tools   = get_post_meta( $post->ID, '_project_tools', true );
	$project_website = get_post_meta( $post->ID, '_project_website', true );
	$project_youtube = get_post_meta( $post->ID, '_project_youtube', true );
	$video_graded_id = get_post_meta( $post->ID, '_project_video_graded', true );
	$video_log_id    = get_post_meta( $post->ID, '_project_video_log', true );

	// Enqueue media scripts
	wp_enqueue_media();
	?>
	<style>
		.project-meta-section {
			margin-bottom: 25px;
			padding-bottom: 20px;
			border-bottom: 1px solid #eee;
		}
		.project-meta-section:last-child {
			border-bottom: none;
		}
		.project-meta-section > label {
			display: block;
			font-weight: 600;
			margin-bottom: 8px;
			font-size: 14px;
		}
		.project-meta-section p.description {
			color: #666;
			font-style: italic;
			margin-top: 5px;
			margin-bottom: 10px;
		}
		.media-preview {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			margin: 10px 0;
		}
		.media-preview-item {
			position: relative;
			width: 120px;
			height: 120px;
		}
		.media-preview-item img,
		.media-preview-item video {
			width: 100%;
			height: 100%;
			object-fit: cover;
			border: 1px solid #ddd;
			border-radius: 4px;
		}
		.media-preview-item.is-video::after {
			content: '▶';
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: rgba(0,0,0,0.7);
			color: white;
			width: 32px;
			height: 32px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 12px;
			pointer-events: none;
		}
		.media-preview-item .remove-media {
			position: absolute;
			top: -8px;
			right: -8px;
			background: #dc3545;
			color: white;
			border: none;
			border-radius: 50%;
			width: 24px;
			height: 24px;
			cursor: pointer;
			font-size: 14px;
			line-height: 1;
		}
		.media-preview-item .remove-media:hover {
			background: #c82333;
		}
		.button-group {
			display: flex;
			gap: 10px;
			margin-top: 10px;
		}
	</style>

	<!-- Images Gallery Section -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Project Images', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Add images for this project. They will appear in the carousel.', 'portfolio-salma' ); ?></p>

		<div id="images-preview" class="media-preview">
			<?php
			if ( ! empty( $gallery_ids ) ) {
				$ids_array = explode( ',', $gallery_ids );
				foreach ( $ids_array as $id ) {
					$img_url = wp_get_attachment_image_url( $id, 'thumbnail' );
					if ( $img_url ) {
						echo '<div class="media-preview-item" data-id="' . esc_attr( $id ) . '">';
						echo '<img src="' . esc_url( $img_url ) . '" alt="">';
						echo '<button type="button" class="remove-media">&times;</button>';
						echo '</div>';
					}
				}
			}
			?>
		</div>

		<input type="hidden" id="project_gallery" name="project_gallery" value="<?php echo esc_attr( $gallery_ids ); ?>">
		<button type="button" class="button" id="add-images-btn">
			<?php esc_html_e( 'Add Images', 'portfolio-salma' ); ?>
		</button>
	</div>

	<!-- Videos Gallery Section -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Project Videos', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Add videos for this project. They will appear in the carousel with a play button.', 'portfolio-salma' ); ?></p>

		<div id="videos-preview" class="media-preview">
			<?php
			if ( ! empty( $video_ids ) ) {
				$ids_array = explode( ',', $video_ids );
				foreach ( $ids_array as $id ) {
					$video_url = wp_get_attachment_url( $id );
					$thumb_url = wp_get_attachment_image_url( $id, 'thumbnail' );
					if ( $video_url ) {
						echo '<div class="media-preview-item is-video" data-id="' . esc_attr( $id ) . '">';
						if ( $thumb_url ) {
							echo '<img src="' . esc_url( $thumb_url ) . '" alt="">';
						} else {
							echo '<video src="' . esc_url( $video_url ) . '"></video>';
						}
						echo '<button type="button" class="remove-media">&times;</button>';
						echo '</div>';
					}
				}
			}
			?>
		</div>

		<input type="hidden" id="project_videos" name="project_videos" value="<?php echo esc_attr( $video_ids ); ?>">
		<button type="button" class="button" id="add-videos-btn">
			<?php esc_html_e( 'Add Videos', 'portfolio-salma' ); ?>
		</button>
	</div>

	<!-- Project Details Section -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Project Details', 'portfolio-salma' ); ?></label>

		<table class="form-table" style="margin-top: 10px;">
			<tr>
				<th style="width: 120px; padding: 10px 10px 10px 0;">
					<label for="project_year"><?php esc_html_e( 'Year', 'portfolio-salma' ); ?></label>
				</th>
				<td>
					<input type="text" id="project_year" name="project_year" value="<?php echo esc_attr( $project_year ); ?>" placeholder="2024" style="width: 100px;">
				</td>
			</tr>
			<tr>
				<th style="padding: 10px 10px 10px 0;">
					<label for="project_category"><?php esc_html_e( 'Category', 'portfolio-salma' ); ?></label>
				</th>
				<td>
					<input type="text" id="project_category" name="project_category" value="<?php echo esc_attr( $project_category ); ?>" placeholder="Web Design, Animation, UI/UX..." style="width: 300px;">
					<p class="description" style="margin-top: 5px;"><?php esc_html_e( 'Type of project (e.g., Web Design, Animation, Videography)', 'portfolio-salma' ); ?></p>
				</td>
			</tr>
			<tr>
				<th style="padding: 10px 10px 10px 0;">
					<label for="project_tools"><?php esc_html_e( 'Tools', 'portfolio-salma' ); ?></label>
				</th>
				<td>
					<input type="text" id="project_tools" name="project_tools" value="<?php echo esc_attr( $project_tools ); ?>" placeholder="After Effects, Figma, Premiere Pro..." style="width: 300px;">
					<p class="description" style="margin-top: 5px;"><?php esc_html_e( 'Software/tools used (e.g., Figma, After Effects, Blender)', 'portfolio-salma' ); ?></p>
				</td>
			</tr>
			<tr>
				<th style="padding: 10px 10px 10px 0;">
					<label for="project_website"><?php esc_html_e( 'Website URL', 'portfolio-salma' ); ?></label>
				</th>
				<td>
					<input type="url" id="project_website" name="project_website" value="<?php echo esc_url( $project_website ); ?>" placeholder="https://example.com" style="width: 400px;">
					<p class="description" style="margin-top: 5px;"><?php esc_html_e( 'Link to live website or project (optional)', 'portfolio-salma' ); ?></p>
				</td>
			</tr>
			<tr>
				<th style="padding: 10px 10px 10px 0;">
					<label for="project_youtube"><?php esc_html_e( 'YouTube URL', 'portfolio-salma' ); ?></label>
				</th>
				<td>
					<input type="url" id="project_youtube" name="project_youtube" value="<?php echo esc_url( $project_youtube ); ?>" placeholder="https://www.youtube.com/watch?v=..." style="width: 400px;">
					<p class="description" style="margin-top: 5px;"><?php esc_html_e( 'Link to YouTube video for this project (optional)', 'portfolio-salma' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<!-- Color Grading Comparison Section -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Color Grading Comparison (Video Projects)', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Upload two versions of your video for the before/after color grading slider.', 'portfolio-salma' ); ?></p>

		<table class="form-table" style="margin-top: 10px;">
			<tr>
				<th style="width: 150px; padding: 10px 10px 10px 0;">
					<label><?php esc_html_e( 'Color Graded Video', 'portfolio-salma' ); ?></label>
				</th>
				<td>
					<div id="graded-video-preview" style="margin-bottom: 10px;">
						<?php
						if ( ! empty( $video_graded_id ) ) {
							$video_url = wp_get_attachment_url( $video_graded_id );
							if ( $video_url ) {
								echo '<video src="' . esc_url( $video_url ) . '" style="max-width: 300px; max-height: 150px;" controls></video>';
								echo '<br><button type="button" class="button remove-graded-video" style="margin-top: 5px;">Remove</button>';
							}
						}
						?>
					</div>
					<input type="hidden" id="project_video_graded" name="project_video_graded" value="<?php echo esc_attr( $video_graded_id ); ?>">
					<button type="button" class="button" id="add-graded-video-btn">
						<?php esc_html_e( 'Select Color Graded Video', 'portfolio-salma' ); ?>
					</button>
					<p class="description" style="margin-top: 5px;"><?php esc_html_e( 'The final color graded version', 'portfolio-salma' ); ?></p>
				</td>
			</tr>
			<tr>
				<th style="padding: 10px 10px 10px 0;">
					<label><?php esc_html_e( 'LOG Video', 'portfolio-salma' ); ?></label>
				</th>
				<td>
					<div id="log-video-preview" style="margin-bottom: 10px;">
						<?php
						if ( ! empty( $video_log_id ) ) {
							$video_url = wp_get_attachment_url( $video_log_id );
							if ( $video_url ) {
								echo '<video src="' . esc_url( $video_url ) . '" style="max-width: 300px; max-height: 150px;" controls></video>';
								echo '<br><button type="button" class="button remove-log-video" style="margin-top: 5px;">Remove</button>';
							}
						}
						?>
					</div>
					<input type="hidden" id="project_video_log" name="project_video_log" value="<?php echo esc_attr( $video_log_id ); ?>">
					<button type="button" class="button" id="add-log-video-btn">
						<?php esc_html_e( 'Select LOG Video', 'portfolio-salma' ); ?>
					</button>
					<p class="description" style="margin-top: 5px;"><?php esc_html_e( 'The original LOG/flat version for comparison', 'portfolio-salma' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<script>
	jQuery(document).ready(function($) {
		var imagesFrame;
		var videosFrame;

		// Add images
		$('#add-images-btn').on('click', function(e) {
			e.preventDefault();

			if (imagesFrame) {
				imagesFrame.open();
				return;
			}

			imagesFrame = wp.media({
				title: '<?php esc_html_e( 'Select Images', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Add to Gallery', 'portfolio-salma' ); ?>' },
				library: { type: 'image' },
				multiple: true
			});

			imagesFrame.on('select', function() {
				var selection = imagesFrame.state().get('selection');
				var currentIds = $('#project_gallery').val();
				var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

				selection.each(function(attachment) {
					attachment = attachment.toJSON();

					if (idsArray.indexOf(attachment.id.toString()) === -1) {
						idsArray.push(attachment.id);

						var thumbUrl = attachment.sizes && attachment.sizes.thumbnail
							? attachment.sizes.thumbnail.url
							: attachment.url;

						var html = '<div class="media-preview-item" data-id="' + attachment.id + '">';
						html += '<img src="' + thumbUrl + '" alt="">';
						html += '<button type="button" class="remove-media">&times;</button>';
						html += '</div>';

						$('#images-preview').append(html);
					}
				});

				$('#project_gallery').val(idsArray.join(','));
			});

			imagesFrame.open();
		});

		// Add videos
		$('#add-videos-btn').on('click', function(e) {
			e.preventDefault();

			if (videosFrame) {
				videosFrame.open();
				return;
			}

			videosFrame = wp.media({
				title: '<?php esc_html_e( 'Select Videos', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Add to Gallery', 'portfolio-salma' ); ?>' },
				library: { type: 'video' },
				multiple: true
			});

			videosFrame.on('select', function() {
				var selection = videosFrame.state().get('selection');
				var currentIds = $('#project_videos').val();
				var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

				selection.each(function(attachment) {
					attachment = attachment.toJSON();

					if (idsArray.indexOf(attachment.id.toString()) === -1) {
						idsArray.push(attachment.id);

						var html = '<div class="media-preview-item is-video" data-id="' + attachment.id + '">';
						html += '<video src="' + attachment.url + '"></video>';
						html += '<button type="button" class="remove-media">&times;</button>';
						html += '</div>';

						$('#videos-preview').append(html);
					}
				});

				$('#project_videos').val(idsArray.join(','));
			});

			videosFrame.open();
		});

		// Remove image
		$('#images-preview').on('click', '.remove-media', function() {
			var item = $(this).closest('.media-preview-item');
			var removeId = item.data('id').toString();
			var currentIds = $('#project_gallery').val();
			var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

			idsArray = idsArray.filter(function(id) {
				return id !== removeId;
			});

			$('#project_gallery').val(idsArray.join(','));
			item.remove();
		});

		// Remove video
		$('#videos-preview').on('click', '.remove-media', function() {
			var item = $(this).closest('.media-preview-item');
			var removeId = item.data('id').toString();
			var currentIds = $('#project_videos').val();
			var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

			idsArray = idsArray.filter(function(id) {
				return id !== removeId;
			});

			$('#project_videos').val(idsArray.join(','));
			item.remove();
		});

		// Color Graded Video
		var gradedFrame;
		$('#add-graded-video-btn').on('click', function(e) {
			e.preventDefault();

			if (gradedFrame) {
				gradedFrame.open();
				return;
			}

			gradedFrame = wp.media({
				title: '<?php esc_html_e( 'Select Color Graded Video', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Use this video', 'portfolio-salma' ); ?>' },
				library: { type: 'video' },
				multiple: false
			});

			gradedFrame.on('select', function() {
				var attachment = gradedFrame.state().get('selection').first().toJSON();
				$('#project_video_graded').val(attachment.id);
				$('#graded-video-preview').html(
					'<video src="' + attachment.url + '" style="max-width: 300px; max-height: 150px;" controls></video>' +
					'<br><button type="button" class="button remove-graded-video" style="margin-top: 5px;">Remove</button>'
				);
			});

			gradedFrame.open();
		});

		// Remove graded video
		$(document).on('click', '.remove-graded-video', function() {
			$('#project_video_graded').val('');
			$('#graded-video-preview').html('');
		});

		// LOG Video
		var logFrame;
		$('#add-log-video-btn').on('click', function(e) {
			e.preventDefault();

			if (logFrame) {
				logFrame.open();
				return;
			}

			logFrame = wp.media({
				title: '<?php esc_html_e( 'Select LOG Video', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Use this video', 'portfolio-salma' ); ?>' },
				library: { type: 'video' },
				multiple: false
			});

			logFrame.on('select', function() {
				var attachment = logFrame.state().get('selection').first().toJSON();
				$('#project_video_log').val(attachment.id);
				$('#log-video-preview').html(
					'<video src="' + attachment.url + '" style="max-width: 300px; max-height: 150px;" controls></video>' +
					'<br><button type="button" class="button remove-log-video" style="margin-top: 5px;">Remove</button>'
				);
			});

			logFrame.open();
		});

		// Remove LOG video
		$(document).on('click', '.remove-log-video', function() {
			$('#project_video_log').val('');
			$('#log-video-preview').html('');
		});
	});
	</script>
	<?php
}

/**
 * Save meta box data
 */
function portfolio_salma_save_project_meta( $post_id ) {
	if ( ! isset( $_POST['portfolio_salma_project_media_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['portfolio_salma_project_media_nonce'], 'portfolio_salma_project_media' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save images
	if ( isset( $_POST['project_gallery'] ) ) {
		$gallery_ids = sanitize_text_field( $_POST['project_gallery'] );
		update_post_meta( $post_id, '_project_gallery', $gallery_ids );
	}

	// Save videos
	if ( isset( $_POST['project_videos'] ) ) {
		$video_ids = sanitize_text_field( $_POST['project_videos'] );
		update_post_meta( $post_id, '_project_videos', $video_ids );
	}

	// Save project details
	if ( isset( $_POST['project_year'] ) ) {
		$year = sanitize_text_field( $_POST['project_year'] );
		update_post_meta( $post_id, '_project_year', $year );
	}

	if ( isset( $_POST['project_category'] ) ) {
		$category = sanitize_text_field( $_POST['project_category'] );
		update_post_meta( $post_id, '_project_category', $category );
	}

	if ( isset( $_POST['project_tools'] ) ) {
		$tools = sanitize_text_field( $_POST['project_tools'] );
		update_post_meta( $post_id, '_project_tools', $tools );
	}

	if ( isset( $_POST['project_website'] ) ) {
		$website = esc_url_raw( $_POST['project_website'] );
		update_post_meta( $post_id, '_project_website', $website );
	}

	if ( isset( $_POST['project_youtube'] ) ) {
		$youtube = esc_url_raw( $_POST['project_youtube'] );
		update_post_meta( $post_id, '_project_youtube', $youtube );
	}

	// Save color grading videos
	if ( isset( $_POST['project_video_graded'] ) ) {
		$video_graded = absint( $_POST['project_video_graded'] );
		update_post_meta( $post_id, '_project_video_graded', $video_graded );
	}

	if ( isset( $_POST['project_video_log'] ) ) {
		$video_log = absint( $_POST['project_video_log'] );
		update_post_meta( $post_id, '_project_video_log', $video_log );
	}
}
add_action( 'save_post_project', 'portfolio_salma_save_project_meta' );

/**
 * Get project gallery images
 *
 * @param int $post_id The project post ID.
 * @return array Array of image URLs.
 */
function portfolio_salma_get_project_gallery( $post_id ) {
	$gallery_ids = get_post_meta( $post_id, '_project_gallery', true );
	$images      = array();

	if ( ! empty( $gallery_ids ) ) {
		$ids_array = explode( ',', $gallery_ids );
		foreach ( $ids_array as $id ) {
			$img_url = wp_get_attachment_image_url( $id, 'large' );
			if ( $img_url ) {
				$images[] = $img_url;
			}
		}
	}

	return $images;
}

/**
 * Get project videos
 *
 * @param int $post_id The project post ID.
 * @return array Array of video URLs.
 */
function portfolio_salma_get_project_videos( $post_id ) {
	$video_ids = get_post_meta( $post_id, '_project_videos', true );
	$videos    = array();

	if ( ! empty( $video_ids ) ) {
		$ids_array = explode( ',', $video_ids );
		foreach ( $ids_array as $id ) {
			$video_url = wp_get_attachment_url( $id );
			if ( $video_url ) {
				$videos[] = $video_url;
			}
		}
	}

	return $videos;
}

/**
 * Get all project media (images + videos combined)
 *
 * @param int $post_id The project post ID.
 * @return array Array of media items with type and url.
 */
function portfolio_salma_get_project_media( $post_id ) {
	$media = array();

	// Get images first
	$images = portfolio_salma_get_project_gallery( $post_id );
	foreach ( $images as $url ) {
		$media[] = array(
			'type' => 'image',
			'url'  => $url,
		);
	}

	// Then videos
	$videos = portfolio_salma_get_project_videos( $post_id );
	foreach ( $videos as $url ) {
		$media[] = array(
			'type' => 'video',
			'url'  => $url,
		);
	}

	return $media;
}

/**
 * Get project details (year, category, tools, website, youtube, color grading videos)
 *
 * @param int $post_id The project post ID.
 * @return array Associative array with project details.
 */
function portfolio_salma_get_project_details( $post_id ) {
	$video_graded_id = get_post_meta( $post_id, '_project_video_graded', true );
	$video_log_id    = get_post_meta( $post_id, '_project_video_log', true );

	return array(
		'year'         => get_post_meta( $post_id, '_project_year', true ),
		'category'     => get_post_meta( $post_id, '_project_category', true ),
		'tools'        => get_post_meta( $post_id, '_project_tools', true ),
		'website'      => get_post_meta( $post_id, '_project_website', true ),
		'youtube'      => get_post_meta( $post_id, '_project_youtube', true ),
		'video_graded' => $video_graded_id ? wp_get_attachment_url( $video_graded_id ) : '',
		'video_log'    => $video_log_id ? wp_get_attachment_url( $video_log_id ) : '',
	);
}
