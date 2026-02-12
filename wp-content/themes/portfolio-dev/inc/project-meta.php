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

	// Video-specific fields (for video projects)
	$color_graded_id = get_post_meta( $post->ID, '_project_color_graded', true );
	$log_image_id    = get_post_meta( $post->ID, '_project_log', true );
	$bts_ids         = get_post_meta( $post->ID, '_project_bts', true );
	$extract_ids     = get_post_meta( $post->ID, '_project_extracts', true );
	$scouting_ids    = get_post_meta( $post->ID, '_project_scouting', true );

	// Photography-specific fields
	$material_image_id = get_post_meta( $post->ID, '_project_material_image', true );
	$lab_sketch_ids    = get_post_meta( $post->ID, '_project_lab_sketches', true );
	$lab_names         = get_post_meta( $post->ID, '_project_lab_names', true );

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

	<!-- Color Graded Image (For Video Projects) -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Color Graded Image', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Add the color graded version for before/after comparison slider (optional, for video projects only).', 'portfolio-salma' ); ?></p>

		<div id="color-graded-preview" class="media-preview">
			<?php
			if ( ! empty( $color_graded_id ) ) {
				$img_url = wp_get_attachment_image_url( $color_graded_id, 'thumbnail' );
				if ( $img_url ) {
					echo '<div class="media-preview-item" data-id="' . esc_attr( $color_graded_id ) . '">';
					echo '<img src="' . esc_url( $img_url ) . '" alt="">';
					echo '<button type="button" class="remove-media">&times;</button>';
					echo '</div>';
				}
			}
			?>
		</div>

		<input type="hidden" id="project_color_graded" name="project_color_graded" value="<?php echo esc_attr( $color_graded_id ); ?>">
		<button type="button" class="button" id="add-color-graded-btn">
			<?php esc_html_e( 'Set Color Graded Image', 'portfolio-salma' ); ?>
		</button>
	</div>

	<!-- LOG Image (For Video Projects) -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'LOG Image', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Add the LOG version for before/after comparison slider (optional, for video projects only).', 'portfolio-salma' ); ?></p>

		<div id="log-image-preview" class="media-preview">
			<?php
			if ( ! empty( $log_image_id ) ) {
				$img_url = wp_get_attachment_image_url( $log_image_id, 'thumbnail' );
				if ( $img_url ) {
					echo '<div class="media-preview-item" data-id="' . esc_attr( $log_image_id ) . '">';
					echo '<img src="' . esc_url( $img_url ) . '" alt="">';
					echo '<button type="button" class="remove-media">&times;</button>';
					echo '</div>';
				}
			}
			?>
		</div>

		<input type="hidden" id="project_log" name="project_log" value="<?php echo esc_attr( $log_image_id ); ?>">
		<button type="button" class="button" id="add-log-btn">
			<?php esc_html_e( 'Set LOG Image', 'portfolio-salma' ); ?>
		</button>
	</div>

	<!-- Behind The Scenes Images -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Behind The Scenes Images', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Add behind-the-scenes images (optional). They will appear in a carousel at the bottom of the project page.', 'portfolio-salma' ); ?></p>

		<div id="bts-preview" class="media-preview">
			<?php
			if ( ! empty( $bts_ids ) ) {
				$ids_array = explode( ',', $bts_ids );
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

		<input type="hidden" id="project_bts" name="project_bts" value="<?php echo esc_attr( $bts_ids ); ?>">
		<button type="button" class="button" id="add-bts-btn">
			<?php esc_html_e( 'Add BTS Images', 'portfolio-salma' ); ?>
		</button>
	</div>

	<!-- Film Extracts (For Video Projects) -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Film Extracts', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Add up to 4 short video clips that will autoplay as loops (like GIFs). Upload .mov or .mp4 files.', 'portfolio-salma' ); ?></p>

		<div id="extracts-preview" class="media-preview">
			<?php
			if ( ! empty( $extract_ids ) ) {
				$ids_array = explode( ',', $extract_ids );
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

		<input type="hidden" id="project_extracts" name="project_extracts" value="<?php echo esc_attr( $extract_ids ); ?>">
		<button type="button" class="button" id="add-extracts-btn">
			<?php esc_html_e( 'Add Film Extracts', 'portfolio-salma' ); ?>
		</button>
	</div>

	<!-- Location Scouting Images (For Video Projects) -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Location Scouting Images', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Add location scouting photos. They will appear scattered with location pins on the project page.', 'portfolio-salma' ); ?></p>

		<div id="scouting-preview" class="media-preview">
			<?php
			if ( ! empty( $scouting_ids ) ) {
				$ids_array = explode( ',', $scouting_ids );
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

		<input type="hidden" id="project_scouting" name="project_scouting" value="<?php echo esc_attr( $scouting_ids ); ?>">
		<button type="button" class="button" id="add-scouting-btn">
			<?php esc_html_e( 'Add Scouting Images', 'portfolio-salma' ); ?>
		</button>
	</div>

	<!-- Material/Equipment Image (For Photography Projects) -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Material / Equipment Photo', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'A photo of your camera/equipment. Displayed next to the project description on photography pages.', 'portfolio-salma' ); ?></p>

		<div id="material-preview" class="media-preview">
			<?php
			if ( ! empty( $material_image_id ) ) {
				$img_url = wp_get_attachment_image_url( $material_image_id, 'thumbnail' );
				if ( $img_url ) {
					echo '<div class="media-preview-item" data-id="' . esc_attr( $material_image_id ) . '">';
					echo '<img src="' . esc_url( $img_url ) . '" alt="">';
					echo '<button type="button" class="remove-media">&times;</button>';
					echo '</div>';
				}
			}
			?>
		</div>

		<input type="hidden" id="project_material_image" name="project_material_image" value="<?php echo esc_attr( $material_image_id ); ?>">
		<button type="button" class="button" id="add-material-btn">
			<?php esc_html_e( 'Add Material Photo', 'portfolio-salma' ); ?>
		</button>
	</div>

	<!-- Lab Sketches (For Photography Projects) -->
	<div class="project-meta-section">
		<label><?php esc_html_e( 'Lab Sketches', 'portfolio-salma' ); ?></label>
		<p class="description"><?php esc_html_e( 'Add sketch illustrations of film development labs. Enter lab names as a comma-separated list matching the order of images.', 'portfolio-salma' ); ?></p>

		<div id="lab-sketches-preview" class="media-preview">
			<?php
			if ( ! empty( $lab_sketch_ids ) ) {
				$ids_array = explode( ',', $lab_sketch_ids );
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

		<input type="hidden" id="project_lab_sketches" name="project_lab_sketches" value="<?php echo esc_attr( $lab_sketch_ids ); ?>">
		<button type="button" class="button" id="add-lab-sketches-btn">
			<?php esc_html_e( 'Add Lab Sketches', 'portfolio-salma' ); ?>
		</button>

		<div style="margin-top: 10px;">
			<label for="project_lab_names"><?php esc_html_e( 'Lab Names', 'portfolio-salma' ); ?></label>
			<input type="text" id="project_lab_names" name="project_lab_names" value="<?php echo esc_attr( $lab_names ); ?>" placeholder="Photo Ueno, Yellow Jacket..." style="width: 400px;">
			<p class="description"><?php esc_html_e( 'Comma-separated lab names, matching the order of sketch images above.', 'portfolio-salma' ); ?></p>
		</div>
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

	<script>
	jQuery(document).ready(function($) {
		var imagesFrame;
		var videosFrame;
		var colorGradedFrame;
		var logFrame;
		var btsFrame;

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

		// Add Color Graded image
		$('#add-color-graded-btn').on('click', function(e) {
			e.preventDefault();

			if (colorGradedFrame) {
				colorGradedFrame.open();
				return;
			}

			colorGradedFrame = wp.media({
				title: '<?php esc_html_e( 'Select Color Graded Image', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Set Image', 'portfolio-salma' ); ?>' },
				library: { type: 'image' },
				multiple: false
			});

			colorGradedFrame.on('select', function() {
				var attachment = colorGradedFrame.state().get('selection').first().toJSON();

				$('#project_color_graded').val(attachment.id);

				var thumbUrl = attachment.sizes && attachment.sizes.thumbnail
					? attachment.sizes.thumbnail.url
					: attachment.url;

				var html = '<div class="media-preview-item" data-id="' + attachment.id + '">';
				html += '<img src="' + thumbUrl + '" alt="">';
				html += '<button type="button" class="remove-media">&times;</button>';
				html += '</div>';

				$('#color-graded-preview').html(html);
			});

			colorGradedFrame.open();
		});

		// Remove Color Graded image
		$('#color-graded-preview').on('click', '.remove-media', function() {
			$('#project_color_graded').val('');
			$('#color-graded-preview').empty();
		});

		// Add LOG image
		$('#add-log-btn').on('click', function(e) {
			e.preventDefault();

			if (logFrame) {
				logFrame.open();
				return;
			}

			logFrame = wp.media({
				title: '<?php esc_html_e( 'Select LOG Image', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Set Image', 'portfolio-salma' ); ?>' },
				library: { type: 'image' },
				multiple: false
			});

			logFrame.on('select', function() {
				var attachment = logFrame.state().get('selection').first().toJSON();

				$('#project_log').val(attachment.id);

				var thumbUrl = attachment.sizes && attachment.sizes.thumbnail
					? attachment.sizes.thumbnail.url
					: attachment.url;

				var html = '<div class="media-preview-item" data-id="' + attachment.id + '">';
				html += '<img src="' + thumbUrl + '" alt="">';
				html += '<button type="button" class="remove-media">&times;</button>';
				html += '</div>';

				$('#log-image-preview').html(html);
			});

			logFrame.open();
		});

		// Remove LOG image
		$('#log-image-preview').on('click', '.remove-media', function() {
			$('#project_log').val('');
			$('#log-image-preview').empty();
		});

		// Add BTS images
		$('#add-bts-btn').on('click', function(e) {
			e.preventDefault();

			if (btsFrame) {
				btsFrame.open();
				return;
			}

			btsFrame = wp.media({
				title: '<?php esc_html_e( 'Select BTS Images', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Add to Gallery', 'portfolio-salma' ); ?>' },
				library: { type: 'image' },
				multiple: true
			});

			btsFrame.on('select', function() {
				var selection = btsFrame.state().get('selection');
				var currentIds = $('#project_bts').val();
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

						$('#bts-preview').append(html);
					}
				});

				$('#project_bts').val(idsArray.join(','));
			});

			btsFrame.open();
		});

		// Remove BTS image
		$('#bts-preview').on('click', '.remove-media', function() {
			var item = $(this).closest('.media-preview-item');
			var removeId = item.data('id').toString();
			var currentIds = $('#project_bts').val();
			var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

			idsArray = idsArray.filter(function(id) {
				return id !== removeId;
			});

			$('#project_bts').val(idsArray.join(','));
			item.remove();
		});

		// Add Film Extracts
		var extractsFrame;
		$('#add-extracts-btn').on('click', function(e) {
			e.preventDefault();

			if (extractsFrame) {
				extractsFrame.open();
				return;
			}

			extractsFrame = wp.media({
				title: '<?php esc_html_e( 'Select Film Extracts (max 4)', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Add Extracts', 'portfolio-salma' ); ?>' },
				library: { type: 'video' },
				multiple: true
			});

			extractsFrame.on('select', function() {
				var selection = extractsFrame.state().get('selection');
				var currentIds = $('#project_extracts').val();
				var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

				selection.each(function(attachment) {
					if (idsArray.length >= 4) return;
					attachment = attachment.toJSON();

					if (idsArray.indexOf(attachment.id.toString()) === -1) {
						idsArray.push(attachment.id);

						var html = '<div class="media-preview-item is-video" data-id="' + attachment.id + '">';
						html += '<video src="' + attachment.url + '"></video>';
						html += '<button type="button" class="remove-media">&times;</button>';
						html += '</div>';

						$('#extracts-preview').append(html);
					}
				});

				$('#project_extracts').val(idsArray.join(','));
			});

			extractsFrame.open();
		});

		// Remove Extract
		$('#extracts-preview').on('click', '.remove-media', function() {
			var item = $(this).closest('.media-preview-item');
			var removeId = item.data('id').toString();
			var currentIds = $('#project_extracts').val();
			var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

			idsArray = idsArray.filter(function(id) {
				return id !== removeId;
			});

			$('#project_extracts').val(idsArray.join(','));
			item.remove();
		});

		// Add Scouting Images
		var scoutingFrame;
		$('#add-scouting-btn').on('click', function(e) {
			e.preventDefault();

			if (scoutingFrame) {
				scoutingFrame.open();
				return;
			}

			scoutingFrame = wp.media({
				title: '<?php esc_html_e( 'Select Location Scouting Images', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Add Images', 'portfolio-salma' ); ?>' },
				library: { type: 'image' },
				multiple: true
			});

			scoutingFrame.on('select', function() {
				var selection = scoutingFrame.state().get('selection');
				var currentIds = $('#project_scouting').val();
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

						$('#scouting-preview').append(html);
					}
				});

				$('#project_scouting').val(idsArray.join(','));
			});

			scoutingFrame.open();
		});

		// Remove Scouting image
		$('#scouting-preview').on('click', '.remove-media', function() {
			var item = $(this).closest('.media-preview-item');
			var removeId = item.data('id').toString();
			var currentIds = $('#project_scouting').val();
			var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

			idsArray = idsArray.filter(function(id) {
				return id !== removeId;
			});

			$('#project_scouting').val(idsArray.join(','));
			item.remove();
		});

		// Add Material Image
		var materialFrame;
		$('#add-material-btn').on('click', function(e) {
			e.preventDefault();

			if (materialFrame) {
				materialFrame.open();
				return;
			}

			materialFrame = wp.media({
				title: '<?php esc_html_e( 'Select Material / Equipment Photo', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Use This Image', 'portfolio-salma' ); ?>' },
				library: { type: 'image' },
				multiple: false
			});

			materialFrame.on('select', function() {
				var attachment = materialFrame.state().get('selection').first().toJSON();
				var thumbUrl = attachment.sizes && attachment.sizes.thumbnail
					? attachment.sizes.thumbnail.url
					: attachment.url;

				$('#material-preview').html(
					'<div class="media-preview-item" data-id="' + attachment.id + '">' +
					'<img src="' + thumbUrl + '" alt="">' +
					'<button type="button" class="remove-media">&times;</button>' +
					'</div>'
				);
				$('#project_material_image').val(attachment.id);
			});

			materialFrame.open();
		});

		// Remove Material Image
		$('#material-preview').on('click', '.remove-media', function() {
			$(this).closest('.media-preview-item').remove();
			$('#project_material_image').val('');
		});

		// Add Lab Sketches
		var labSketchesFrame;
		$('#add-lab-sketches-btn').on('click', function(e) {
			e.preventDefault();

			if (labSketchesFrame) {
				labSketchesFrame.open();
				return;
			}

			labSketchesFrame = wp.media({
				title: '<?php esc_html_e( 'Select Lab Sketch Images', 'portfolio-salma' ); ?>',
				button: { text: '<?php esc_html_e( 'Add Sketches', 'portfolio-salma' ); ?>' },
				library: { type: 'image' },
				multiple: true
			});

			labSketchesFrame.on('select', function() {
				var selection = labSketchesFrame.state().get('selection');
				var currentIds = $('#project_lab_sketches').val();
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

						$('#lab-sketches-preview').append(html);
					}
				});

				$('#project_lab_sketches').val(idsArray.join(','));
			});

			labSketchesFrame.open();
		});

		// Remove Lab Sketch
		$('#lab-sketches-preview').on('click', '.remove-media', function() {
			var item = $(this).closest('.media-preview-item');
			var removeId = item.data('id').toString();
			var currentIds = $('#project_lab_sketches').val();
			var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

			idsArray = idsArray.filter(function(id) {
				return id !== removeId;
			});

			$('#project_lab_sketches').val(idsArray.join(','));
			item.remove();
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

	// Save video-specific fields
	if ( isset( $_POST['project_color_graded'] ) ) {
		$color_graded_id = absint( $_POST['project_color_graded'] );
		update_post_meta( $post_id, '_project_color_graded', $color_graded_id );
	} else {
		delete_post_meta( $post_id, '_project_color_graded' );
	}

	if ( isset( $_POST['project_log'] ) ) {
		$log_id = absint( $_POST['project_log'] );
		update_post_meta( $post_id, '_project_log', $log_id );
	} else {
		delete_post_meta( $post_id, '_project_log' );
	}

	if ( isset( $_POST['project_bts'] ) ) {
		$bts_ids = sanitize_text_field( $_POST['project_bts'] );
		update_post_meta( $post_id, '_project_bts', $bts_ids );
	} else {
		delete_post_meta( $post_id, '_project_bts' );
	}

	// Save film extracts
	if ( isset( $_POST['project_extracts'] ) ) {
		$extract_ids = sanitize_text_field( $_POST['project_extracts'] );
		update_post_meta( $post_id, '_project_extracts', $extract_ids );
	} else {
		delete_post_meta( $post_id, '_project_extracts' );
	}

	// Save location scouting
	if ( isset( $_POST['project_scouting'] ) ) {
		$scouting_ids = sanitize_text_field( $_POST['project_scouting'] );
		update_post_meta( $post_id, '_project_scouting', $scouting_ids );
	} else {
		delete_post_meta( $post_id, '_project_scouting' );
	}

	// Save material image
	if ( isset( $_POST['project_material_image'] ) && ! empty( $_POST['project_material_image'] ) ) {
		$material_id = absint( $_POST['project_material_image'] );
		update_post_meta( $post_id, '_project_material_image', $material_id );
	} else {
		delete_post_meta( $post_id, '_project_material_image' );
	}

	// Save lab sketches
	if ( isset( $_POST['project_lab_sketches'] ) ) {
		$lab_ids = sanitize_text_field( $_POST['project_lab_sketches'] );
		update_post_meta( $post_id, '_project_lab_sketches', $lab_ids );
	} else {
		delete_post_meta( $post_id, '_project_lab_sketches' );
	}

	// Save lab names
	if ( isset( $_POST['project_lab_names'] ) ) {
		$lab_names = sanitize_text_field( $_POST['project_lab_names'] );
		update_post_meta( $post_id, '_project_lab_names', $lab_names );
	} else {
		delete_post_meta( $post_id, '_project_lab_names' );
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
 * Get project details (year, category, tools, website, youtube)
 *
 * @param int $post_id The project post ID.
 * @return array Associative array with year, category, tools, website, youtube.
 */
function portfolio_salma_get_project_details( $post_id ) {
	return array(
		'year'     => get_post_meta( $post_id, '_project_year', true ),
		'category' => get_post_meta( $post_id, '_project_category', true ),
		'tools'    => get_post_meta( $post_id, '_project_tools', true ),
		'website'  => get_post_meta( $post_id, '_project_website', true ),
		'youtube'  => get_post_meta( $post_id, '_project_youtube', true ),
	);
}

/**
 * Get Color Graded image URL
 *
 * @param int $post_id The project post ID.
 * @return string|false Image URL or false if not set.
 */
function portfolio_salma_get_color_graded_image( $post_id ) {
	$image_id = get_post_meta( $post_id, '_project_color_graded', true );
	if ( $image_id ) {
		return wp_get_attachment_image_url( $image_id, 'full' );
	}
	return false;
}

/**
 * Get LOG image URL
 *
 * @param int $post_id The project post ID.
 * @return string|false Image URL or false if not set.
 */
function portfolio_salma_get_log_image( $post_id ) {
	$image_id = get_post_meta( $post_id, '_project_log', true );
	if ( $image_id ) {
		return wp_get_attachment_image_url( $image_id, 'full' );
	}
	return false;
}

/**
 * Get BTS (Behind The Scenes) images
 *
 * @param int $post_id The project post ID.
 * @return array Array of image URLs.
 */
function portfolio_salma_get_bts_images( $post_id ) {
	$bts_ids = get_post_meta( $post_id, '_project_bts', true );
	$images  = array();

	if ( ! empty( $bts_ids ) ) {
		$ids_array = explode( ',', $bts_ids );
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
 * Get film extract video URLs
 *
 * @param int $post_id The project post ID.
 * @return array Array of video URLs (max 4).
 */
function portfolio_salma_get_extracts( $post_id ) {
	$extract_ids = get_post_meta( $post_id, '_project_extracts', true );
	$videos      = array();

	if ( ! empty( $extract_ids ) ) {
		$ids_array = array_slice( explode( ',', $extract_ids ), 0, 4 );
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
 * Get location scouting images
 *
 * @param int $post_id The project post ID.
 * @return array Array of image URLs.
 */
function portfolio_salma_get_scouting_images( $post_id ) {
	$scouting_ids = get_post_meta( $post_id, '_project_scouting', true );
	$images       = array();

	if ( ! empty( $scouting_ids ) ) {
		$ids_array = explode( ',', $scouting_ids );
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
 * Get material/equipment image URL
 *
 * @param int $post_id The project post ID.
 * @return string|false Image URL or false.
 */
function portfolio_salma_get_material_image( $post_id ) {
	$material_id = get_post_meta( $post_id, '_project_material_image', true );

	if ( ! empty( $material_id ) ) {
		return wp_get_attachment_image_url( $material_id, 'large' );
	}

	return false;
}

/**
 * Get lab sketches with names
 *
 * @param int $post_id The project post ID.
 * @return array Array of [ 'url' => ..., 'name' => ... ].
 */
function portfolio_salma_get_lab_sketches( $post_id ) {
	$sketch_ids = get_post_meta( $post_id, '_project_lab_sketches', true );
	$lab_names  = get_post_meta( $post_id, '_project_lab_names', true );
	$sketches   = array();

	if ( ! empty( $sketch_ids ) ) {
		$ids_array   = explode( ',', $sketch_ids );
		$names_array = ! empty( $lab_names ) ? array_map( 'trim', explode( ',', $lab_names ) ) : array();

		foreach ( $ids_array as $index => $id ) {
			$img_url = wp_get_attachment_image_url( $id, 'large' );
			if ( $img_url ) {
				$sketches[] = array(
					'url'  => $img_url,
					'name' => isset( $names_array[ $index ] ) ? $names_array[ $index ] : '',
				);
			}
		}
	}

	return $sketches;
}
