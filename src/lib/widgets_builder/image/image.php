<?php
/**
 * Add TinyMCE Widget. This has been forked from http://wordpress.org/extend/plugins/image-widget/.
 * All copyright notices for Modern Tribe have remained intact. 
 * IDs, classes and layout have been changed to best meet the needs of Think Up Themes Ltd. 
 * Change have been made within the restrictions of the GPL license under which the Modern Tribe Image Widget has been released.
 *
 * @package ThinkUpThemes
 */


//----------------------------------------------------------------------------------
//	Image Widget
//----------------------------------------------------------------------------------

class thinkup_builder_imagetheme extends WP_Widget {

	const VERSION = '4.0.6';
	const CUSTOM_IMAGE_SIZE_SLUG = 'thinkup_builder_imagetheme_custom';

	// Register widget description.
	public function __construct() {
		$widget_ops = array( 'classname' => 'thinkup_builder_imagetheme', 'description' => __( 'Add an image from the media library.', 'sento' ) );
		$control_ops = array( 'id_base' => 'thinkup_builder_imagetheme' );
		parent::__construct('thinkup_builder_imagetheme', __('Image', 'sento'), $widget_ops, $control_ops);

		// Add scripts to admin area
		add_action('admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_setup' ) );
	}

	//Enqueue all the javascript.
	function admin_setup() {
		wp_enqueue_media();
		wp_enqueue_script( 'thinkup-builder-image', get_template_directory_uri() . '/lib/widgets_builder/image/js/image.js', array( 'jquery', 'media-upload' ), time() );

		wp_localize_script( 'thinkup-builder-image', 'ThinkupBuilderImage', array(
			'frame_title' => __( 'Select an Image', 'sento' ),
			'button_title' => __( 'Insert Into Widget', 'sento' ),
		) );
	}

	//Enqueue all the css.
	function admin_head() {

		echo '<style type="text/css">',
			 '.uploader input.button { width: 100%; height: 34px; line-height: 33px; margin: 8px 0; }',
			 '.thinkup_builder_imagetheme .aligncenter { display: block; margin-left: auto !important; margin-right: auto !important; }',
			 '.thinkup_builder_imagetheme { overflow: hidden; max-height: 300px; }',
			 '.thinkup_builder_imagetheme img { width: 100%; height: auto; margin: 10px 0; }',
			 '</style>';
	}

	// Add widget structure to Admin area.
	function form( $instance ) {

		$default_entries = array( 
			'title'           => '', 
			'uploader_button' => '', 
			'attachment_id'   => '', 
			'imageurl'        => '', 
			'size'            => '',
			'align'           => '',
			'link'            => '', 
			'overlay_enable'  => '',
			'overlay_icon'    => '',
			'lightbox'        => '', 
			'text_alt'        => '', 
			'animate'         => '', 
			'delay'           => '', 	
		);
		$instance = wp_parse_args( (array) $instance, $default_entries );

		$align          = $instance['align'];
		$overlay_enable = $instance['overlay_enable'];
		$overlay_icon   = $instance['overlay_icon'];
		$lightbox       = $instance['lightbox'];
		$text_alt       = $instance['text_alt'];
		$animate        = $instance['animate'];
		$delay          = $instance['delay'];
//		$instance       = wp_parse_args( (array) $instance, self::get_defaults() );

		$id_prefix = $this->get_field_id('');

		echo '<p style="margin-bottom: 20px;"><label for="' . $this->get_field_id('title') . '" style="display: inline-block;width: 100px;" >' . __('Widget Title', 'sento') . ':</label>',
			 '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr(strip_tags($instance['title'])) . '" style="display: inline-block;width: 200px;margin: 0;" /></p>';

		echo '<div class="uploader">',
			 '<input type="submit" class="button" name="' . $this->get_field_name('uploader_button') . '" id="' . $this->get_field_id('uploader_button') . '" value="' . __('Select an Image', 'sento') . '" onclick="imageWidget.uploader( &#39;' . $id_prefix . '&#39;, &#39;' . $id_prefix . '&#39; ); return false;" />',
			 '<div class="thinkup_builder_imagetheme" id="' . $this->get_field_id('preview') . '">',
			 wp_get_attachment_image( $instance['attachment_id'], $instance['size'] ),
			 '<img class="image-builder-urlout" src="" />',
			 '</div>',
			 '<input type="hidden" id="' . $this->get_field_id('attachment_id') .'" name="' . $this->get_field_name('attachment_id') .'" value="' . abs($instance['attachment_id']) .'" />',
			 '<input type="hidden" id="' . $this->get_field_id('imageurl') . '" class="image-builder-urlin" name="' . $this->get_field_name('imageurl') . '" value="' . $instance['imageurl'] . '" />',
			 '</div>',
			 '<span clear="all" /></span>';

		echo '<div id="' . $this->get_field_id('fields') . '" class="image-builder-fields">';
		echo '<div id="' . $this->get_field_id('custom_size_selector') . '" class="image-image-size" >',
			 '<p style="margin-bottom: 20px;"><label for="' . $this->get_field_id('size') . '" style="display: inline-block;width: 150px;">' . __('Size', 'sento') . ':</label>',
			 '<select name="' . $this->get_field_name('size') . '" id="' . $this->get_field_id('size') . '" onChange="imageWidget.toggleSizes( &#39;' . $id_prefix . '&#39;, &#39;' . $id_prefix . '&#39; );" style="display: inline-block;width: 200px;margin: 0;">';

					// Note: this is dumb. We shouldn't need to have to do this. There should really be a centralized function in core code for this.
					$possible_sizes = apply_filters( 'image_size_names_choose', array(
						'full'      => __('Full Size', 'sento'),
						'thumbnail' => __('Thumbnail', 'sento'),
						'medium'    => __('Medium', 'sento'),
						'large'     => __('Large', 'sento'),
					) );
	//				$possible_sizes[self::CUSTOM_IMAGE_SIZE_SLUG] = __('Custom', 'sento');

					foreach( $possible_sizes as $size_key => $size_label ) {
						echo '<option value="' . $size_key . '" .' . selected( $instance['size'], $size_key ) . '>' . $size_label . '</option>';
					}
		echo '</select>',
			 '</p>',
			 '</div>',
			 '</div>';

		echo '<div id="' . $this->get_field_id('custom_size_fields') . '">';

		echo '<p><label for="' . $this->get_field_id('align') . '" style="display: inline-block;width: 150px;">Alignment:</label>
			<select name="' . $this->get_field_name('align') . '" id="' . $this->get_field_id('align') . '" style="display: inline-block;width: 200px;margin: 0;">
			<option '; ?><?php if($align == "auto") { echo "selected"; } ?><?php echo ' value="auto">Auto</option>
			<option '; ?><?php if($align == "left") { echo "selected"; } ?><?php echo ' value="left">Left</option>
			<option '; ?><?php if($align == "right") { echo "selected"; } ?><?php echo ' value="right">Right</option>
			<option '; ?><?php if($align == "center") { echo "selected"; } ?><?php echo ' value="center">Center</option>
			</select>
		</p>';

		echo '<p><label for="' . $this->get_field_id('link') . '" style="display: inline-block;width: 150px;" >' . __('Link', 'sento') . ':</label>',
			 '<input class="widefat" id="' . $this->get_field_id('link') . '" name="' . $this->get_field_name('link') . '" type="text" value="' . esc_attr(strip_tags($instance['link'])) . '" style="display: inline-block;width: 200px;margin: 0;" /><br />';

		echo '</div>';

		echo '<p><label for="' . $this->get_field_id('overlay_enable') . '" style="display: inline-block;width: 150px;">Enable Overlay:</label>
			<select name="' . $this->get_field_name('overlay_enable') . '" id="' . $this->get_field_id('overlay_enable') . '" style="display: inline-block;width: 200px;margin: 0;">
			<option '; ?><?php if($overlay_enable == "off") { echo "selected"; } ?><?php echo ' value="off">Off</option>
			<option '; ?><?php if($overlay_enable == "on") { echo "selected"; } ?><?php echo ' value="on">On</option>
			</select>
		</p>';

		echo '<p><label for="' . $this->get_field_id('overlay_icon') . '" style="display: inline-block;width: 150px;">Icon:</label>
			<select name="' . $this->get_field_name('overlay_icon') . '" id="' . $this->get_field_id('overlay_icon') . '" style="display: inline-block;width: 200px;margin: 0;">
			<option '; ?><?php if($overlay_icon == "hover-plus") { echo "selected"; } ?><?php echo ' value="hover-plus">hover-plus</option>
			<option '; ?><?php if($overlay_icon == "adjust") { echo "selected"; } ?><?php echo ' value="adjust">Adjust</option>
			<option '; ?><?php if($overlay_icon == "adn") { echo "selected"; } ?><?php echo ' value="adn">Adn</option>
			<option '; ?><?php if($overlay_icon == "align-center") { echo "selected"; } ?><?php echo ' value="align-center">Align-Center</option>
			<option '; ?><?php if($overlay_icon == "align-justify") { echo "selected"; } ?><?php echo ' value="align-justify">Align-Justify</option>
			<option '; ?><?php if($overlay_icon == "align-left") { echo "selected"; } ?><?php echo ' value="align-left">Align-Left</option>
			<option '; ?><?php if($overlay_icon == "align-right") { echo "selected"; } ?><?php echo ' value="align-right">Align-Right</option>
			<option '; ?><?php if($overlay_icon == "ambulance") { echo "selected"; } ?><?php echo ' value="ambulance">Ambulance</option>
			<option '; ?><?php if($overlay_icon == "anchor") { echo "selected"; } ?><?php echo ' value="anchor">Anchor</option>
			<option '; ?><?php if($overlay_icon == "android") { echo "selected"; } ?><?php echo ' value="android">Android</option>
			<option '; ?><?php if($overlay_icon == "angle-double-down") { echo "selected"; } ?><?php echo ' value="angle-double-down">Angle-Double-Down</option>
			<option '; ?><?php if($overlay_icon == "angle-double-left") { echo "selected"; } ?><?php echo ' value="angle-double-left">Angle-Double-Left</option>
			<option '; ?><?php if($overlay_icon == "angle-double-right") { echo "selected"; } ?><?php echo ' value="angle-double-right">Angle-Double-Right</option>
			<option '; ?><?php if($overlay_icon == "angle-double-up") { echo "selected"; } ?><?php echo ' value="angle-double-up">Angle-Double-Up</option>
			<option '; ?><?php if($overlay_icon == "angle-down") { echo "selected"; } ?><?php echo ' value="angle-down">Angle-Down</option>
			<option '; ?><?php if($overlay_icon == "angle-left") { echo "selected"; } ?><?php echo ' value="angle-left">Angle-Left</option>
			<option '; ?><?php if($overlay_icon == "angle-right") { echo "selected"; } ?><?php echo ' value="angle-right">Angle-Right</option>
			<option '; ?><?php if($overlay_icon == "angle-up") { echo "selected"; } ?><?php echo ' value="angle-up">Angle-Up</option>
			<option '; ?><?php if($overlay_icon == "apple") { echo "selected"; } ?><?php echo ' value="apple">Apple</option>
			<option '; ?><?php if($overlay_icon == "archive") { echo "selected"; } ?><?php echo ' value="archive">Archive</option>
			<option '; ?><?php if($overlay_icon == "arrow-circle-down") { echo "selected"; } ?><?php echo ' value="arrow-circle-down">Arrow-Circle-Down</option>
			<option '; ?><?php if($overlay_icon == "arrow-circle-left") { echo "selected"; } ?><?php echo ' value="arrow-circle-left">Arrow-Circle-Left</option>
			<option '; ?><?php if($overlay_icon == "arrow-circle-o-down") { echo "selected"; } ?><?php echo ' value="arrow-circle-o-down">Arrow-Circle-O-Down</option>
			<option '; ?><?php if($overlay_icon == "arrow-circle-o-left") { echo "selected"; } ?><?php echo ' value="arrow-circle-o-left">Arrow-Circle-O-Left</option>
			<option '; ?><?php if($overlay_icon == "arrow-circle-o-right") { echo "selected"; } ?><?php echo ' value="arrow-circle-o-right">Arrow-Circle-O-Right</option>
			<option '; ?><?php if($overlay_icon == "arrow-circle-o-up") { echo "selected"; } ?><?php echo ' value="arrow-circle-o-up">Arrow-Circle-O-Up</option>
			<option '; ?><?php if($overlay_icon == "arrow-circle-right") { echo "selected"; } ?><?php echo ' value="arrow-circle-right">Arrow-Circle-Right</option>
			<option '; ?><?php if($overlay_icon == "arrow-circle-up") { echo "selected"; } ?><?php echo ' value="arrow-circle-up">Arrow-Circle-Up</option>
			<option '; ?><?php if($overlay_icon == "arrow-down") { echo "selected"; } ?><?php echo ' value="arrow-down">Arrow-Down</option>
			<option '; ?><?php if($overlay_icon == "arrow-left") { echo "selected"; } ?><?php echo ' value="arrow-left">Arrow-Left</option>
			<option '; ?><?php if($overlay_icon == "arrow-right") { echo "selected"; } ?><?php echo ' value="arrow-right">Arrow-Right</option>
			<option '; ?><?php if($overlay_icon == "arrows") { echo "selected"; } ?><?php echo ' value="arrows">Arrows</option>
			<option '; ?><?php if($overlay_icon == "arrows-alt") { echo "selected"; } ?><?php echo ' value="arrows-alt">Arrows-Alt</option>
			<option '; ?><?php if($overlay_icon == "arrows-h") { echo "selected"; } ?><?php echo ' value="arrows-h">Arrows-H</option>
			<option '; ?><?php if($overlay_icon == "arrows-v") { echo "selected"; } ?><?php echo ' value="arrows-v">Arrows-V</option>
			<option '; ?><?php if($overlay_icon == "arrow-up") { echo "selected"; } ?><?php echo ' value="arrow-up">Arrow-Up</option>
			<option '; ?><?php if($overlay_icon == "asterisk") { echo "selected"; } ?><?php echo ' value="asterisk">Asterisk</option>
			<option '; ?><?php if($overlay_icon == "backward") { echo "selected"; } ?><?php echo ' value="backward">Backward</option>
			<option '; ?><?php if($overlay_icon == "ban") { echo "selected"; } ?><?php echo ' value="ban">Ban</option>
			<option '; ?><?php if($overlay_icon == "bar-chart-o") { echo "selected"; } ?><?php echo ' value="bar-chart-o">Bar-Chart-O</option>
			<option '; ?><?php if($overlay_icon == "barcode") { echo "selected"; } ?><?php echo ' value="barcode">Barcode</option>
			<option '; ?><?php if($overlay_icon == "bars") { echo "selected"; } ?><?php echo ' value="bars">Bars</option>
			<option '; ?><?php if($overlay_icon == "beer") { echo "selected"; } ?><?php echo ' value="beer">Beer</option>
			<option '; ?><?php if($overlay_icon == "behance") { echo "selected"; } ?><?php echo ' value="behance">Behance</option>
			<option '; ?><?php if($overlay_icon == "behance-square") { echo "selected"; } ?><?php echo ' value="behance-square">Behance-Square</option>
			<option '; ?><?php if($overlay_icon == "bell") { echo "selected"; } ?><?php echo ' value="bell">Bell</option>
			<option '; ?><?php if($overlay_icon == "bell-o") { echo "selected"; } ?><?php echo ' value="bell-o">Bell-O</option>
			<option '; ?><?php if($overlay_icon == "bitbucket") { echo "selected"; } ?><?php echo ' value="bitbucket">Bitbucket</option>
			<option '; ?><?php if($overlay_icon == "bitbucket-square") { echo "selected"; } ?><?php echo ' value="bitbucket-square">Bitbucket-Square</option>
			<option '; ?><?php if($overlay_icon == "bold") { echo "selected"; } ?><?php echo ' value="bold">Bold</option>
			<option '; ?><?php if($overlay_icon == "bolt") { echo "selected"; } ?><?php echo ' value="bolt">Bolt</option>
			<option '; ?><?php if($overlay_icon == "bomb") { echo "selected"; } ?><?php echo ' value="bomb">Bomb</option>
			<option '; ?><?php if($overlay_icon == "book") { echo "selected"; } ?><?php echo ' value="book">Book</option>
			<option '; ?><?php if($overlay_icon == "bookmark") { echo "selected"; } ?><?php echo ' value="bookmark">Bookmark</option>
			<option '; ?><?php if($overlay_icon == "bookmark-o") { echo "selected"; } ?><?php echo ' value="bookmark-o">Bookmark-O</option>
			<option '; ?><?php if($overlay_icon == "briefcase") { echo "selected"; } ?><?php echo ' value="briefcase">Briefcase</option>
			<option '; ?><?php if($overlay_icon == "btc") { echo "selected"; } ?><?php echo ' value="btc">Btc</option>
			<option '; ?><?php if($overlay_icon == "bug") { echo "selected"; } ?><?php echo ' value="bug">Bug</option>
			<option '; ?><?php if($overlay_icon == "building") { echo "selected"; } ?><?php echo ' value="building">Building</option>
			<option '; ?><?php if($overlay_icon == "building-o") { echo "selected"; } ?><?php echo ' value="building-o">Building-O</option>
			<option '; ?><?php if($overlay_icon == "bullhorn") { echo "selected"; } ?><?php echo ' value="bullhorn">Bullhorn</option>
			<option '; ?><?php if($overlay_icon == "bullseye") { echo "selected"; } ?><?php echo ' value="bullseye">Bullseye</option>
			<option '; ?><?php if($overlay_icon == "calendar") { echo "selected"; } ?><?php echo ' value="calendar">Calendar</option>
			<option '; ?><?php if($overlay_icon == "calendar-o") { echo "selected"; } ?><?php echo ' value="calendar-o">Calendar-O</option>
			<option '; ?><?php if($overlay_icon == "camera") { echo "selected"; } ?><?php echo ' value="camera">Camera</option>
			<option '; ?><?php if($overlay_icon == "camera-retro") { echo "selected"; } ?><?php echo ' value="camera-retro">Camera-Retro</option>
			<option '; ?><?php if($overlay_icon == "car") { echo "selected"; } ?><?php echo ' value="car">Car</option>
			<option '; ?><?php if($overlay_icon == "caret-down") { echo "selected"; } ?><?php echo ' value="caret-down">Caret-Down</option>
			<option '; ?><?php if($overlay_icon == "caret-left") { echo "selected"; } ?><?php echo ' value="caret-left">Caret-Left</option>
			<option '; ?><?php if($overlay_icon == "caret-right") { echo "selected"; } ?><?php echo ' value="caret-right">Caret-Right</option>
			<option '; ?><?php if($overlay_icon == "caret-square-o-down") { echo "selected"; } ?><?php echo ' value="caret-square-o-down">Caret-Square-O-Down</option>
			<option '; ?><?php if($overlay_icon == "caret-square-o-left") { echo "selected"; } ?><?php echo ' value="caret-square-o-left">Caret-Square-O-Left</option>
			<option '; ?><?php if($overlay_icon == "caret-square-o-right") { echo "selected"; } ?><?php echo ' value="caret-square-o-right">Caret-Square-O-Right</option>
			<option '; ?><?php if($overlay_icon == "caret-square-o-up") { echo "selected"; } ?><?php echo ' value="caret-square-o-up">Caret-Square-O-Up</option>
			<option '; ?><?php if($overlay_icon == "caret-up") { echo "selected"; } ?><?php echo ' value="caret-up">Caret-Up</option>
			<option '; ?><?php if($overlay_icon == "certificate") { echo "selected"; } ?><?php echo ' value="certificate">Certificate</option>
			<option '; ?><?php if($overlay_icon == "chain-broken") { echo "selected"; } ?><?php echo ' value="chain-broken">Chain-Broken</option>
			<option '; ?><?php if($overlay_icon == "check") { echo "selected"; } ?><?php echo ' value="check">Check</option>
			<option '; ?><?php if($overlay_icon == "check-circle") { echo "selected"; } ?><?php echo ' value="check-circle">Check-Circle</option>
			<option '; ?><?php if($overlay_icon == "check-circle-o") { echo "selected"; } ?><?php echo ' value="check-circle-o">Check-Circle-O</option>
			<option '; ?><?php if($overlay_icon == "check-square") { echo "selected"; } ?><?php echo ' value="check-square">Check-Square</option>
			<option '; ?><?php if($overlay_icon == "check-square-o") { echo "selected"; } ?><?php echo ' value="check-square-o">Check-Square-O</option>
			<option '; ?><?php if($overlay_icon == "chevron-circle-down") { echo "selected"; } ?><?php echo ' value="chevron-circle-down">Chevron-Circle-Down</option>
			<option '; ?><?php if($overlay_icon == "chevron-circle-left") { echo "selected"; } ?><?php echo ' value="chevron-circle-left">Chevron-Circle-Left</option>
			<option '; ?><?php if($overlay_icon == "chevron-circle-right") { echo "selected"; } ?><?php echo ' value="chevron-circle-right">Chevron-Circle-Right</option>
			<option '; ?><?php if($overlay_icon == "chevron-circle-up") { echo "selected"; } ?><?php echo ' value="chevron-circle-up">Chevron-Circle-Up</option>
			<option '; ?><?php if($overlay_icon == "chevron-down") { echo "selected"; } ?><?php echo ' value="chevron-down">Chevron-Down</option>
			<option '; ?><?php if($overlay_icon == "chevron-left") { echo "selected"; } ?><?php echo ' value="chevron-left">Chevron-Left</option>
			<option '; ?><?php if($overlay_icon == "chevron-right") { echo "selected"; } ?><?php echo ' value="chevron-right">Chevron-Right</option>
			<option '; ?><?php if($overlay_icon == "chevron-up") { echo "selected"; } ?><?php echo ' value="chevron-up">Chevron-Up</option>
			<option '; ?><?php if($overlay_icon == "child") { echo "selected"; } ?><?php echo ' value="child">Child</option>
			<option '; ?><?php if($overlay_icon == "circle") { echo "selected"; } ?><?php echo ' value="circle">Circle</option>
			<option '; ?><?php if($overlay_icon == "circle-o") { echo "selected"; } ?><?php echo ' value="circle-o">Circle-O</option>
			<option '; ?><?php if($overlay_icon == "circle-o-notch") { echo "selected"; } ?><?php echo ' value="circle-o-notch">Circle-O-Notch</option>
			<option '; ?><?php if($overlay_icon == "circle-thin") { echo "selected"; } ?><?php echo ' value="circle-thin">Circle-Thin</option>
			<option '; ?><?php if($overlay_icon == "clipboard") { echo "selected"; } ?><?php echo ' value="clipboard">Clipboard</option>
			<option '; ?><?php if($overlay_icon == "clock-o") { echo "selected"; } ?><?php echo ' value="clock-o">Clock-O</option>
			<option '; ?><?php if($overlay_icon == "cloud") { echo "selected"; } ?><?php echo ' value="cloud">Cloud</option>
			<option '; ?><?php if($overlay_icon == "cloud-download") { echo "selected"; } ?><?php echo ' value="cloud-download">Cloud-Download</option>
			<option '; ?><?php if($overlay_icon == "cloud-upload") { echo "selected"; } ?><?php echo ' value="cloud-upload">Cloud-Upload</option>
			<option '; ?><?php if($overlay_icon == "code") { echo "selected"; } ?><?php echo ' value="code">Code</option>
			<option '; ?><?php if($overlay_icon == "code-fork") { echo "selected"; } ?><?php echo ' value="code-fork">Code-Fork</option>
			<option '; ?><?php if($overlay_icon == "codepen") { echo "selected"; } ?><?php echo ' value="codepen">Codepen</option>
			<option '; ?><?php if($overlay_icon == "coffee") { echo "selected"; } ?><?php echo ' value="coffee">Coffee</option>
			<option '; ?><?php if($overlay_icon == "cog") { echo "selected"; } ?><?php echo ' value="cog">Cog</option>
			<option '; ?><?php if($overlay_icon == "cogs") { echo "selected"; } ?><?php echo ' value="cogs">Cogs</option>
			<option '; ?><?php if($overlay_icon == "columns") { echo "selected"; } ?><?php echo ' value="columns">Columns</option>
			<option '; ?><?php if($overlay_icon == "comment") { echo "selected"; } ?><?php echo ' value="comment">Comment</option>
			<option '; ?><?php if($overlay_icon == "comment-o") { echo "selected"; } ?><?php echo ' value="comment-o">Comment-O</option>
			<option '; ?><?php if($overlay_icon == "comments") { echo "selected"; } ?><?php echo ' value="comments">Comments</option>
			<option '; ?><?php if($overlay_icon == "comments-o") { echo "selected"; } ?><?php echo ' value="comments-o">Comments-O</option>
			<option '; ?><?php if($overlay_icon == "compass") { echo "selected"; } ?><?php echo ' value="compass">Compass</option>
			<option '; ?><?php if($overlay_icon == "compress") { echo "selected"; } ?><?php echo ' value="compress">Compress</option>
			<option '; ?><?php if($overlay_icon == "credit-card") { echo "selected"; } ?><?php echo ' value="credit-card">Credit-Card</option>
			<option '; ?><?php if($overlay_icon == "crop") { echo "selected"; } ?><?php echo ' value="crop">Crop</option>
			<option '; ?><?php if($overlay_icon == "crosshairs") { echo "selected"; } ?><?php echo ' value="crosshairs">Crosshairs</option>
			<option '; ?><?php if($overlay_icon == "css3") { echo "selected"; } ?><?php echo ' value="css3">Css3</option>
			<option '; ?><?php if($overlay_icon == "cube") { echo "selected"; } ?><?php echo ' value="cube">Cube</option>
			<option '; ?><?php if($overlay_icon == "cubes") { echo "selected"; } ?><?php echo ' value="cubes">Cubes</option>
			<option '; ?><?php if($overlay_icon == "cutlery") { echo "selected"; } ?><?php echo ' value="cutlery">Cutlery</option>
			<option '; ?><?php if($overlay_icon == "database") { echo "selected"; } ?><?php echo ' value="database">Database</option>
			<option '; ?><?php if($overlay_icon == "delicious") { echo "selected"; } ?><?php echo ' value="delicious">Delicious</option>
			<option '; ?><?php if($overlay_icon == "desktop") { echo "selected"; } ?><?php echo ' value="desktop">Desktop</option>
			<option '; ?><?php if($overlay_icon == "deviantart") { echo "selected"; } ?><?php echo ' value="deviantart">Deviantart</option>
			<option '; ?><?php if($overlay_icon == "digg") { echo "selected"; } ?><?php echo ' value="digg">Digg</option>
			<option '; ?><?php if($overlay_icon == "dot-circle-o") { echo "selected"; } ?><?php echo ' value="dot-circle-o">Dot-Circle-O</option>
			<option '; ?><?php if($overlay_icon == "download") { echo "selected"; } ?><?php echo ' value="download">Download</option>
			<option '; ?><?php if($overlay_icon == "dribbble") { echo "selected"; } ?><?php echo ' value="dribbble">Dribbble</option>
			<option '; ?><?php if($overlay_icon == "dropbox") { echo "selected"; } ?><?php echo ' value="dropbox">Dropbox</option>
			<option '; ?><?php if($overlay_icon == "drupal") { echo "selected"; } ?><?php echo ' value="drupal">Drupal</option>
			<option '; ?><?php if($overlay_icon == "eject") { echo "selected"; } ?><?php echo ' value="eject">Eject</option>
			<option '; ?><?php if($overlay_icon == "ellipsis-h") { echo "selected"; } ?><?php echo ' value="ellipsis-h">Ellipsis-H</option>
			<option '; ?><?php if($overlay_icon == "ellipsis-v") { echo "selected"; } ?><?php echo ' value="ellipsis-v">Ellipsis-V</option>
			<option '; ?><?php if($overlay_icon == "empire") { echo "selected"; } ?><?php echo ' value="empire">Empire</option>
			<option '; ?><?php if($overlay_icon == "envelope") { echo "selected"; } ?><?php echo ' value="envelope">Envelope</option>
			<option '; ?><?php if($overlay_icon == "envelope-o") { echo "selected"; } ?><?php echo ' value="envelope-o">Envelope-O</option>
			<option '; ?><?php if($overlay_icon == "envelope-square") { echo "selected"; } ?><?php echo ' value="envelope-square">Envelope-Square</option>
			<option '; ?><?php if($overlay_icon == "eraser") { echo "selected"; } ?><?php echo ' value="eraser">Eraser</option>
			<option '; ?><?php if($overlay_icon == "eur") { echo "selected"; } ?><?php echo ' value="eur">Eur</option>
			<option '; ?><?php if($overlay_icon == "exchange") { echo "selected"; } ?><?php echo ' value="exchange">Exchange</option>
			<option '; ?><?php if($overlay_icon == "exclamation") { echo "selected"; } ?><?php echo ' value="exclamation">Exclamation</option>
			<option '; ?><?php if($overlay_icon == "exclamation-circle") { echo "selected"; } ?><?php echo ' value="exclamation-circle">Exclamation-Circle</option>
			<option '; ?><?php if($overlay_icon == "exclamation-triangle") { echo "selected"; } ?><?php echo ' value="exclamation-triangle">Exclamation-Triangle</option>
			<option '; ?><?php if($overlay_icon == "expand") { echo "selected"; } ?><?php echo ' value="expand">Expand</option>
			<option '; ?><?php if($overlay_icon == "external-link") { echo "selected"; } ?><?php echo ' value="external-link">External-Link</option>
			<option '; ?><?php if($overlay_icon == "external-link-square") { echo "selected"; } ?><?php echo ' value="external-link-square">External-Link-Square</option>
			<option '; ?><?php if($overlay_icon == "eye") { echo "selected"; } ?><?php echo ' value="eye">Eye</option>
			<option '; ?><?php if($overlay_icon == "eye-slash") { echo "selected"; } ?><?php echo ' value="eye-slash">Eye-Slash</option>
			<option '; ?><?php if($overlay_icon == "facebook") { echo "selected"; } ?><?php echo ' value="facebook">Facebook</option>
			<option '; ?><?php if($overlay_icon == "facebook-square") { echo "selected"; } ?><?php echo ' value="facebook-square">Facebook-Square</option>
			<option '; ?><?php if($overlay_icon == "fast-backward") { echo "selected"; } ?><?php echo ' value="fast-backward">Fast-Backward</option>
			<option '; ?><?php if($overlay_icon == "fast-forward") { echo "selected"; } ?><?php echo ' value="fast-forward">Fast-Forward</option>
			<option '; ?><?php if($overlay_icon == "fax") { echo "selected"; } ?><?php echo ' value="fax">Fax</option>
			<option '; ?><?php if($overlay_icon == "female") { echo "selected"; } ?><?php echo ' value="female">Female</option>
			<option '; ?><?php if($overlay_icon == "fighter-jet") { echo "selected"; } ?><?php echo ' value="fighter-jet">Fighter-Jet</option>
			<option '; ?><?php if($overlay_icon == "file") { echo "selected"; } ?><?php echo ' value="file">File</option>
			<option '; ?><?php if($overlay_icon == "file-archive-o") { echo "selected"; } ?><?php echo ' value="file-archive-o">File-Archive-O</option>
			<option '; ?><?php if($overlay_icon == "file-audio-o") { echo "selected"; } ?><?php echo ' value="file-audio-o">File-Audio-O</option>
			<option '; ?><?php if($overlay_icon == "file-code-o") { echo "selected"; } ?><?php echo ' value="file-code-o">File-Code-O</option>
			<option '; ?><?php if($overlay_icon == "file-excel-o") { echo "selected"; } ?><?php echo ' value="file-excel-o">File-Excel-O</option>
			<option '; ?><?php if($overlay_icon == "file-image-o") { echo "selected"; } ?><?php echo ' value="file-image-o">File-Image-O</option>
			<option '; ?><?php if($overlay_icon == "file-o") { echo "selected"; } ?><?php echo ' value="file-o">File-O</option>
			<option '; ?><?php if($overlay_icon == "file-pdf-o") { echo "selected"; } ?><?php echo ' value="file-pdf-o">File-Pdf-O</option>
			<option '; ?><?php if($overlay_icon == "file-powerpoint-o") { echo "selected"; } ?><?php echo ' value="file-powerpoint-o">File-Powerpoint-O</option>
			<option '; ?><?php if($overlay_icon == "files-o") { echo "selected"; } ?><?php echo ' value="files-o">Files-O</option>
			<option '; ?><?php if($overlay_icon == "file-text") { echo "selected"; } ?><?php echo ' value="file-text">File-Text</option>
			<option '; ?><?php if($overlay_icon == "file-text-o") { echo "selected"; } ?><?php echo ' value="file-text-o">File-Text-O</option>
			<option '; ?><?php if($overlay_icon == "file-video-o") { echo "selected"; } ?><?php echo ' value="file-video-o">File-Video-O</option>
			<option '; ?><?php if($overlay_icon == "file-word-o") { echo "selected"; } ?><?php echo ' value="file-word-o">File-Word-O</option>
			<option '; ?><?php if($overlay_icon == "film") { echo "selected"; } ?><?php echo ' value="film">Film</option>
			<option '; ?><?php if($overlay_icon == "filter") { echo "selected"; } ?><?php echo ' value="filter">Filter</option>
			<option '; ?><?php if($overlay_icon == "fire") { echo "selected"; } ?><?php echo ' value="fire">Fire</option>
			<option '; ?><?php if($overlay_icon == "fire-extinguisher") { echo "selected"; } ?><?php echo ' value="fire-extinguisher">Fire-Extinguisher</option>
			<option '; ?><?php if($overlay_icon == "flag") { echo "selected"; } ?><?php echo ' value="flag">Flag</option>
			<option '; ?><?php if($overlay_icon == "flag-checkered") { echo "selected"; } ?><?php echo ' value="flag-checkered">Flag-Checkered</option>
			<option '; ?><?php if($overlay_icon == "flag-o") { echo "selected"; } ?><?php echo ' value="flag-o">Flag-O</option>
			<option '; ?><?php if($overlay_icon == "flask") { echo "selected"; } ?><?php echo ' value="flask">Flask</option>
			<option '; ?><?php if($overlay_icon == "flickr") { echo "selected"; } ?><?php echo ' value="flickr">Flickr</option>
			<option '; ?><?php if($overlay_icon == "floppy-o") { echo "selected"; } ?><?php echo ' value="floppy-o">Floppy-O</option>
			<option '; ?><?php if($overlay_icon == "folder") { echo "selected"; } ?><?php echo ' value="folder">Folder</option>
			<option '; ?><?php if($overlay_icon == "folder-o") { echo "selected"; } ?><?php echo ' value="folder-o">Folder-O</option>
			<option '; ?><?php if($overlay_icon == "folder-open") { echo "selected"; } ?><?php echo ' value="folder-open">Folder-Open</option>
			<option '; ?><?php if($overlay_icon == "folder-open-o") { echo "selected"; } ?><?php echo ' value="folder-open-o">Folder-Open-O</option>
			<option '; ?><?php if($overlay_icon == "font") { echo "selected"; } ?><?php echo ' value="font">Font</option>
			<option '; ?><?php if($overlay_icon == "forward") { echo "selected"; } ?><?php echo ' value="forward">Forward</option>
			<option '; ?><?php if($overlay_icon == "foursquare") { echo "selected"; } ?><?php echo ' value="foursquare">Foursquare</option>
			<option '; ?><?php if($overlay_icon == "frown-o") { echo "selected"; } ?><?php echo ' value="frown-o">Frown-O</option>
			<option '; ?><?php if($overlay_icon == "gamepad") { echo "selected"; } ?><?php echo ' value="gamepad">Gamepad</option>
			<option '; ?><?php if($overlay_icon == "gavel") { echo "selected"; } ?><?php echo ' value="gavel">Gavel</option>
			<option '; ?><?php if($overlay_icon == "gbp") { echo "selected"; } ?><?php echo ' value="gbp">Gbp</option>
			<option '; ?><?php if($overlay_icon == "gift") { echo "selected"; } ?><?php echo ' value="gift">Gift</option>
			<option '; ?><?php if($overlay_icon == "git") { echo "selected"; } ?><?php echo ' value="git">Git</option>
			<option '; ?><?php if($overlay_icon == "github") { echo "selected"; } ?><?php echo ' value="github">Github</option>
			<option '; ?><?php if($overlay_icon == "github-alt") { echo "selected"; } ?><?php echo ' value="github-alt">Github-Alt</option>
			<option '; ?><?php if($overlay_icon == "github-square") { echo "selected"; } ?><?php echo ' value="github-square">Github-Square</option>
			<option '; ?><?php if($overlay_icon == "git-square") { echo "selected"; } ?><?php echo ' value="git-square">Git-Square</option>
			<option '; ?><?php if($overlay_icon == "gittip") { echo "selected"; } ?><?php echo ' value="gittip">Gittip</option>
			<option '; ?><?php if($overlay_icon == "glass") { echo "selected"; } ?><?php echo ' value="glass">Glass</option>
			<option '; ?><?php if($overlay_icon == "globe") { echo "selected"; } ?><?php echo ' value="globe">Globe</option>
			<option '; ?><?php if($overlay_icon == "google") { echo "selected"; } ?><?php echo ' value="google">Google</option>
			<option '; ?><?php if($overlay_icon == "google-plus") { echo "selected"; } ?><?php echo ' value="google-plus">Google-Plus</option>
			<option '; ?><?php if($overlay_icon == "google-plus-square") { echo "selected"; } ?><?php echo ' value="google-plus-square">Google-Plus-Square</option>
			<option '; ?><?php if($overlay_icon == "graduation-cap") { echo "selected"; } ?><?php echo ' value="graduation-cap">Graduation-Cap</option>
			<option '; ?><?php if($overlay_icon == "hacker-news") { echo "selected"; } ?><?php echo ' value="hacker-news">Hacker-News</option>
			<option '; ?><?php if($overlay_icon == "hand-o-down") { echo "selected"; } ?><?php echo ' value="hand-o-down">Hand-O-Down</option>
			<option '; ?><?php if($overlay_icon == "hand-o-left") { echo "selected"; } ?><?php echo ' value="hand-o-left">Hand-O-Left</option>
			<option '; ?><?php if($overlay_icon == "hand-o-right") { echo "selected"; } ?><?php echo ' value="hand-o-right">Hand-O-Right</option>
			<option '; ?><?php if($overlay_icon == "hand-o-up") { echo "selected"; } ?><?php echo ' value="hand-o-up">Hand-O-Up</option>
			<option '; ?><?php if($overlay_icon == "hdd-o") { echo "selected"; } ?><?php echo ' value="hdd-o">Hdd-O</option>
			<option '; ?><?php if($overlay_icon == "header") { echo "selected"; } ?><?php echo ' value="header">Header</option>
			<option '; ?><?php if($overlay_icon == "headphones") { echo "selected"; } ?><?php echo ' value="headphones">Headphones</option>
			<option '; ?><?php if($overlay_icon == "heart") { echo "selected"; } ?><?php echo ' value="heart">Heart</option>
			<option '; ?><?php if($overlay_icon == "heart-o") { echo "selected"; } ?><?php echo ' value="heart-o">Heart-O</option>
			<option '; ?><?php if($overlay_icon == "history") { echo "selected"; } ?><?php echo ' value="history">History</option>
			<option '; ?><?php if($overlay_icon == "home") { echo "selected"; } ?><?php echo ' value="home">Home</option>
			<option '; ?><?php if($overlay_icon == "hospital-o") { echo "selected"; } ?><?php echo ' value="hospital-o">Hospital-O</option>
			<option '; ?><?php if($overlay_icon == "h-square") { echo "selected"; } ?><?php echo ' value="h-square">H-Square</option>
			<option '; ?><?php if($overlay_icon == "html5") { echo "selected"; } ?><?php echo ' value="html5">Html5</option>
			<option '; ?><?php if($overlay_icon == "inbox") { echo "selected"; } ?><?php echo ' value="inbox">Inbox</option>
			<option '; ?><?php if($overlay_icon == "indent") { echo "selected"; } ?><?php echo ' value="indent">Indent</option>
			<option '; ?><?php if($overlay_icon == "info") { echo "selected"; } ?><?php echo ' value="info">Info</option>
			<option '; ?><?php if($overlay_icon == "info-circle") { echo "selected"; } ?><?php echo ' value="info-circle">Info-Circle</option>
			<option '; ?><?php if($overlay_icon == "inr") { echo "selected"; } ?><?php echo ' value="inr">Inr</option>
			<option '; ?><?php if($overlay_icon == "instagram") { echo "selected"; } ?><?php echo ' value="instagram">Instagram</option>
			<option '; ?><?php if($overlay_icon == "italic") { echo "selected"; } ?><?php echo ' value="italic">Italic</option>
			<option '; ?><?php if($overlay_icon == "joomla") { echo "selected"; } ?><?php echo ' value="joomla">Joomla</option>
			<option '; ?><?php if($overlay_icon == "jpy") { echo "selected"; } ?><?php echo ' value="jpy">Jpy</option>
			<option '; ?><?php if($overlay_icon == "jsfiddle") { echo "selected"; } ?><?php echo ' value="jsfiddle">Jsfiddle</option>
			<option '; ?><?php if($overlay_icon == "key") { echo "selected"; } ?><?php echo ' value="key">Key</option>
			<option '; ?><?php if($overlay_icon == "keyboard-o") { echo "selected"; } ?><?php echo ' value="keyboard-o">Keyboard-O</option>
			<option '; ?><?php if($overlay_icon == "krw") { echo "selected"; } ?><?php echo ' value="krw">Krw</option>
			<option '; ?><?php if($overlay_icon == "language") { echo "selected"; } ?><?php echo ' value="language">Language</option>
			<option '; ?><?php if($overlay_icon == "laptop") { echo "selected"; } ?><?php echo ' value="laptop">Laptop</option>
			<option '; ?><?php if($overlay_icon == "leaf") { echo "selected"; } ?><?php echo ' value="leaf">Leaf</option>
			<option '; ?><?php if($overlay_icon == "lemon-o") { echo "selected"; } ?><?php echo ' value="lemon-o">Lemon-O</option>
			<option '; ?><?php if($overlay_icon == "level-down") { echo "selected"; } ?><?php echo ' value="level-down">Level-Down</option>
			<option '; ?><?php if($overlay_icon == "level-up") { echo "selected"; } ?><?php echo ' value="level-up">Level-Up</option>
			<option '; ?><?php if($overlay_icon == "life-ring") { echo "selected"; } ?><?php echo ' value="life-ring">Life-Ring</option>
			<option '; ?><?php if($overlay_icon == "lightbulb-o") { echo "selected"; } ?><?php echo ' value="lightbulb-o">Lightbulb-O</option>
			<option '; ?><?php if($overlay_icon == "link") { echo "selected"; } ?><?php echo ' value="link">Link</option>
			<option '; ?><?php if($overlay_icon == "linkedin") { echo "selected"; } ?><?php echo ' value="linkedin">Linkedin</option>
			<option '; ?><?php if($overlay_icon == "linkedin-square") { echo "selected"; } ?><?php echo ' value="linkedin-square">Linkedin-Square</option>
			<option '; ?><?php if($overlay_icon == "linux") { echo "selected"; } ?><?php echo ' value="linux">Linux</option>
			<option '; ?><?php if($overlay_icon == "list") { echo "selected"; } ?><?php echo ' value="list">List</option>
			<option '; ?><?php if($overlay_icon == "list-alt") { echo "selected"; } ?><?php echo ' value="list-alt">List-Alt</option>
			<option '; ?><?php if($overlay_icon == "list-ol") { echo "selected"; } ?><?php echo ' value="list-ol">List-Ol</option>
			<option '; ?><?php if($overlay_icon == "list-ul") { echo "selected"; } ?><?php echo ' value="list-ul">List-Ul</option>
			<option '; ?><?php if($overlay_icon == "location-arrow") { echo "selected"; } ?><?php echo ' value="location-arrow">Location-Arrow</option>
			<option '; ?><?php if($overlay_icon == "lock") { echo "selected"; } ?><?php echo ' value="lock">Lock</option>
			<option '; ?><?php if($overlay_icon == "long-arrow-down") { echo "selected"; } ?><?php echo ' value="long-arrow-down">Long-Arrow-Down</option>
			<option '; ?><?php if($overlay_icon == "long-arrow-left") { echo "selected"; } ?><?php echo ' value="long-arrow-left">Long-Arrow-Left</option>
			<option '; ?><?php if($overlay_icon == "long-arrow-right") { echo "selected"; } ?><?php echo ' value="long-arrow-right">Long-Arrow-Right</option>
			<option '; ?><?php if($overlay_icon == "long-arrow-up") { echo "selected"; } ?><?php echo ' value="long-arrow-up">Long-Arrow-Up</option>
			<option '; ?><?php if($overlay_icon == "magic") { echo "selected"; } ?><?php echo ' value="magic">Magic</option>
			<option '; ?><?php if($overlay_icon == "magnet") { echo "selected"; } ?><?php echo ' value="magnet">Magnet</option>
			<option '; ?><?php if($overlay_icon == "male") { echo "selected"; } ?><?php echo ' value="male">Male</option>
			<option '; ?><?php if($overlay_icon == "map-marker") { echo "selected"; } ?><?php echo ' value="map-marker">Map-Marker</option>
			<option '; ?><?php if($overlay_icon == "maxcdn") { echo "selected"; } ?><?php echo ' value="maxcdn">Maxcdn</option>
			<option '; ?><?php if($overlay_icon == "medkit") { echo "selected"; } ?><?php echo ' value="medkit">Medkit</option>
			<option '; ?><?php if($overlay_icon == "meh-o") { echo "selected"; } ?><?php echo ' value="meh-o">Meh-O</option>
			<option '; ?><?php if($overlay_icon == "microphone") { echo "selected"; } ?><?php echo ' value="microphone">Microphone</option>
			<option '; ?><?php if($overlay_icon == "microphone-slash") { echo "selected"; } ?><?php echo ' value="microphone-slash">Microphone-Slash</option>
			<option '; ?><?php if($overlay_icon == "minus") { echo "selected"; } ?><?php echo ' value="minus">Minus</option>
			<option '; ?><?php if($overlay_icon == "minus-circle") { echo "selected"; } ?><?php echo ' value="minus-circle">Minus-Circle</option>
			<option '; ?><?php if($overlay_icon == "minus-square") { echo "selected"; } ?><?php echo ' value="minus-square">Minus-Square</option>
			<option '; ?><?php if($overlay_icon == "minus-square-o") { echo "selected"; } ?><?php echo ' value="minus-square-o">Minus-Square-O</option>
			<option '; ?><?php if($overlay_icon == "mobile") { echo "selected"; } ?><?php echo ' value="mobile">Mobile</option>
			<option '; ?><?php if($overlay_icon == "money") { echo "selected"; } ?><?php echo ' value="money">Money</option>
			<option '; ?><?php if($overlay_icon == "moon-o") { echo "selected"; } ?><?php echo ' value="moon-o">Moon-O</option>
			<option '; ?><?php if($overlay_icon == "music") { echo "selected"; } ?><?php echo ' value="music">Music</option>
			<option '; ?><?php if($overlay_icon == "openid") { echo "selected"; } ?><?php echo ' value="openid">Openid</option>
			<option '; ?><?php if($overlay_icon == "outdent") { echo "selected"; } ?><?php echo ' value="outdent">Outdent</option>
			<option '; ?><?php if($overlay_icon == "pagelines") { echo "selected"; } ?><?php echo ' value="pagelines">Pagelines</option>
			<option '; ?><?php if($overlay_icon == "paperclip") { echo "selected"; } ?><?php echo ' value="paperclip">Paperclip</option>
			<option '; ?><?php if($overlay_icon == "paper-plane") { echo "selected"; } ?><?php echo ' value="paper-plane">Paper-Plane</option>
			<option '; ?><?php if($overlay_icon == "paper-plane-o") { echo "selected"; } ?><?php echo ' value="paper-plane-o">Paper-Plane-O</option>
			<option '; ?><?php if($overlay_icon == "paragraph") { echo "selected"; } ?><?php echo ' value="paragraph">Paragraph</option>
			<option '; ?><?php if($overlay_icon == "pause") { echo "selected"; } ?><?php echo ' value="pause">Pause</option>
			<option '; ?><?php if($overlay_icon == "paw") { echo "selected"; } ?><?php echo ' value="paw">Paw</option>
			<option '; ?><?php if($overlay_icon == "pencil") { echo "selected"; } ?><?php echo ' value="pencil">Pencil</option>
			<option '; ?><?php if($overlay_icon == "pencil-square") { echo "selected"; } ?><?php echo ' value="pencil-square">Pencil-Square</option>
			<option '; ?><?php if($overlay_icon == "pencil-square-o") { echo "selected"; } ?><?php echo ' value="pencil-square-o">Pencil-Square-O</option>
			<option '; ?><?php if($overlay_icon == "phone") { echo "selected"; } ?><?php echo ' value="phone">Phone</option>
			<option '; ?><?php if($overlay_icon == "phone-square") { echo "selected"; } ?><?php echo ' value="phone-square">Phone-Square</option>
			<option '; ?><?php if($overlay_icon == "picture-o") { echo "selected"; } ?><?php echo ' value="picture-o">Picture-O</option>
			<option '; ?><?php if($overlay_icon == "pied-piper") { echo "selected"; } ?><?php echo ' value="pied-piper">Pied-Piper</option>
			<option '; ?><?php if($overlay_icon == "pied-piper-alt") { echo "selected"; } ?><?php echo ' value="pied-piper-alt">Pied-Piper-Alt</option>
			<option '; ?><?php if($overlay_icon == "pinterest") { echo "selected"; } ?><?php echo ' value="pinterest">Pinterest</option>
			<option '; ?><?php if($overlay_icon == "pinterest-square") { echo "selected"; } ?><?php echo ' value="pinterest-square">Pinterest-Square</option>
			<option '; ?><?php if($overlay_icon == "plane") { echo "selected"; } ?><?php echo ' value="plane">Plane</option>
			<option '; ?><?php if($overlay_icon == "play") { echo "selected"; } ?><?php echo ' value="play">Play</option>
			<option '; ?><?php if($overlay_icon == "play-circle") { echo "selected"; } ?><?php echo ' value="play-circle">Play-Circle</option>
			<option '; ?><?php if($overlay_icon == "play-circle-o") { echo "selected"; } ?><?php echo ' value="play-circle-o">Play-Circle-O</option>
			<option '; ?><?php if($overlay_icon == "plus") { echo "selected"; } ?><?php echo ' value="plus">Plus</option>
			<option '; ?><?php if($overlay_icon == "plus-circle") { echo "selected"; } ?><?php echo ' value="plus-circle">Plus-Circle</option>
			<option '; ?><?php if($overlay_icon == "plus-square") { echo "selected"; } ?><?php echo ' value="plus-square">Plus-Square</option>
			<option '; ?><?php if($overlay_icon == "plus-square-o") { echo "selected"; } ?><?php echo ' value="plus-square-o">Plus-Square-O</option>
			<option '; ?><?php if($overlay_icon == "power-off") { echo "selected"; } ?><?php echo ' value="power-off">Power-Off</option>
			<option '; ?><?php if($overlay_icon == "print") { echo "selected"; } ?><?php echo ' value="print">Print</option>
			<option '; ?><?php if($overlay_icon == "puzzle-piece") { echo "selected"; } ?><?php echo ' value="puzzle-piece">Puzzle-Piece</option>
			<option '; ?><?php if($overlay_icon == "qq") { echo "selected"; } ?><?php echo ' value="qq">Qq</option>
			<option '; ?><?php if($overlay_icon == "qrcode") { echo "selected"; } ?><?php echo ' value="qrcode">Qrcode</option>
			<option '; ?><?php if($overlay_icon == "question") { echo "selected"; } ?><?php echo ' value="question">Question</option>
			<option '; ?><?php if($overlay_icon == "question-circle") { echo "selected"; } ?><?php echo ' value="question-circle">Question-Circle</option>
			<option '; ?><?php if($overlay_icon == "quote-left") { echo "selected"; } ?><?php echo ' value="quote-left">Quote-Left</option>
			<option '; ?><?php if($overlay_icon == "quote-right") { echo "selected"; } ?><?php echo ' value="quote-right">Quote-Right</option>
			<option '; ?><?php if($overlay_icon == "random") { echo "selected"; } ?><?php echo ' value="random">Random</option>
			<option '; ?><?php if($overlay_icon == "rebel") { echo "selected"; } ?><?php echo ' value="rebel">Rebel</option>
			<option '; ?><?php if($overlay_icon == "recycle") { echo "selected"; } ?><?php echo ' value="recycle">Recycle</option>
			<option '; ?><?php if($overlay_icon == "reddit") { echo "selected"; } ?><?php echo ' value="reddit">Reddit</option>
			<option '; ?><?php if($overlay_icon == "reddit-square") { echo "selected"; } ?><?php echo ' value="reddit-square">Reddit-Square</option>
			<option '; ?><?php if($overlay_icon == "refresh") { echo "selected"; } ?><?php echo ' value="refresh">Refresh</option>
			<option '; ?><?php if($overlay_icon == "renren") { echo "selected"; } ?><?php echo ' value="renren">Renren</option>
			<option '; ?><?php if($overlay_icon == "repeat") { echo "selected"; } ?><?php echo ' value="repeat">Repeat</option>
			<option '; ?><?php if($overlay_icon == "reply") { echo "selected"; } ?><?php echo ' value="reply">Reply</option>
			<option '; ?><?php if($overlay_icon == "reply-all") { echo "selected"; } ?><?php echo ' value="reply-all">Reply-All</option>
			<option '; ?><?php if($overlay_icon == "retweet") { echo "selected"; } ?><?php echo ' value="retweet">Retweet</option>
			<option '; ?><?php if($overlay_icon == "road") { echo "selected"; } ?><?php echo ' value="road">Road</option>
			<option '; ?><?php if($overlay_icon == "rocket") { echo "selected"; } ?><?php echo ' value="rocket">Rocket</option>
			<option '; ?><?php if($overlay_icon == "rss") { echo "selected"; } ?><?php echo ' value="rss">Rss</option>
			<option '; ?><?php if($overlay_icon == "rss-square") { echo "selected"; } ?><?php echo ' value="rss-square">Rss-Square</option>
			<option '; ?><?php if($overlay_icon == "rub") { echo "selected"; } ?><?php echo ' value="rub">Rub</option>
			<option '; ?><?php if($overlay_icon == "scissors") { echo "selected"; } ?><?php echo ' value="scissors">Scissors</option>
			<option '; ?><?php if($overlay_icon == "search") { echo "selected"; } ?><?php echo ' value="search">Search</option>
			<option '; ?><?php if($overlay_icon == "search-minus") { echo "selected"; } ?><?php echo ' value="search-minus">Search-Minus</option>
			<option '; ?><?php if($overlay_icon == "search-plus") { echo "selected"; } ?><?php echo ' value="search-plus">Search-Plus</option>
			<option '; ?><?php if($overlay_icon == "share") { echo "selected"; } ?><?php echo ' value="share">Share</option>
			<option '; ?><?php if($overlay_icon == "share-alt") { echo "selected"; } ?><?php echo ' value="share-alt">Share-Alt</option>
			<option '; ?><?php if($overlay_icon == "share-alt-square") { echo "selected"; } ?><?php echo ' value="share-alt-square">Share-Alt-Square</option>
			<option '; ?><?php if($overlay_icon == "share-square") { echo "selected"; } ?><?php echo ' value="share-square">Share-Square</option>
			<option '; ?><?php if($overlay_icon == "share-square-o") { echo "selected"; } ?><?php echo ' value="share-square-o">Share-Square-O</option>
			<option '; ?><?php if($overlay_icon == "shield") { echo "selected"; } ?><?php echo ' value="shield">Shield</option>
			<option '; ?><?php if($overlay_icon == "shopping-cart") { echo "selected"; } ?><?php echo ' value="shopping-cart">Shopping-Cart</option>
			<option '; ?><?php if($overlay_icon == "signal") { echo "selected"; } ?><?php echo ' value="signal">Signal</option>
			<option '; ?><?php if($overlay_icon == "sign-in") { echo "selected"; } ?><?php echo ' value="sign-in">Sign-In</option>
			<option '; ?><?php if($overlay_icon == "sign-out") { echo "selected"; } ?><?php echo ' value="sign-out">Sign-Out</option>
			<option '; ?><?php if($overlay_icon == "sitemap") { echo "selected"; } ?><?php echo ' value="sitemap">Sitemap</option>
			<option '; ?><?php if($overlay_icon == "skype") { echo "selected"; } ?><?php echo ' value="skype">Skype</option>
			<option '; ?><?php if($overlay_icon == "slack") { echo "selected"; } ?><?php echo ' value="slack">Slack</option>
			<option '; ?><?php if($overlay_icon == "sliders") { echo "selected"; } ?><?php echo ' value="sliders">Sliders</option>
			<option '; ?><?php if($overlay_icon == "smile-o") { echo "selected"; } ?><?php echo ' value="smile-o">Smile-O</option>
			<option '; ?><?php if($overlay_icon == "sort") { echo "selected"; } ?><?php echo ' value="sort">Sort</option>
			<option '; ?><?php if($overlay_icon == "sort-alpha-asc") { echo "selected"; } ?><?php echo ' value="sort-alpha-asc">Sort-Alpha-Asc</option>
			<option '; ?><?php if($overlay_icon == "sort-alpha-desc") { echo "selected"; } ?><?php echo ' value="sort-alpha-desc">Sort-Alpha-Desc</option>
			<option '; ?><?php if($overlay_icon == "sort-amount-asc") { echo "selected"; } ?><?php echo ' value="sort-amount-asc">Sort-Amount-Asc</option>
			<option '; ?><?php if($overlay_icon == "sort-amount-desc") { echo "selected"; } ?><?php echo ' value="sort-amount-desc">Sort-Amount-Desc</option>
			<option '; ?><?php if($overlay_icon == "sort-asc") { echo "selected"; } ?><?php echo ' value="sort-asc">Sort-Asc</option>
			<option '; ?><?php if($overlay_icon == "sort-desc") { echo "selected"; } ?><?php echo ' value="sort-desc">Sort-Desc</option>
			<option '; ?><?php if($overlay_icon == "sort-numeric-asc") { echo "selected"; } ?><?php echo ' value="sort-numeric-asc">Sort-Numeric-Asc</option>
			<option '; ?><?php if($overlay_icon == "sort-numeric-desc") { echo "selected"; } ?><?php echo ' value="sort-numeric-desc">Sort-Numeric-Desc</option>
			<option '; ?><?php if($overlay_icon == "soundcloud") { echo "selected"; } ?><?php echo ' value="soundcloud">Soundcloud</option>
			<option '; ?><?php if($overlay_icon == "space-shuttle") { echo "selected"; } ?><?php echo ' value="space-shuttle">Space-Shuttle</option>
			<option '; ?><?php if($overlay_icon == "spinner") { echo "selected"; } ?><?php echo ' value="spinner">Spinner</option>
			<option '; ?><?php if($overlay_icon == "spoon") { echo "selected"; } ?><?php echo ' value="spoon">Spoon</option>
			<option '; ?><?php if($overlay_icon == "spotify") { echo "selected"; } ?><?php echo ' value="spotify">Spotify</option>
			<option '; ?><?php if($overlay_icon == "square") { echo "selected"; } ?><?php echo ' value="square">Square</option>
			<option '; ?><?php if($overlay_icon == "square-o") { echo "selected"; } ?><?php echo ' value="square-o">Square-O</option>
			<option '; ?><?php if($overlay_icon == "stack-exchange") { echo "selected"; } ?><?php echo ' value="stack-exchange">Stack-Exchange</option>
			<option '; ?><?php if($overlay_icon == "stack-overflow") { echo "selected"; } ?><?php echo ' value="stack-overflow">Stack-Overflow</option>
			<option '; ?><?php if($overlay_icon == "star") { echo "selected"; } ?><?php echo ' value="star">Star</option>
			<option '; ?><?php if($overlay_icon == "star-half") { echo "selected"; } ?><?php echo ' value="star-half">Star-Half</option>
			<option '; ?><?php if($overlay_icon == "star-half-o") { echo "selected"; } ?><?php echo ' value="star-half-o">Star-Half-O</option>
			<option '; ?><?php if($overlay_icon == "star-o") { echo "selected"; } ?><?php echo ' value="star-o">Star-O</option>
			<option '; ?><?php if($overlay_icon == "steam") { echo "selected"; } ?><?php echo ' value="steam">Steam</option>
			<option '; ?><?php if($overlay_icon == "steam-square") { echo "selected"; } ?><?php echo ' value="steam-square">Steam-Square</option>
			<option '; ?><?php if($overlay_icon == "step-backward") { echo "selected"; } ?><?php echo ' value="step-backward">Step-Backward</option>
			<option '; ?><?php if($overlay_icon == "step-forward") { echo "selected"; } ?><?php echo ' value="step-forward">Step-Forward</option>
			<option '; ?><?php if($overlay_icon == "stethoscope") { echo "selected"; } ?><?php echo ' value="stethoscope">Stethoscope</option>
			<option '; ?><?php if($overlay_icon == "stop") { echo "selected"; } ?><?php echo ' value="stop">Stop</option>
			<option '; ?><?php if($overlay_icon == "strikethrough") { echo "selected"; } ?><?php echo ' value="strikethrough">Strikethrough</option>
			<option '; ?><?php if($overlay_icon == "stumbleupon") { echo "selected"; } ?><?php echo ' value="stumbleupon">Stumbleupon</option>
			<option '; ?><?php if($overlay_icon == "stumbleupon-circle") { echo "selected"; } ?><?php echo ' value="stumbleupon-circle">Stumbleupon-Circle</option>
			<option '; ?><?php if($overlay_icon == "subscript") { echo "selected"; } ?><?php echo ' value="subscript">Subscript</option>
			<option '; ?><?php if($overlay_icon == "suitcase") { echo "selected"; } ?><?php echo ' value="suitcase">Suitcase</option>
			<option '; ?><?php if($overlay_icon == "sun-o") { echo "selected"; } ?><?php echo ' value="sun-o">Sun-O</option>
			<option '; ?><?php if($overlay_icon == "superscript") { echo "selected"; } ?><?php echo ' value="superscript">Superscript</option>
			<option '; ?><?php if($overlay_icon == "table") { echo "selected"; } ?><?php echo ' value="table">Table</option>
			<option '; ?><?php if($overlay_icon == "tablet") { echo "selected"; } ?><?php echo ' value="tablet">Tablet</option>
			<option '; ?><?php if($overlay_icon == "tachometer") { echo "selected"; } ?><?php echo ' value="tachometer">Tachometer</option>
			<option '; ?><?php if($overlay_icon == "tag") { echo "selected"; } ?><?php echo ' value="tag">Tag</option>
			<option '; ?><?php if($overlay_icon == "tags") { echo "selected"; } ?><?php echo ' value="tags">Tags</option>
			<option '; ?><?php if($overlay_icon == "tasks") { echo "selected"; } ?><?php echo ' value="tasks">Tasks</option>
			<option '; ?><?php if($overlay_icon == "taxi") { echo "selected"; } ?><?php echo ' value="taxi">Taxi</option>
			<option '; ?><?php if($overlay_icon == "tencent-weibo") { echo "selected"; } ?><?php echo ' value="tencent-weibo">Tencent-Weibo</option>
			<option '; ?><?php if($overlay_icon == "terminal") { echo "selected"; } ?><?php echo ' value="terminal">Terminal</option>
			<option '; ?><?php if($overlay_icon == "text-height") { echo "selected"; } ?><?php echo ' value="text-height">Text-Height</option>
			<option '; ?><?php if($overlay_icon == "text-width") { echo "selected"; } ?><?php echo ' value="text-width">Text-Width</option>
			<option '; ?><?php if($overlay_icon == "th") { echo "selected"; } ?><?php echo ' value="th">Th</option>
			<option '; ?><?php if($overlay_icon == "th-large") { echo "selected"; } ?><?php echo ' value="th-large">Th-Large</option>
			<option '; ?><?php if($overlay_icon == "th-list") { echo "selected"; } ?><?php echo ' value="th-list">Th-List</option>
			<option '; ?><?php if($overlay_icon == "thumbs-down") { echo "selected"; } ?><?php echo ' value="thumbs-down">Thumbs-Down</option>
			<option '; ?><?php if($overlay_icon == "thumbs-o-down") { echo "selected"; } ?><?php echo ' value="thumbs-o-down">Thumbs-O-Down</option>
			<option '; ?><?php if($overlay_icon == "thumbs-o-up") { echo "selected"; } ?><?php echo ' value="thumbs-o-up">Thumbs-O-Up</option>
			<option '; ?><?php if($overlay_icon == "thumbs-up") { echo "selected"; } ?><?php echo ' value="thumbs-up">Thumbs-Up</option>
			<option '; ?><?php if($overlay_icon == "thumb-tack") { echo "selected"; } ?><?php echo ' value="thumb-tack">Thumb-Tack</option>
			<option '; ?><?php if($overlay_icon == "ticket") { echo "selected"; } ?><?php echo ' value="ticket">Ticket</option>
			<option '; ?><?php if($overlay_icon == "times") { echo "selected"; } ?><?php echo ' value="times">Times</option>
			<option '; ?><?php if($overlay_icon == "times-circle") { echo "selected"; } ?><?php echo ' value="times-circle">Times-Circle</option>
			<option '; ?><?php if($overlay_icon == "times-circle-o") { echo "selected"; } ?><?php echo ' value="times-circle-o">Times-Circle-O</option>
			<option '; ?><?php if($overlay_icon == "tint") { echo "selected"; } ?><?php echo ' value="tint">Tint</option>
			<option '; ?><?php if($overlay_icon == "trash-o") { echo "selected"; } ?><?php echo ' value="trash-o">Trash-O</option>
			<option '; ?><?php if($overlay_icon == "tree") { echo "selected"; } ?><?php echo ' value="tree">Tree</option>
			<option '; ?><?php if($overlay_icon == "trello") { echo "selected"; } ?><?php echo ' value="trello">Trello</option>
			<option '; ?><?php if($overlay_icon == "trophy") { echo "selected"; } ?><?php echo ' value="trophy">Trophy</option>
			<option '; ?><?php if($overlay_icon == "truck") { echo "selected"; } ?><?php echo ' value="truck">Truck</option>
			<option '; ?><?php if($overlay_icon == "try") { echo "selected"; } ?><?php echo ' value="try">Try</option>
			<option '; ?><?php if($overlay_icon == "tumblr") { echo "selected"; } ?><?php echo ' value="tumblr">Tumblr</option>
			<option '; ?><?php if($overlay_icon == "tumblr-square") { echo "selected"; } ?><?php echo ' value="tumblr-square">Tumblr-Square</option>
			<option '; ?><?php if($overlay_icon == "twitter") { echo "selected"; } ?><?php echo ' value="twitter">Twitter</option>
			<option '; ?><?php if($overlay_icon == "twitter-square") { echo "selected"; } ?><?php echo ' value="twitter-square">Twitter-Square</option>
			<option '; ?><?php if($overlay_icon == "umbrella") { echo "selected"; } ?><?php echo ' value="umbrella">Umbrella</option>
			<option '; ?><?php if($overlay_icon == "underline") { echo "selected"; } ?><?php echo ' value="underline">Underline</option>
			<option '; ?><?php if($overlay_icon == "undo") { echo "selected"; } ?><?php echo ' value="undo">Undo</option>
			<option '; ?><?php if($overlay_icon == "university") { echo "selected"; } ?><?php echo ' value="university">University</option>
			<option '; ?><?php if($overlay_icon == "unlock") { echo "selected"; } ?><?php echo ' value="unlock">Unlock</option>
			<option '; ?><?php if($overlay_icon == "unlock-alt") { echo "selected"; } ?><?php echo ' value="unlock-alt">Unlock-Alt</option>
			<option '; ?><?php if($overlay_icon == "upload") { echo "selected"; } ?><?php echo ' value="upload">Upload</option>
			<option '; ?><?php if($overlay_icon == "usd") { echo "selected"; } ?><?php echo ' value="usd">Usd</option>
			<option '; ?><?php if($overlay_icon == "user") { echo "selected"; } ?><?php echo ' value="user">User</option>
			<option '; ?><?php if($overlay_icon == "user-md") { echo "selected"; } ?><?php echo ' value="user-md">User-Md</option>
			<option '; ?><?php if($overlay_icon == "users") { echo "selected"; } ?><?php echo ' value="users">Users</option>
			<option '; ?><?php if($overlay_icon == "video-camera") { echo "selected"; } ?><?php echo ' value="video-camera">Video-Camera</option>
			<option '; ?><?php if($overlay_icon == "vimeo-square") { echo "selected"; } ?><?php echo ' value="vimeo-square">Vimeo-Square</option>
			<option '; ?><?php if($overlay_icon == "vine") { echo "selected"; } ?><?php echo ' value="vine">Vine</option>
			<option '; ?><?php if($overlay_icon == "vk") { echo "selected"; } ?><?php echo ' value="vk">Vk</option>
			<option '; ?><?php if($overlay_icon == "volume-down") { echo "selected"; } ?><?php echo ' value="volume-down">Volume-Down</option>
			<option '; ?><?php if($overlay_icon == "volume-off") { echo "selected"; } ?><?php echo ' value="volume-off">Volume-Off</option>
			<option '; ?><?php if($overlay_icon == "volume-up") { echo "selected"; } ?><?php echo ' value="volume-up">Volume-Up</option>
			<option '; ?><?php if($overlay_icon == "weibo") { echo "selected"; } ?><?php echo ' value="weibo">Weibo</option>
			<option '; ?><?php if($overlay_icon == "weixin") { echo "selected"; } ?><?php echo ' value="weixin">Weixin</option>
			<option '; ?><?php if($overlay_icon == "wheelchair") { echo "selected"; } ?><?php echo ' value="wheelchair">Wheelchair</option>
			<option '; ?><?php if($overlay_icon == "windows") { echo "selected"; } ?><?php echo ' value="windows">Windows</option>
			<option '; ?><?php if($overlay_icon == "wordpress") { echo "selected"; } ?><?php echo ' value="wordpress">Wordpress</option>
			<option '; ?><?php if($overlay_icon == "wrench") { echo "selected"; } ?><?php echo ' value="wrench">Wrench</option>
			<option '; ?><?php if($overlay_icon == "xing") { echo "selected"; } ?><?php echo ' value="xing">Xing</option>
			<option '; ?><?php if($overlay_icon == "xing-square") { echo "selected"; } ?><?php echo ' value="xing-square">Xing-Square</option>
			<option '; ?><?php if($overlay_icon == "yahoo") { echo "selected"; } ?><?php echo ' value="yahoo">Yahoo</option>
			<option '; ?><?php if($overlay_icon == "youtube") { echo "selected"; } ?><?php echo ' value="youtube">Youtube</option>
			<option '; ?><?php if($overlay_icon == "youtube-play") { echo "selected"; } ?><?php echo ' value="youtube-play">Youtube-Play</option>
			<option '; ?><?php if($overlay_icon == "youtube-square") { echo "selected"; } ?><?php echo ' value="youtube-square">Youtube-Square</option>
			</select>
		</p>';

		echo '<p><label for="' . $this->get_field_id('lightbox') . '" style="display: inline-block;width: 150px;">Add Lightbox?</label>&nbsp;<input id="' . $this->get_field_id('lightbox') . '" name="' . $this->get_field_name('lightbox') . '" type="checkbox" '; ?><?php if($lightbox == "on") { echo 'checked=checked'; } ?><?php echo ' /></p>';

		echo '<p><label for="' . $this->get_field_id('text_alt') . '" style="display: inline-block;width: 153px;">Alt Tag Text:</label><input class="widefat" id="' . $this->get_field_id('text_alt') . '" name="' . $this->get_field_name('text_alt') . '" type="text" value="' . esc_attr($text_alt) . '" style="display: inline-block;  width: 200px;margin: 0;" /></p>';

		echo '<p><label for="' . $this->get_field_id('animate') . '" style="display: inline-block;width: 150px;">Animation:</label>
			<select name="' . $this->get_field_name('animate') . '" id="' . $this->get_field_id('animate') . '" style="display: inline-block;width: 200px;margin: 0;">
			<option '; ?><?php if($animate == "none") { echo "selected"; } ?><?php echo ' value="none">None</option>
			<option '; ?><?php if($animate == "bounceIn") { echo "selected"; } ?><?php echo ' value="bounceIn">bounceIn</option>
			<option '; ?><?php if($animate == "bounceInDown") { echo "selected"; } ?><?php echo ' value="bounceInDown">bounceInDown</option>
			<option '; ?><?php if($animate == "bounceInUp") { echo "selected"; } ?><?php echo ' value="bounceInUp">bounceInUp</option>
			<option '; ?><?php if($animate == "bounceInLeft") { echo "selected"; } ?><?php echo ' value="bounceInLeft">bounceInLeft</option>
			<option '; ?><?php if($animate == "bounceInRight") { echo "selected"; } ?><?php echo ' value="bounceInRight">bounceInRight</option>
			<option '; ?><?php if($animate == "bounceOut") { echo "selected"; } ?><?php echo ' value="bounceOut">bounceOut</option>
			<option '; ?><?php if($animate == "bounceOutDown") { echo "selected"; } ?><?php echo ' value="bounceOutDown">bounceOutDown</option>
			<option '; ?><?php if($animate == "bounceOutUp") { echo "selected"; } ?><?php echo ' value="bounceOutUp">bounceOutUp</option>
			<option '; ?><?php if($animate == "bounceOutLeft") { echo "selected"; } ?><?php echo ' value="bounceOutLeft">bounceOutLeft</option>
			<option '; ?><?php if($animate == "bounceOutRight") { echo "selected"; } ?><?php echo ' value="bounceOutRight">bounceOutRight</option>
			<option '; ?><?php if($animate == "flipInX") { echo "selected"; } ?><?php echo ' value="flipInX">flipInX</option>
			<option '; ?><?php if($animate == "flipOutX") { echo "selected"; } ?><?php echo ' value="flipOutX">flipOutX</option>
			<option '; ?><?php if($animate == "flipInY") { echo "selected"; } ?><?php echo ' value="flipInY">flipInY</option>
			<option '; ?><?php if($animate == "flipOutY") { echo "selected"; } ?><?php echo ' value="flipOutY">flipOutY</option>
			<option '; ?><?php if($animate == "fadeIn") { echo "selected"; } ?><?php echo ' value="fadeIn">fadeIn</option>
			<option '; ?><?php if($animate == "fadeInUp") { echo "selected"; } ?><?php echo ' value="fadeInUp">fadeInUp</option>
			<option '; ?><?php if($animate == "fadeInDown") { echo "selected"; } ?><?php echo ' value="fadeInDown">fadeInDown</option>
			<option '; ?><?php if($animate == "fadeInLeft") { echo "selected"; } ?><?php echo ' value="fadeInLeft">fadeInLeft</option>
			<option '; ?><?php if($animate == "fadeInRight") { echo "selected"; } ?><?php echo ' value="fadeInRight">fadeInRight</option>
			<option '; ?><?php if($animate == "fadeInUpBig") { echo "selected"; } ?><?php echo ' value="fadeInUpBig">fadeInUpBig</option>
			<option '; ?><?php if($animate == "fadeInDownBig") { echo "selected"; } ?><?php echo ' value="fadeInDownBig">fadeInDownBig</option>
			<option '; ?><?php if($animate == "fadeInLeftBig") { echo "selected"; } ?><?php echo ' value="fadeInLeftBig">fadeInLeftBig</option>
			<option '; ?><?php if($animate == "fadeInRightBig") { echo "selected"; } ?><?php echo ' value="fadeInRightBig">fadeInRightBig</option>
			<option '; ?><?php if($animate == "fadeOut") { echo "selected"; } ?><?php echo ' value="fadeOut">fadeOut</option>
			<option '; ?><?php if($animate == "fadeOutUp") { echo "selected"; } ?><?php echo ' value="fadeOutUp">fadeOutUp</option>
			<option '; ?><?php if($animate == "fadeOutDown") { echo "selected"; } ?><?php echo ' value="fadeOutDown">fadeOutDown</option>
			<option '; ?><?php if($animate == "fadeOutLeft") { echo "selected"; } ?><?php echo ' value="fadeOutLeft">fadeOutLeft</option>
			<option '; ?><?php if($animate == "fadeOutRight") { echo "selected"; } ?><?php echo ' value="fadeOutRight">fadeOutRight</option>
			<option '; ?><?php if($animate == "fadeOutUpBig") { echo "selected"; } ?><?php echo ' value="fadeOutUpBig">fadeOutUpBig</option>
			<option '; ?><?php if($animate == "fadeOutDownBig") { echo "selected"; } ?><?php echo ' value="fadeOutDownBig">fadeOutDownBig</option>
			<option '; ?><?php if($animate == "fadeOutLeftBig") { echo "selected"; } ?><?php echo ' value="fadeOutLeftBig">fadeOutLeftBig</option>
			<option '; ?><?php if($animate == "fadeOutRightBig") { echo "selected"; } ?><?php echo ' value="fadeOutRightBig">fadeOutRightBig</option>
			<option '; ?><?php if($animate == "hinge") { echo "selected"; } ?><?php echo ' value="hinge">hinge</option>
			<option '; ?><?php if($animate == "lightSpeedIn") { echo "selected"; } ?><?php echo ' value="lightSpeedIn">lightSpeedIn</option>
			<option '; ?><?php if($animate == "lightSpeedOut") { echo "selected"; } ?><?php echo ' value="lightSpeedOut">lightSpeedOut</option>
			<option '; ?><?php if($animate == "rollIn") { echo "selected"; } ?><?php echo ' value="rollIn">rollIn</option>
			<option '; ?><?php if($animate == "rollOut") { echo "selected"; } ?><?php echo ' value="rollOut">rollOut</option>
			<option '; ?><?php if($animate == "rotateIn") { echo "selected"; } ?><?php echo ' value="rotateIn">rotateIn</option>
			<option '; ?><?php if($animate == "rotateInDownLeft") { echo "selected"; } ?><?php echo ' value="rotateInDownLeft">rotateInDownLeft</option>
			<option '; ?><?php if($animate == "rotateInDownRight") { echo "selected"; } ?><?php echo ' value="rotateInDownRight">rotateInDownRight</option>
			<option '; ?><?php if($animate == "rotateInUpLeft") { echo "selected"; } ?><?php echo ' value="rotateInUpLeft">rotateInUpLeft</option>
			<option '; ?><?php if($animate == "rotateInUpRight") { echo "selected"; } ?><?php echo ' value="rotateInUpRight">rotateInUpRight</option>
			<option '; ?><?php if($animate == "rotateOut") { echo "selected"; } ?><?php echo ' value="rotateOut">rotateOut</option>
			<option '; ?><?php if($animate == "rotateOutDownLeft") { echo "selected"; } ?><?php echo ' value="rotateOutDownLeft">rotateOutDownLeft</option>
			<option '; ?><?php if($animate == "rotateOutDownRight") { echo "selected"; } ?><?php echo ' value="rotateOutDownRight">rotateOutDownRight</option>
			<option '; ?><?php if($animate == "rotateOutUpLeft") { echo "selected"; } ?><?php echo ' value="rotateOutUpLeft">rotateOutUpLeft</option>
			<option '; ?><?php if($animate == "rotateOutUpRight") { echo "selected"; } ?><?php echo ' value="rotateOutUpRight">rotateOutUpRight</option>
			<option '; ?><?php if($animate == "slideInDown") { echo "selected"; } ?><?php echo ' value="slideInDown">slideInDown</option>
			<option '; ?><?php if($animate == "slideInLeft") { echo "selected"; } ?><?php echo ' value="slideInLeft">slideInLeft</option>
			<option '; ?><?php if($animate == "slideInRight") { echo "selected"; } ?><?php echo ' value="slideInRight">slideInRight</option>
			<option '; ?><?php if($animate == "slideOutUp") { echo "selected"; } ?><?php echo ' value="slideOutUp">slideOutUp</option>
			<option '; ?><?php if($animate == "slideOutLeft") { echo "selected"; } ?><?php echo ' value="slideOutLeft">slideOutLeft</option>
			<option '; ?><?php if($animate == "slideOutRight") { echo "selected"; } ?><?php echo ' value="slideOutRight">slideOutRight</option>
			</select>
		</p>';
	
		echo '<p><label for="' . $this->get_field_id('delay') . '" style="display: inline-block;width: 153px;">Animation Delay (ms):</label><input class="widefat" id="' . $this->get_field_id('delay') . '" name="' . $this->get_field_name('delay') . '" type="text" value="' . esc_attr($delay) . '" style="display: inline-block;  width: 200px;margin: 0;" /></p>';

		// enqueue script to hide default carousel portfolio option
		wp_enqueue_style( 'image-backend', get_template_directory_uri() . '/lib/widgets_builder/image/css/image-backend.css', '', '1.0.0' );
	}

	// Assign variable values.
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, self::get_defaults() );
		$instance['title']          = strip_tags($new_instance['title']);
		$instance['align']          = $new_instance['align'];
		$instance['link']           = $new_instance['link'];
		$instance['overlay_enable'] = $new_instance['overlay_enable'];
		$instance['overlay_icon']   = $new_instance['overlay_icon'];
		$instance['size']           = $new_instance['size'];
		$instance['lightbox']       = $new_instance['lightbox'];
		$instance['text_alt']       = $new_instance['text_alt'];
		$instance['animate']        = $new_instance['animate'];
		$instance['delay']          = $new_instance['delay'];

		// Reverse compatibility with $image, now called $attachement_id
		$instance['attachment_id'] = abs( $new_instance['attachment_id'] );
		$instance['imageurl'] = $new_instance['imageurl']; // deprecated

		$instance['aspect_ratio'] = $this->get_image_aspect_ratio( $instance );

		return $instance;
	}

	// Output widget to front-end.
	function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( (array) $instance, self::get_defaults() );
		if ( !empty( $instance['imageurl'] ) || !empty( $instance['attachment_id'] ) ) {

			$align         = NULL;
			$lightbox      = NULL;
			$animate       = NULL;
			$delay         = NULL;
			$animate_start = NULL;
			$animate_end   = NULL;

			$instance['title']       = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
			$instance['align']       = $instance['align'];
			$instance['link']        = apply_filters( 'image_widget_image_link', esc_url( $instance['link'] ), $args, $instance );
			$overlay_enable          = $instance['overlay_enable'];
			$overlay_icon            = $instance['overlay_icon'];
			$lightbox                = $instance['lightbox'];
			$text_alt                = $instance['text_alt'];
			$animate                 = $instance['animate'];
			$delay                   = $instance['delay'];

			if ( !defined( 'IMAGE_WIDGET_COMPATIBILITY_TEST' ) ) {
				$instance['attachment_id'] = ( $instance['attachment_id'] > 0 ) ? $instance['attachment_id'] : $instance['image'];
				$instance['attachment_id'] = apply_filters( 'image_widget_image_attachment_id', abs( $instance['attachment_id'] ), $args, $instance );
				$instance['size']          = apply_filters( 'image_widget_image_size', esc_attr( $instance['size'] ), $args, $instance );
			}
			$instance['imageurl'] = apply_filters( 'image_widget_image_url', esc_url( $instance['imageurl'] ), $args, $instance );

			// No longer using extracted vars. This is here for backwards compatibility.
			extract( $instance );
			
			// Assign animation variables
			if ( ! empty( $animate ) and $animate !== 'none' ) {
				$animate_start = '<div class="animated start-' . $animate . '" title="' . $delay . '">';
				$animate_end   = '</div><div class="clearboth"></div>';
			}
			
			$image_img = wp_get_attachment_image_src( $attachment_id, $size, true );
			$image_img_full = wp_get_attachment_image_src( $attachment_id, 'full', true );

			if ( $align == 'left' ) {
				$align = ' style="text-align: left;"';
			} else if ( $align == 'right' ) {
				$align = ' style="text-align: right;"';
			} else if ( $align == 'center' ) {
				$align = ' style="text-align: center;"';
			} else {
				$align = NULL;				
			}

			// Assign overlay icon
			if ( empty( $overlay_icon ) ) {
				$overlay_icon = 'fa fa-hover-link';
			} else {
				$overlay_icon = 'fa fa-' . $overlay_icon;
			}

			echo $animate_start;

			if ( $lightbox == 'on' ) {
				echo do_shortcode( '[image image="' . $image_img_full[0] . '" thumb="' . $image_img[0] . '" title="' . $text_alt . '"]' );
			} else if ( empty( $link ) ) {
				echo '<p' . $align .'><img src="' . $image_img[0] . '" alt="' . $text_alt . '" / ></p>';					
			} else if ( $overlay_enable == 'on' ) {
				echo '<div' . $align . ' class="sc-image sc-carousel">';
				echo '<div class="entry-header">';
				echo '<a href="' . $link . '"><img src="' . $image_img[0] . '" alt="' . $text_alt . '" / >';
				echo '<div class="image-overlay overlay2">';
				echo '<div class="image-overlay-inner">';
				echo '<div class="prettyphoto-wrap"><span><i class="' . $overlay_icon . '"></i></span></div>';
				echo '</div>';
				echo '</div>';
				echo '</a></div>';
				echo '</div>';
			} else {
				echo '<a href="' . $link . '"><img src="' . $image_img[0] . '" alt="' . $text_alt . '" / ></a>';
			}

			echo $animate_end;

			if ( ! empty( $animate ) and $animate !== 'none' ) {
				
				if ( ! wp_script_is( 'animate-js', 'enqueued' ) ) {
				// Enque styles only if widget is being used
				wp_enqueue_style( 'animate-css', plugin_dir_url(SITEORIGIN_PANELS_BASE_FILE) . 'inc/plugins/animate.css/animate.css', array(), '1.0' );
				wp_enqueue_style( 'animate-thinkup-css', plugin_dir_url(SITEORIGIN_PANELS_BASE_FILE) . 'widgets-builder/animation/css/animate-thinkup-panels.css', array(), '1.0' );

				if ( ! wp_script_is( 'waypoints', 'enqueued' ) ) {
				// Enque waypoints only if widget is being used
				wp_enqueue_script( 'waypoints', plugin_dir_url(SITEORIGIN_PANELS_BASE_FILE) . 'inc/plugins/waypoints/waypoints.min.js', array( 'jquery' ), '2.0.3', 'true'  );
				wp_enqueue_script( 'waypoints-sticky', plugin_dir_url(SITEORIGIN_PANELS_BASE_FILE) . 'inc/plugins/waypoints/waypoints-sticky.min.js', array( 'jquery' ), '2.0.3', 'true'  );
				}

				// Enque scripts only if widget is being used
				wp_enqueue_script( 'animate-js', plugin_dir_url(SITEORIGIN_PANELS_BASE_FILE) . 'widgets-builder/animation/js/animate-thinkup-panels.js', array( 'jquery' ), '1.1', true );
				}
			}
		}
	}

	// Render an array of default values.
	private static function get_defaults() {

		$defaults = array(
			'title'       => '',
			'link'        => '',
			'image'       => 0, // reverse compatible - now attachement_id
			'imageurl'    => '', // reverse compatible.
		);

		return $defaults;
	}

	// Establish the aspect ratio of the image.
	private function get_image_aspect_ratio( $instance ) {
		if ( !empty( $instance['aspect_ratio'] ) ) {
			return abs( $instance['aspect_ratio'] );
		} else {
			$attachment_id = ( !empty($instance['attachment_id']) ) ? $instance['attachment_id'] : $instance['image'];
			if ( !empty($attachment_id) ) {
				$image_details = wp_get_attachment_image_src( $attachment_id, 'full' );
				if ($image_details) {
					return ( $image_details[1]/$image_details[2] );
				}
			}
		}
	}
}
add_action( 'widgets_init', function() { return register_widget( "thinkup_builder_imagetheme" ); } );


?>