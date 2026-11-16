<?php
/**
 * Plugin Name: Prime Estates Setup
 * Description: Builds a complete real estate website on activation: Property listings with search filters, enquiry forms with an Enquiries inbox, Home, Properties, Services, About, Contact and Privacy pages, menu, styling, SEO markup and a settings page for business details.
 * Version: 1.1
 * Author: Prime Estates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =====================================================================
 * 1. DATA (edit these lists to change services, agents, steps)
 * ===================================================================== */
/* Line icons (inline SVG) used instead of emoji. */
function pe_icon( $name ) {
	$paths = array(
		'home'   => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
		'key'    => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="M10.7 12.3L21 2"/><path d="M15.5 7.5l3 3"/>',
		'tag'    => '<path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><path d="M7.5 7.5h.01"/>',
		'chart'  => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
		'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
		'file'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h8"/>',
		'pin'    => '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="pe-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

function pe_services() {
	return array(
		array( 'Buy', 'Find the right home, apartment or land. We shortlist properties that match your budget and arrange viewings.', 'home' ),
		array( 'Rent', 'Long and short term rentals in good locations, with clear terms and fair prices.', 'key' ),
		array( 'Sell', 'We price, advertise and show your property to serious buyers, and handle the paperwork with you.', 'tag' ),
		array( 'Valuation', 'A free, honest market valuation based on recent sales and rents in your area.', 'chart' ),
	);
}

function pe_steps() {
	return array(
		array( 'Tell us what you need', 'Share your budget, location and type of property.' ),
		array( 'We shortlist', 'We send you matching properties and honest advice.' ),
		array( 'Visit with an agent', 'We arrange viewings at times that suit you.' ),
		array( 'Close with confidence', 'We guide you through offers, documents and handover.' ),
	);
}

function pe_agents() {
	return array(
		array( 'Ruwan Jayawardena', 'Principal Agent', 'Residential sales and valuations' ),
		array( 'Thilini Wickramasinghe', 'Senior Agent', 'Apartments and rentals' ),
		array( 'Mohamed Rizwan', 'Land & Commercial Agent', 'Land, offices and shops' ),
	);
}

function pe_interests() {
	return array( 'Buying a property', 'Renting a property', 'Selling my property', 'Property valuation', 'Other' );
}

function pe_statuses() {
	return array( 'Available', 'Under Offer', 'Sold', 'Rented' );
}

function pe_enquiry_statuses() {
	return array( 'New', 'Contacted', 'Viewing booked', 'Closed' );
}

/* =====================================================================
 * 2. SETTINGS (Settings > Prime Estates)
 * ===================================================================== */
function pe_fields() {
	return array(
		'name'         => array( 'Business name', 'text', 'Prime Estates' ),
		'description'  => array( 'Short description (used for search engines)', 'textarea', 'Homes, apartments, land and commercial properties for sale and rent. Free valuations and trusted local agents.' ),
		'phone'        => array( 'Phone number', 'text', '+94 77 123 4567' ),
		'whatsapp'     => array( 'WhatsApp number (digits only, with country code, e.g. 94771234567)', 'digits', '94771234567' ),
		'email'        => array( 'Email address (enquiries are sent here)', 'email', '' ),
		'address'      => array( 'Office address', 'text', '123 Main Street, Colombo' ),
		'map'          => array( 'Google Maps embed (paste the iframe code or its src link)', 'map', '' ),
		'hours_week'   => array( 'Office hours: Monday - Friday', 'text', '9:00 AM - 6:00 PM' ),
		'hours_sat'    => array( 'Office hours: Saturday', 'text', '9:00 AM - 2:00 PM' ),
		'hours_sun'    => array( 'Office hours: Sunday', 'text', 'By appointment' ),
		'facebook'     => array( 'Facebook page URL', 'url', '' ),
		'instagram'    => array( 'Instagram URL', 'url', '' ),
		'youtube'      => array( 'YouTube URL', 'url', '' ),
		'tiktok'       => array( 'TikTok URL', 'url', '' ),
		'testimonials' => array( 'Client reviews: one per line as  Name | Review  (real reviews only; the section is hidden when empty)', 'textarea', '' ),
	);
}

function pe_opt( $key ) {
	$saved = get_option( 'pe_settings', array() );
	if ( is_array( $saved ) && isset( $saved[ $key ] ) && '' !== $saved[ $key ] ) {
		return $saved[ $key ];
	}
	if ( 'email' === $key ) {
		return get_option( 'admin_email' );
	}
	$f = pe_fields();
	return isset( $f[ $key ] ) ? $f[ $key ][2] : '';
}

function pe_sanitize_settings( $input ) {
	$out   = array();
	$input = is_array( $input ) ? $input : array();
	foreach ( pe_fields() as $key => $f ) {
		$v = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';
		switch ( $f[1] ) {
			case 'email':
				$v = sanitize_email( $v );
				break;
			case 'url':
				$v = esc_url_raw( $v );
				break;
			case 'digits':
				$v = preg_replace( '/\D/', '', $v );
				break;
			case 'textarea':
				$v = sanitize_textarea_field( $v );
				break;
			case 'map':
				if ( preg_match( '/src=["\']([^"\']+)["\']/i', $v, $m ) ) {
					$v = $m[1];
				}
				$v = esc_url_raw( trim( $v ) );
				if ( 0 !== strpos( $v, 'https://www.google.com/maps/embed' ) ) {
					$v = '';
				}
				break;
			default:
				$v = sanitize_text_field( $v );
		}
		$out[ $key ] = $v;
	}
	return $out;
}

add_action( 'admin_init', function () {
	register_setting( 'pe_settings_group', 'pe_settings', array( 'sanitize_callback' => 'pe_sanitize_settings' ) );
} );

add_action( 'admin_menu', function () {
	add_options_page( 'Prime Estates', 'Prime Estates', 'manage_options', 'pe-settings', 'pe_settings_page' );
} );

function pe_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>Prime Estates settings</h1><p>These details appear on your pages, buttons and search engine data.</p>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'pe_settings_group' );
	echo '<table class="form-table" role="presentation">';
	foreach ( pe_fields() as $key => $f ) {
		$val = pe_opt( $key );
		echo '<tr><th scope="row"><label for="pe_' . esc_attr( $key ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
		if ( 'textarea' === $f[1] ) {
			echo '<textarea class="large-text" rows="4" id="pe_' . esc_attr( $key ) . '" name="pe_settings[' . esc_attr( $key ) . ']">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input class="regular-text" type="text" id="pe_' . esc_attr( $key ) . '" name="pe_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '">';
		}
		echo '</td></tr>';
	}
	echo '</table>';
	submit_button();
	echo '</form></div>';
}

/* =====================================================================
 * 3. PROPERTIES (post type, taxonomies, details box)
 * ===================================================================== */
function pe_register_types() {
	register_post_type( 'pe_property', array(
		'labels'        => array( 'name' => 'Properties', 'singular_name' => 'Property', 'menu_name' => 'Properties', 'add_new_item' => 'Add New Property', 'edit_item' => 'Edit Property', 'not_found' => 'No properties yet.' ),
		'public'        => true,
		'has_archive'   => false,
		'rewrite'       => array( 'slug' => 'property' ),
		'menu_icon'     => 'dashicons-building',
		'menu_position' => 25,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'  => true,
	) );
	register_taxonomy( 'pe_type', 'pe_property', array(
		'labels'            => array( 'name' => 'Property Types', 'singular_name' => 'Property Type' ),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'property-type' ),
		'show_in_rest'      => true,
	) );
	register_taxonomy( 'pe_location', 'pe_property', array(
		'labels'            => array( 'name' => 'Locations', 'singular_name' => 'Location' ),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'location' ),
		'show_in_rest'      => true,
	) );
	register_post_type( 'pe_enquiry', array(
		'labels'        => array( 'name' => 'Enquiries', 'singular_name' => 'Enquiry', 'menu_name' => 'Enquiries', 'not_found' => 'No enquiries yet.' ),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-email-alt',
		'menu_position' => 26,
		'supports'      => array( 'title', 'editor' ),
		'capabilities'  => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'  => true,
	) );
}
add_action( 'init', 'pe_register_types' );

function pe_meta_fields() {
	return array(
		'pe_price'   => 'Price (Rs.; monthly rent for rentals; 0 = price on request)',
		'pe_beds'    => 'Bedrooms',
		'pe_baths'   => 'Bathrooms',
		'pe_area'    => 'Floor area (sq ft)',
		'pe_land'    => 'Land size (perches)',
		'pe_address' => 'Street address (shown on the property page)',
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'pe_details', 'Property details', 'pe_details_box', 'pe_property', 'normal', 'high' );
	add_meta_box( 'pe_enq_status_box', 'Enquiry status', function ( $post ) {
		$s = get_post_meta( $post->ID, 'pe_e_status', true );
		$s = $s ? $s : 'New';
		wp_nonce_field( 'pe_enq_status', 'pe_enq_nonce' );
		echo '<select name="pe_e_status">';
		foreach ( pe_enquiry_statuses() as $opt ) {
			echo '<option value="' . esc_attr( $opt ) . '"' . selected( $s, $opt, false ) . '>' . esc_html( $opt ) . '</option>';
		}
		echo '</select>';
	}, 'pe_enquiry', 'side' );
} );

function pe_details_box( $post ) {
	wp_nonce_field( 'pe_details_save', 'pe_details_nonce' );
	$listing  = get_post_meta( $post->ID, 'pe_listing', true );
	$status   = get_post_meta( $post->ID, 'pe_status', true );
	$featured = get_post_meta( $post->ID, 'pe_featured', true );
	echo '<table class="form-table" role="presentation">';
	echo '<tr><th><label for="pe_listing">Listing type</label></th><td><select id="pe_listing" name="pe_listing"><option value="sale"' . selected( $listing, 'sale', false ) . '>For Sale</option><option value="rent"' . selected( $listing, 'rent', false ) . '>For Rent</option></select></td></tr>';
	echo '<tr><th><label for="pe_status">Status</label></th><td><select id="pe_status" name="pe_status">';
	foreach ( pe_statuses() as $s ) {
		echo '<option value="' . esc_attr( $s ) . '"' . selected( $status ? $status : 'Available', $s, false ) . '>' . esc_html( $s ) . '</option>';
	}
	echo '</select></td></tr>';
	foreach ( pe_meta_fields() as $key => $label ) {
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text" type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( get_post_meta( $post->ID, $key, true ) ) . '"></td></tr>';
	}
	echo '<tr><th>Featured</th><td><label><input type="checkbox" name="pe_featured" value="1"' . checked( $featured, '1', false ) . '> Show on the home page</label></td></tr>';
	echo '</table><p>Tip: set a Featured image (right side) and choose a Property Type and Location.</p>';
}

add_action( 'save_post_pe_property', function ( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['pe_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pe_details_nonce'] ) ), 'pe_details_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$listing = isset( $_POST['pe_listing'] ) ? sanitize_text_field( wp_unslash( $_POST['pe_listing'] ) ) : 'sale';
	update_post_meta( $post_id, 'pe_listing', in_array( $listing, array( 'sale', 'rent' ), true ) ? $listing : 'sale' );
	$status = isset( $_POST['pe_status'] ) ? sanitize_text_field( wp_unslash( $_POST['pe_status'] ) ) : 'Available';
	update_post_meta( $post_id, 'pe_status', in_array( $status, pe_statuses(), true ) ? $status : 'Available' );
	foreach ( array( 'pe_price', 'pe_beds', 'pe_baths', 'pe_area', 'pe_land' ) as $k ) {
		$v = isset( $_POST[ $k ] ) ? preg_replace( '/[^\d.]/', '', sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) : '';
		update_post_meta( $post_id, $k, $v );
	}
	$addr = isset( $_POST['pe_address'] ) ? sanitize_text_field( wp_unslash( $_POST['pe_address'] ) ) : '';
	update_post_meta( $post_id, 'pe_address', $addr );
	update_post_meta( $post_id, 'pe_featured', ! empty( $_POST['pe_featured'] ) ? '1' : '0' );
} );

add_filter( 'manage_pe_property_posts_columns', function ( $cols ) {
	return array( 'cb' => '<input type="checkbox">', 'title' => 'Property', 'pe_price' => 'Price', 'pe_listing' => 'Listing', 'pe_status' => 'Status', 'taxonomy-pe_type' => 'Type', 'taxonomy-pe_location' => 'Location', 'date' => 'Date' );
} );

add_action( 'manage_pe_property_posts_custom_column', function ( $col, $id ) {
	if ( 'pe_price' === $col ) {
		echo esc_html( pe_price_text( $id ) );
	} elseif ( 'pe_listing' === $col ) {
		echo esc_html( 'rent' === get_post_meta( $id, 'pe_listing', true ) ? 'For Rent' : 'For Sale' );
	} elseif ( 'pe_status' === $col ) {
		$s = get_post_meta( $id, 'pe_status', true );
		echo esc_html( $s ? $s : 'Available' );
	}
}, 10, 2 );

function pe_price_text( $id ) {
	$price = (float) get_post_meta( $id, 'pe_price', true );
	if ( $price <= 0 ) {
		return 'Price on request';
	}
	$text = 'Rs. ' . number_format( $price );
	return 'rent' === get_post_meta( $id, 'pe_listing', true ) ? $text . ' / month' : $text;
}

function pe_first_term( $id, $tax ) {
	$terms = get_the_terms( $id, $tax );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
}

function pe_specs_html( $id ) {
	$out = '';
	foreach ( array( 'pe_beds' => 'Beds', 'pe_baths' => 'Baths', 'pe_area' => 'sq ft', 'pe_land' => 'perches' ) as $k => $label ) {
		$v = get_post_meta( $id, $k, true );
		if ( '' !== $v && (float) $v > 0 ) {
			$out .= '<span><strong>' . esc_html( $v + 0 ) . '</strong> ' . esc_html( $label ) . '</span>';
		}
	}
	return $out;
}

function pe_badge_html( $id ) {
	$status = get_post_meta( $id, 'pe_status', true );
	if ( in_array( $status, array( 'Sold', 'Rented', 'Under Offer' ), true ) ) {
		return '<span class="pe-badge pe-badge--closed">' . esc_html( $status ) . '</span>';
	}
	return 'rent' === get_post_meta( $id, 'pe_listing', true ) ? '<span class="pe-badge pe-badge--rent">For Rent</span>' : '<span class="pe-badge">For Sale</span>';
}

function pe_card( $id ) {
	$thumb = has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy' ) ) : '<div class="pe-noimg" aria-hidden="true">' . pe_icon( 'home' ) . '</div>';
	$loc   = pe_first_term( $id, 'pe_location' );
	return '<article class="pe-card"><a class="pe-card-img" href="' . esc_url( get_permalink( $id ) ) . '">' . $thumb . pe_badge_html( $id ) . '</a>'
		. '<div class="pe-card-body"><div class="pe-price">' . esc_html( pe_price_text( $id ) ) . '</div>'
		. '<h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>'
		. ( $loc ? '<p class="pe-loc">' . pe_icon( 'pin' ) . esc_html( $loc ) . '</p>' : '' )
		. '<div class="pe-specs">' . pe_specs_html( $id ) . '</div></div></article>';
}

/* ---------- Property query and listing shortcodes ---------- */
function pe_query( $filters, $paged = 1, $limit = 9 ) {
	$args = array(
		'post_type'      => 'pe_property',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'paged'          => $paged,
		'meta_query'     => array( 'relation' => 'AND' ),
		'tax_query'      => array( 'relation' => 'AND' ),
	);
	if ( ! empty( $filters['q'] ) ) {
		$args['s'] = $filters['q'];
	}
	if ( ! empty( $filters['listing'] ) ) {
		$args['meta_query'][] = array( 'key' => 'pe_listing', 'value' => $filters['listing'] );
	}
	if ( ! empty( $filters['featured'] ) ) {
		$args['meta_query'][] = array( 'key' => 'pe_featured', 'value' => '1' );
	}
	if ( ! empty( $filters['min_price'] ) ) {
		$args['meta_query'][] = array( 'key' => 'pe_price', 'value' => (float) $filters['min_price'], 'compare' => '>=', 'type' => 'NUMERIC' );
	}
	if ( ! empty( $filters['max_price'] ) ) {
		$args['meta_query'][] = array( 'key' => 'pe_price', 'value' => (float) $filters['max_price'], 'compare' => '<=', 'type' => 'NUMERIC' );
	}
	if ( ! empty( $filters['beds'] ) ) {
		$args['meta_query'][] = array( 'key' => 'pe_beds', 'value' => (int) $filters['beds'], 'compare' => '>=', 'type' => 'NUMERIC' );
	}
	foreach ( array( 'type' => 'pe_type', 'location' => 'pe_location' ) as $k => $tax ) {
		if ( ! empty( $filters[ $k ] ) ) {
			$args['tax_query'][] = array( 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $filters[ $k ] );
		}
	}
	return new WP_Query( $args );
}

function pe_get_filters() {
	$f = array();
	foreach ( array( 'q', 'listing', 'type', 'location', 'min_price', 'max_price', 'beds' ) as $k ) {
		$f[ $k ] = isset( $_GET[ $k ] ) ? sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) : '';
	}
	if ( ! in_array( $f['listing'], array( 'sale', 'rent' ), true ) ) {
		$f['listing'] = '';
	}
	return $f;
}

function pe_term_options( $tax, $current ) {
	$out   = '';
	$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
	if ( is_array( $terms ) ) {
		foreach ( $terms as $t ) {
			$out .= '<option value="' . esc_attr( $t->slug ) . '"' . selected( $current, $t->slug, false ) . '>' . esc_html( $t->name ) . '</option>';
		}
	}
	return $out;
}

add_shortcode( 'pe_search', function () {
	$f = pe_get_filters();
	ob_start();
	?>
	<form class="pe-search" method="get" action="<?php echo esc_url( home_url( '/properties/' ) ); ?>">
		<label>Keyword <input type="text" name="q" value="<?php echo esc_attr( $f['q'] ); ?>" placeholder="e.g. garden, near school"></label>
		<label>I want to
			<select name="listing"><option value="">Buy or Rent</option><option value="sale"<?php selected( $f['listing'], 'sale' ); ?>>Buy</option><option value="rent"<?php selected( $f['listing'], 'rent' ); ?>>Rent</option></select>
		</label>
		<label>Property type <select name="type"><option value="">Any type</option><?php echo pe_term_options( 'pe_type', $f['type'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select></label>
		<label>Location <select name="location"><option value="">Any location</option><?php echo pe_term_options( 'pe_location', $f['location'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select></label>
		<label>Min price (Rs.) <input type="number" name="min_price" min="0" step="1" value="<?php echo esc_attr( $f['min_price'] ); ?>"></label>
		<label>Max price (Rs.) <input type="number" name="max_price" min="0" step="1" value="<?php echo esc_attr( $f['max_price'] ); ?>"></label>
		<label>Bedrooms
			<select name="beds"><option value="">Any</option><?php foreach ( array( 1, 2, 3, 4, 5 ) as $b ) : ?><option value="<?php echo (int) $b; ?>"<?php selected( $f['beds'], (string) $b ); ?>><?php echo (int) $b; ?>+</option><?php endforeach; ?></select>
		</label>
		<button type="submit">Search properties</button>
	</form>
	<?php
	return ob_get_clean();
} );

add_shortcode( 'pe_properties', function ( $atts ) {
	$a = shortcode_atts( array( 'limit' => 6, 'featured' => '', 'listing' => '' ), $atts );
	$q = pe_query( array( 'featured' => $a['featured'], 'listing' => $a['listing'] ), 1, (int) $a['limit'] );
	if ( ! $q->have_posts() && $a['featured'] ) {
		$q = pe_query( array(), 1, (int) $a['limit'] ); // Nothing featured yet: show the latest.
	}
	if ( ! $q->have_posts() ) {
		return '<p class="pe-empty">New properties are coming soon.</p>';
	}
	$out = '<div class="pe-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$out .= pe_card( get_the_ID() );
	}
	wp_reset_postdata();
	return $out . '</div>';
} );

add_shortcode( 'pe_listings', function () {
	$f     = pe_get_filters();
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$q     = pe_query( $f, $paged, 9 );
	$out   = do_shortcode( '[pe_search]' );
	$out  .= '<p class="pe-count">' . (int) $q->found_posts . ' ' . ( 1 === (int) $q->found_posts ? 'property' : 'properties' ) . ' found</p>';
	if ( ! $q->have_posts() ) {
		return $out . '<p class="pe-empty">No properties match your search. Try changing the filters, or <a href="' . esc_url( home_url( '/contact/' ) ) . '">tell us what you need</a>.</p>';
	}
	$out .= '<div class="pe-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$out .= pe_card( get_the_ID() );
	}
	wp_reset_postdata();
	$out .= '</div>';
	$links = paginate_links( array(
		'base'      => trailingslashit( get_permalink() ) . '%_%',
		'format'    => 'page/%#%/',
		'current'   => $paged,
		'total'     => (int) $q->max_num_pages,
		'add_args'  => array_filter( $f ),
		'prev_text' => '&laquo; Prev',
		'next_text' => 'Next &raquo;',
	) );
	return $out . ( $links ? '<nav class="pe-pager" aria-label="Pages">' . $links . '</nav>' : '' );
} );

/* ---------- Single property page: details above, enquiry below ---------- */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'pe_property' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$id   = get_the_ID();
	$head = '<div class="pe-single-head">' . pe_badge_html( $id ) . '<div class="pe-price pe-price--big">' . esc_html( pe_price_text( $id ) ) . '</div>';
	$loc  = pe_first_term( $id, 'pe_location' );
	$addr = get_post_meta( $id, 'pe_address', true );
	if ( $addr || $loc ) {
		$head .= '<p class="pe-loc">' . pe_icon( 'pin' ) . esc_html( trim( $addr . ( $addr && $loc ? ', ' : '' ) . $loc ) ) . '</p>';
	}
	$head .= '<div class="pe-specs pe-specs--big">' . pe_specs_html( $id ) . '</div></div>';

	$wa  = pe_opt( 'whatsapp' );
	$btn = '';
	if ( $wa ) {
		$text = rawurlencode( 'Hi, I am interested in: ' . get_the_title( $id ) . ' ' . get_permalink( $id ) );
		$btn  = '<a class="pe-btn pe-btn--wa" href="' . esc_url( 'https://wa.me/' . $wa . '?text=' . $text ) . '" target="_blank" rel="noopener">Ask on WhatsApp</a> ';
	}
	$btn .= '<a class="pe-btn pe-btn-ghost-dark" href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', pe_opt( 'phone' ) ) ) . '">Call ' . esc_html( pe_opt( 'phone' ) ) . '</a>';

	$tail = '<div class="pe-single-actions">' . $btn . '</div>'
		. '<div class="pe-formbox pe-formbox--single"><h2>Interested in this property?</h2>' . pe_enquiry_form( $id ) . '</div>'
		. '<p><a href="' . esc_url( home_url( '/properties/' ) ) . '">&laquo; Back to all properties</a></p>';
	return $head . $content . $tail;
} );

/* =====================================================================
 * 4. SMALL SHORTCODES
 * ===================================================================== */
add_shortcode( 'pe_name', function () {
	return esc_html( pe_opt( 'name' ) );
} );

add_shortcode( 'pe_stats', function () {
	$total = pe_query( array(), 1, 1 )->found_posts;
	$q     = new WP_Query( array( 'post_type' => 'pe_property', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => 'pe_status', 'value' => 'Available' ) ) ) );
	$areas = get_terms( array( 'taxonomy' => 'pe_location', 'hide_empty' => true ) );
	$areas = is_array( $areas ) ? count( $areas ) : 0;
	return '<div class="pe-stats"><div class="pe-stat"><strong>' . (int) $total . '</strong><span>Properties listed</span></div><div class="pe-stat"><strong>' . (int) $q->found_posts . '</strong><span>Available now</span></div><div class="pe-stat"><strong>' . (int) $areas . '</strong><span>Areas covered</span></div></div>';
} );

add_shortcode( 'pe_hours', function () {
	$rows = array( 'Monday - Friday' => pe_opt( 'hours_week' ), 'Saturday' => pe_opt( 'hours_sat' ), 'Sunday' => pe_opt( 'hours_sun' ) );
	$out  = '<div class="pe-hours">';
	foreach ( $rows as $day => $time ) {
		$out .= '<div class="pe-row"><span class="pe-name">' . esc_html( $day ) . '</span><span class="pe-dots"></span><span class="pe-amt">' . esc_html( $time ) . '</span></div>';
	}
	return $out . '</div>';
} );

add_shortcode( 'pe_contact_info', function () {
	$out  = '<div class="pe-info"><h3>Visit our office</h3><p>' . esc_html( pe_opt( 'address' ) ) . '</p></div>';
	$out .= '<div class="pe-info"><h3>Call or message</h3><p><a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', pe_opt( 'phone' ) ) ) . '">' . esc_html( pe_opt( 'phone' ) ) . '</a>';
	if ( pe_opt( 'whatsapp' ) ) {
		$out .= '<br><a href="' . esc_url( 'https://wa.me/' . pe_opt( 'whatsapp' ) ) . '" target="_blank" rel="noopener">Chat on WhatsApp</a>';
	}
	$out .= '<br><a href="mailto:' . esc_attr( pe_opt( 'email' ) ) . '">' . esc_html( pe_opt( 'email' ) ) . '</a></p></div>';
	$out .= '<div class="pe-info"><h3>Office hours</h3><p>Monday - Friday: ' . esc_html( pe_opt( 'hours_week' ) ) . '<br>Saturday: ' . esc_html( pe_opt( 'hours_sat' ) ) . '<br>Sunday: ' . esc_html( pe_opt( 'hours_sun' ) ) . '</p></div>';
	return $out;
} );

add_shortcode( 'pe_map', function () {
	$src = pe_opt( 'map' );
	if ( '' === $src ) {
		return '';
	}
	return '<div class="pe-map"><iframe src="' . esc_url( $src ) . '" title="Map showing our office" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>';
} );

add_shortcode( 'pe_social', function () {
	$out = '';
	foreach ( array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok' ) as $k => $label ) {
		if ( pe_opt( $k ) ) {
			$out .= '<a href="' . esc_url( pe_opt( $k ) ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a>';
		}
	}
	return '' === $out ? '' : '<div class="pe-social">' . $out . '</div>';
} );

add_shortcode( 'pe_testimonials', function () {
	$lines = array_filter( array_map( 'trim', explode( "\n", pe_opt( 'testimonials' ) ) ) );
	$out   = '';
	foreach ( $lines as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) && '' !== $parts[0] && '' !== $parts[1] ) {
			$out .= '<blockquote class="pe-quote"><p>&ldquo;' . esc_html( $parts[1] ) . '&rdquo;</p><cite>' . esc_html( $parts[0] ) . '</cite></blockquote>';
		}
	}
	return '' === $out ? '' : '<div class="pe-section"><h2>What our clients say</h2><div class="pe-quotes">' . $out . '</div></div>';
} );

add_shortcode( 'pe_services', function () {
	$out = '<div class="pe-services">';
	foreach ( pe_services() as $s ) {
		$out .= '<div class="pe-service"><div class="pe-ico" aria-hidden="true">' . pe_icon( $s[2] ) . '</div><h3>' . esc_html( $s[0] ) . '</h3><p>' . esc_html( $s[1] ) . '</p></div>';
	}
	return $out . '</div>';
} );

add_shortcode( 'pe_steps', function () {
	$out = '<div class="pe-steps">';
	$n   = 1;
	foreach ( pe_steps() as $s ) {
		$out .= '<div class="pe-step"><span class="pe-num">' . $n++ . '</span><h3>' . esc_html( $s[0] ) . '</h3><p>' . esc_html( $s[1] ) . '</p></div>';
	}
	return $out . '</div>';
} );

add_shortcode( 'pe_agents', function () {
	$out = '<div class="pe-agents">';
	foreach ( pe_agents() as $t ) {
		$parts    = explode( ' ', $t[0] );
		$initials = strtoupper( substr( $parts[0], 0, 1 ) . substr( end( $parts ), 0, 1 ) );
		$out .= '<div class="pe-agent"><div class="pe-avatar" aria-hidden="true">' . esc_html( $initials ) . '</div><h3>' . esc_html( $t[0] ) . '</h3><strong>' . esc_html( $t[1] ) . '</strong><p>' . esc_html( $t[2] ) . '</p></div>';
	}
	return $out . '</div>';
} );

/* =====================================================================
 * 5. ENQUIRIES (form, inbox, emails)
 * ===================================================================== */
function pe_enquiry_form( $property_id = 0 ) {
	$status = isset( $_GET['pe_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['pe_sent'] ) ) : '';
	ob_start();
	if ( '1' === $status ) {
		echo '<div class="pe-notice pe-ok" role="status">Thank you! Your enquiry has been received. One of our agents will contact you soon.</div>';
	} elseif ( '0' === $status ) {
		echo '<div class="pe-notice pe-err" role="alert">Please fill in your name, a valid email, and tick the agreement box.</div>';
	} elseif ( '2' === $status ) {
		echo '<div class="pe-notice pe-err" role="alert">Too many requests from your connection. Please try again later or contact us on WhatsApp.</div>';
	}
	?>
	<form class="pe-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="pe_enquiry_submit">
		<input type="hidden" name="pe_back" value="<?php echo esc_url( get_permalink() ); ?>">
		<input type="hidden" name="pe_property" value="<?php echo (int) $property_id; ?>">
		<?php wp_nonce_field( 'pe_enquiry', 'pe_nonce' ); ?>
		<label>Your name <input type="text" name="pe_name" required maxlength="100" autocomplete="name"></label>
		<div class="pe-row2">
			<label>Email address <input type="email" name="pe_email" required maxlength="100" autocomplete="email"></label>
			<label>Phone (optional) <input type="tel" name="pe_phone" maxlength="30" autocomplete="tel"></label>
		</div>
		<?php if ( ! $property_id ) : ?>
		<label>I am interested in
			<select name="pe_interest">
				<?php foreach ( pe_interests() as $i ) : ?>
					<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php endif; ?>
		<label>Message (optional) <textarea name="pe_message" rows="4" maxlength="1000"></textarea></label>
		<label class="pe-check"><input type="checkbox" name="pe_consent" value="1" required> I agree to be contacted about my enquiry. See our <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Privacy Policy</a>.</label>
		<p class="pe-hp" aria-hidden="true"><label>Leave empty <input type="text" name="pe_website" tabindex="-1" autocomplete="off"></label></p>
		<button type="submit">Send enquiry</button>
	</form>
	<?php
	return ob_get_clean();
}

add_shortcode( 'pe_enquiry', function () {
	return pe_enquiry_form( 0 );
} );

function pe_enquiry_handle() {
	$back  = isset( $_POST['pe_back'] ) ? esc_url_raw( wp_unslash( $_POST['pe_back'] ) ) : home_url( '/' );
	$nonce = isset( $_POST['pe_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pe_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'pe_enquiry' ) ) {
		wp_safe_redirect( add_query_arg( 'pe_sent', '0', $back ) );
		exit;
	}
	if ( ! empty( $_POST['pe_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'pe_sent', '1', $back ) );
		exit;
	}

	// Simple rate limit: 5 enquiries per hour per visitor.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'pe_rl_' . md5( $ip );
	$cnt = (int) get_transient( $key );
	if ( $cnt >= 5 ) {
		wp_safe_redirect( add_query_arg( 'pe_sent', '2', $back ) );
		exit;
	}
	set_transient( $key, $cnt + 1, HOUR_IN_SECONDS );

	$name     = isset( $_POST['pe_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pe_name'] ) ) : '';
	$email    = isset( $_POST['pe_email'] ) ? sanitize_email( wp_unslash( $_POST['pe_email'] ) ) : '';
	$phone    = isset( $_POST['pe_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['pe_phone'] ) ) : '';
	$interest = isset( $_POST['pe_interest'] ) ? sanitize_text_field( wp_unslash( $_POST['pe_interest'] ) ) : '';
	$message  = isset( $_POST['pe_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pe_message'] ) ) : '';
	$pid      = isset( $_POST['pe_property'] ) ? (int) $_POST['pe_property'] : 0;
	$consent  = ! empty( $_POST['pe_consent'] );

	$prop_title = '';
	$prop_url   = '';
	if ( $pid > 0 && 'pe_property' === get_post_type( $pid ) ) {
		$prop_title = get_the_title( $pid );
		$prop_url   = get_permalink( $pid );
		$interest   = 'Property enquiry';
	} else {
		$pid = 0;
		if ( ! in_array( $interest, pe_interests(), true ) ) {
			$interest = 'Other';
		}
	}

	if ( '' === $name || ! is_email( $email ) || ! $consent ) {
		wp_safe_redirect( add_query_arg( 'pe_sent', '0', $back ) );
		exit;
	}

	$body = "Name: $name\nEmail: $email\nPhone: $phone\nInterest: $interest\n";
	if ( $prop_title ) {
		$body .= "Property: $prop_title\nLink: $prop_url\n";
	}
	$body .= "\n$message";
	$id    = wp_insert_post( array(
		'post_type'    => 'pe_enquiry',
		'post_status'  => 'publish',
		'post_title'   => ( $prop_title ? $prop_title : $interest ) . ' - ' . $name,
		'post_content' => $body,
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, 'pe_e_status', 'New' );
	}

	$biz = wp_specialchars_decode( pe_opt( 'name' ), ENT_QUOTES );
	@wp_mail( pe_opt( 'email' ), '[' . $biz . '] New enquiry: ' . ( $prop_title ? $prop_title : $interest ), $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	$reply = "Hi $name,\n\nThanks for contacting $biz. We received your enquiry" . ( $prop_title ? " about $prop_title" : '' ) . " and one of our agents will contact you soon.\n\nIf you need us sooner, call " . pe_opt( 'phone' ) . ".\n\n$biz";
	@wp_mail( $email, 'We received your enquiry - ' . $biz, $reply, array( 'Reply-To: ' . pe_opt( 'email' ) ) );

	wp_safe_redirect( add_query_arg( 'pe_sent', '1', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_pe_enquiry_submit', 'pe_enquiry_handle' );
add_action( 'admin_post_pe_enquiry_submit', 'pe_enquiry_handle' );

add_filter( 'manage_pe_enquiry_posts_columns', function () {
	return array( 'cb' => '<input type="checkbox">', 'title' => 'Enquiry', 'pe_e_status' => 'Status', 'date' => 'Received' );
} );

add_action( 'manage_pe_enquiry_posts_custom_column', function ( $col, $id ) {
	if ( 'pe_e_status' === $col ) {
		$s = get_post_meta( $id, 'pe_e_status', true );
		echo esc_html( $s ? $s : 'New' );
	}
}, 10, 2 );

add_action( 'save_post_pe_enquiry', function ( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['pe_enq_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pe_enq_nonce'] ) ), 'pe_enq_status' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$s = isset( $_POST['pe_e_status'] ) ? sanitize_text_field( wp_unslash( $_POST['pe_e_status'] ) ) : 'New';
	if ( in_array( $s, pe_enquiry_statuses(), true ) ) {
		update_post_meta( $post_id, 'pe_e_status', $s );
	}
} );

/* =====================================================================
 * 6. FRONT END EXTRAS: SEO data, WhatsApp button, admin reminder
 * ===================================================================== */
add_action( 'wp_head', function () {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ) {
		return; // An SEO plugin is handling this.
	}
	$desc  = pe_opt( 'description' );
	$image = '';
	if ( is_singular( 'pe_property' ) ) {
		$pid = get_queried_object_id();
		if ( has_post_thumbnail( $pid ) ) {
			$image = get_the_post_thumbnail_url( $pid, 'large' );
		}
		$ex = get_the_excerpt( $pid );
		if ( $ex ) {
			$desc = wp_strip_all_tags( $ex );
		}
	}
	if ( ! $image && file_exists( plugin_dir_path( __FILE__ ) . 'images/hero.jpg' ) ) {
		$image = plugin_dir_url( __FILE__ ) . 'images/hero.jpg';
	}
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( pe_opt( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	$same = array();
	foreach ( array( 'facebook', 'instagram', 'youtube', 'tiktok' ) as $k ) {
		if ( pe_opt( $k ) ) {
			$same[] = pe_opt( $k );
		}
	}
	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'RealEstateAgent',
		'name'        => pe_opt( 'name' ),
		'description' => pe_opt( 'description' ),
		'url'         => home_url( '/' ),
		'telephone'   => pe_opt( 'phone' ),
		'email'       => pe_opt( 'email' ),
		'address'     => array( '@type' => 'PostalAddress', 'streetAddress' => pe_opt( 'address' ) ),
	);
	if ( $same ) {
		$schema['sameAs'] = $same;
	}
	$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, $flags ) . '</script>' . "\n";

	if ( is_singular( 'pe_property' ) ) {
		$pid     = get_queried_object_id();
		$listing = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'RealEstateListing',
			'name'       => get_the_title( $pid ),
			'url'        => get_permalink( $pid ),
			'datePosted' => get_the_date( 'c', $pid ),
			'description' => $desc,
		);
		if ( $image ) {
			$listing['image'] = $image;
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $listing, $flags ) . '</script>' . "\n";
	}
}, 5 );

add_action( 'wp_footer', function () {
	$wa = pe_opt( 'whatsapp' );
	if ( '' === $wa ) {
		return;
	}
	$text = rawurlencode( 'Hi, I would like to know more about your properties.' );
	echo '<a class="pe-whatsapp" href="' . esc_url( 'https://wa.me/' . $wa . '?text=' . $text ) . '" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">'
		. '<svg viewBox="0 0 32 32" width="24" height="24" aria-hidden="true"><path fill="#fff" d="M16 4C9.4 4 4 8.9 4 15c0 2.6 1 5 2.7 6.9L5.5 28l6.4-2.1c1.3.4 2.7.6 4.1.6 6.6 0 12-4.9 12-11S22.6 4 16 4z"/></svg><span>WhatsApp</span></a>';
} );

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$q = new WP_Query( array( 'post_type' => 'pe_property', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => 'pe_sample', 'meta_value' => '1' ) );
	if ( $q->found_posts > 0 ) {
		echo '<div class="notice notice-warning"><p><strong>Prime Estates:</strong> the sample properties are still on your site. Delete them under Properties (they have no photos) and add your real listings before going live.</p></div>';
	}
} );

/* =====================================================================
 * 7. PAGE CONTENT
 * ===================================================================== */
function pe_page_html( $key ) {
	$props   = esc_url( home_url( '/properties/' ) );
	$contact = esc_url( home_url( '/contact/' ) );

	if ( 'home' === $key ) {
		return '<div class="pe-hero"><h1>Find a place you will love to call home</h1><p>Homes, apartments, land and commercial properties for sale and rent, with local agents who know the area.</p><div class="pe-hero-search">[pe_search]</div></div>'
			. '[pe_stats]'
			. '<div class="pe-section"><h2>Featured properties</h2>[pe_properties featured="1" limit="6"]<p class="pe-center"><a class="pe-btn" href="' . $props . '">View all properties</a></p></div>'
			. '<div class="pe-section"><h2>How we can help</h2>[pe_services]</div>'
			. '<div class="pe-section pe-band"><h2>Why choose us</h2><div class="pe-features">'
			. '<div class="pe-feature"><div class="pe-ico">' . pe_icon( 'search' ) . '</div><h3>Local knowledge</h3><p>Agents who know the streets, prices and neighbourhoods.</p></div>'
			. '<div class="pe-feature"><div class="pe-ico">' . pe_icon( 'shield' ) . '</div><h3>Honest advice</h3><p>Clear information about every property, good and bad.</p></div>'
			. '<div class="pe-feature"><div class="pe-ico">' . pe_icon( 'file' ) . '</div><h3>Paperwork support</h3><p>Help with offers, documents and handover from start to finish.</p></div>'
			. '</div></div>'
			. '[pe_testimonials]'
			. '<div class="pe-section"><div class="pe-cta"><h2>Looking to buy, rent or sell?</h2><p>Tell us what you need and an agent will get back to you.</p><a class="pe-btn" href="' . $contact . '">Contact us</a></div></div>';
	}

	if ( 'properties' === $key ) {
		return '<div class="pe-banner pe-banner--props"><h1>Properties</h1><p>Search homes, apartments, land and commercial properties.</p></div>'
			. '<div class="pe-section">[pe_listings]</div>'
			. '<div class="pe-section"><div class="pe-cta"><h2>Cannot find what you need?</h2><p>Tell us your budget and location. We often know about properties before they are listed.</p><a class="pe-btn" href="' . $contact . '">Tell us what you need</a></div></div>';
	}

	if ( 'services' === $key ) {
		return '<div class="pe-banner pe-banner--services"><h1>Our services</h1><p>Whether you are buying, renting or selling, we guide you at every step.</p></div>'
			. '<div class="pe-section">[pe_services]</div>'
			. '<div class="pe-section pe-band"><h2>How it works</h2>[pe_steps]</div>'
			. '<div class="pe-section"><div class="pe-cta"><h2>Get a free valuation</h2><p>Find out what your property is worth today.</p><a class="pe-btn" href="' . $contact . '">Request a valuation</a></div></div>';
	}

	if ( 'about' === $key ) {
		return '<div class="pe-banner pe-banner--about"><h1>About us</h1><p>Trusted property advice from people who live and work here.</p></div>'
			. '<div class="pe-section"><div class="pe-two"><div><h2 class="pe-left">Our story</h2><p>[pe_name] helps families, investors and businesses find the right property. We combine local knowledge with clear, honest advice, so every client understands the process and the price.</p><p>From the first viewing to the final signature, our team stays with you.</p></div><div class="pe-about-img" role="img" aria-label="Our office"></div></div></div>'
			. '<div class="pe-section pe-band"><h2>What we believe</h2><div class="pe-features">'
			. '<div class="pe-feature"><h3>Transparency</h3><p>Accurate details and fair pricing on every listing.</p></div>'
			. '<div class="pe-feature"><h3>Respect for your time</h3><p>Quick replies and viewings that fit your schedule.</p></div>'
			. '<div class="pe-feature"><h3>Long-term relationships</h3><p>We want you to recommend us, not just use us once.</p></div>'
			. '</div></div>'
			. '<div class="pe-section"><h2>Meet our agents</h2>[pe_agents]</div>'
			. '<div class="pe-section"><div class="pe-cta"><h2>Let us talk</h2><p>Call, message or visit our office.</p><a class="pe-btn" href="' . $contact . '">Contact us</a></div></div>';
	}

	if ( 'privacy' === $key ) {
		return '<div class="pe-banner pe-banner--about"><h1>Privacy policy</h1><p>How we handle your information.</p></div>'
			. '<div class="pe-section pe-legal"><h3>What we collect</h3><p>When you send an enquiry we collect your name, email address, phone number (optional), what you are interested in, the property you asked about and any message you write.</p>'
			. '<h3>How we use it</h3><p>We use these details only to reply to your enquiry and contact you about it. We do not sell your information.</p>'
			. '<h3>How long we keep it</h3><p>We keep enquiries for as long as needed to help you, then delete them.</p>'
			. '<h3>Your choices</h3><p>You can ask us to see, correct or delete your information at any time by contacting [pe_name] using the details on the Contact page.</p></div>';
	}

	return '<div class="pe-banner pe-banner--contact"><h1>Contact us</h1><p>Tell us what you are looking for. An agent will reply by email or phone.</p></div>'
		. '<div class="pe-section"><div class="pe-contact-grid"><div>[pe_contact_info][pe_social]</div><div class="pe-formbox">[pe_enquiry]</div></div></div>'
		. '<div class="pe-section">[pe_map]</div>';
}

function pe_make_page( $title, $slug, $html ) {
	$existing = get_page_by_path( $slug );
	if ( $existing && 'trash' !== $existing->post_status ) {
		return $existing->ID;
	}
	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->",
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, 'site-sidebar-layout', 'no-sidebar' );
		update_post_meta( $id, 'site-content-layout', 'normal-width-container' );
		update_post_meta( $id, 'site-post-title', 'disabled' );
		return $id;
	}
	return 0;
}

/* =====================================================================
 * 8. SETUP ON ACTIVATION
 * ===================================================================== */
function pe_assign_menu() {
	$menu = wp_get_nav_menu_object( 'Prime Menu' );
	if ( ! $menu ) {
		return;
	}
	$locs = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locs['primary'] ) ) {
		$locs['primary'] = $menu->term_id;
		set_theme_mod( 'nav_menu_locations', $locs );
	}
}
add_action( 'after_switch_theme', 'pe_assign_menu' );

register_activation_hook( __FILE__, 'pe_setup' );
add_action( 'admin_init', 'pe_apply' );

function pe_sample_properties() {
	return array(
		array( 'Modern 4-Bedroom House with Garden', 'sale', 'House', 'Colombo', 65000000, 4, 3, 2800, 10, 'Quiet residential road, close to schools and shops', 'A bright two-storey house with a large garden, covered parking and a modern kitchen. Close to schools, shops and public transport.', true ),
		array( 'Luxury 3-Bedroom Apartment', 'sale', 'Apartment', 'Colombo', 48000000, 3, 2, 1650, 0, 'City centre, 12th floor', 'A spacious apartment with city views, 24-hour security, a swimming pool and a gym in the building.', true ),
		array( 'Spacious 2-Bedroom Apartment for Rent', 'rent', 'Apartment', 'Kandy', 85000, 2, 2, 1100, 0, 'Near the lake, fully furnished', 'Fully furnished apartment with a balcony, hot water and secure parking. Available for long term rent.', true ),
		array( 'Residential Land with Road Frontage', 'sale', 'Land', 'Galle', 12500000, 0, 0, 0, 20, 'Main road, electricity and water available', 'A level 20 perch block with road frontage, clear deed and all utilities at the boundary.', false ),
		array( 'Beachside 3-Bedroom Villa', 'sale', 'House', 'Negombo', 72000000, 3, 3, 2400, 15, 'A short walk to the beach', 'A well kept villa with a private garden, an open living area and parking for two cars.', false ),
		array( 'Ground Floor Shop Space', 'rent', 'Commercial', 'Nugegoda', 150000, 0, 1, 900, 0, 'Busy main road with high foot traffic', 'Ground floor commercial space with a wide frontage, back storage and a staff bathroom.', false ),
	);
}

function pe_create_samples() {
	if ( get_option( 'pe_samples_done' ) ) {
		return;
	}
	foreach ( array( 'House', 'Apartment', 'Land', 'Commercial' ) as $t ) {
		if ( ! term_exists( $t, 'pe_type' ) ) {
			wp_insert_term( $t, 'pe_type' );
		}
	}
	foreach ( array( 'Colombo', 'Kandy', 'Galle', 'Negombo', 'Nugegoda' ) as $l ) {
		if ( ! term_exists( $l, 'pe_location' ) ) {
			wp_insert_term( $l, 'pe_location' );
		}
	}
	foreach ( pe_sample_properties() as $p ) {
		$id = wp_insert_post( array(
			'post_type'    => 'pe_property',
			'post_status'  => 'publish',
			'post_title'   => $p[0],
			'post_content' => $p[10],
			'post_excerpt' => $p[10],
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'pe_listing', $p[1] );
			update_post_meta( $id, 'pe_status', 'Available' );
			update_post_meta( $id, 'pe_price', $p[4] );
			update_post_meta( $id, 'pe_beds', $p[5] );
			update_post_meta( $id, 'pe_baths', $p[6] );
			update_post_meta( $id, 'pe_area', $p[7] );
			update_post_meta( $id, 'pe_land', $p[8] );
			update_post_meta( $id, 'pe_address', $p[9] );
			update_post_meta( $id, 'pe_featured', $p[11] ? '1' : '0' );
			update_post_meta( $id, 'pe_sample', '1' );
			wp_set_object_terms( $id, $p[2], 'pe_type' );
			wp_set_object_terms( $id, $p[3], 'pe_location' );
		}
	}
	update_option( 'pe_samples_done', 1 );
}

/* Optional photos for the sample properties: images/sample-1.jpg ... sample-6.jpg (in the order of pe_sample_properties). */
function pe_attach_sample_images() {
	$dir = plugin_dir_path( __FILE__ ) . 'images/';
	$have = array();
	for ( $n = 1; $n <= 6; $n++ ) {
		if ( file_exists( $dir . 'sample-' . $n . '.jpg' ) ) {
			$have[] = $n;
		}
	}
	$sig = md5( wp_json_encode( $have ) );
	if ( get_option( 'pe_sample_img_sig' ) === $sig ) {
		return;
	}
	$posts = get_posts( array( 'post_type' => 'pe_property', 'post_status' => 'any', 'posts_per_page' => 6, 'orderby' => 'ID', 'order' => 'ASC', 'meta_key' => 'pe_sample', 'meta_value' => '1', 'fields' => 'ids' ) );
	if ( ! $posts ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	foreach ( $posts as $i => $pid ) {
		$n = $i + 1;
		if ( ! in_array( $n, $have, true ) || has_post_thumbnail( $pid ) ) {
			continue;
		}
		$tmp = wp_tempnam( 'sample-' . $n . '.jpg' );
		copy( $dir . 'sample-' . $n . '.jpg', $tmp );
		$att = media_handle_sideload( array( 'name' => 'pe-sample-' . $n . '.jpg', 'tmp_name' => $tmp ), $pid, get_the_title( $pid ) );
		if ( is_wp_error( $att ) ) {
			@unlink( $tmp );
			continue;
		}
		set_post_thumbnail( $pid, $att );
	}
	update_option( 'pe_sample_img_sig', $sig );
}
add_action( 'admin_init', 'pe_attach_sample_images' );

/* One-time upgrade: swap the emoji icons on an existing Home page for line icons. */
function pe_upgrade_home_icons() {
	if ( get_option( 'pe_icons_v2' ) ) {
		return;
	}
	$home = get_page_by_path( 'home' );
	if ( $home ) {
		$map = array( '&#128269;' => 'search', '&#129309;' => 'shield', '&#128196;' => 'file' );
		$c   = $home->post_content;
		foreach ( $map as $ent => $name ) {
			$c = str_replace( '<div class="pe-ico">' . $ent . '</div>', '<div class="pe-ico">' . pe_icon( $name ) . '</div>', $c );
		}
		if ( $c !== $home->post_content ) {
			wp_update_post( array( 'ID' => $home->ID, 'post_content' => wp_slash( $c ) ) );
		}
	}
	update_option( 'pe_icons_v2', 1 );
}
add_action( 'admin_init', 'pe_upgrade_home_icons' );

function pe_setup() {
	// Register types now so terms and permalinks work during activation.
	pe_register_types();
	pe_create_samples();
	pe_attach_sample_images();

	$ids = array(
		'home'       => pe_make_page( 'Home', 'home', pe_page_html( 'home' ) ),
		'properties' => pe_make_page( 'Properties', 'properties', pe_page_html( 'properties' ) ),
		'services'   => pe_make_page( 'Services', 'services', pe_page_html( 'services' ) ),
		'about'      => pe_make_page( 'About', 'about', pe_page_html( 'about' ) ),
		'contact'    => pe_make_page( 'Contact', 'contact', pe_page_html( 'contact' ) ),
		'privacy'    => pe_make_page( 'Privacy Policy', 'privacy', pe_page_html( 'privacy' ) ),
	);

	update_option( 'blogname', pe_opt( 'name' ) );
	update_option( 'blogdescription', 'Homes, apartments and land for sale and rent' );
	if ( $ids['home'] ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	if ( ! wp_get_nav_menu_object( 'Prime Menu' ) ) {
		$menu_id = wp_create_nav_menu( 'Prime Menu' );
		if ( ! is_wp_error( $menu_id ) ) {
			$pos   = 1;
			$items = array( 'home' => 'Home', 'properties' => 'Properties', 'services' => 'Services', 'about' => 'About', 'contact' => 'Contact Us' );
			foreach ( $items as $k => $label ) {
				if ( $ids[ $k ] ) {
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'     => $label,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $ids[ $k ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $pos++,
						'menu-item-classes'   => 'contact' === $k ? 'pe-book-btn' : '',
					) );
				}
			}
		}
	}
	pe_assign_menu();
	pe_apply();
	flush_rewrite_rules();
}

function pe_apply() {
	pe_assign_menu();

	// CSS: main file added once.
	$current = wp_get_custom_css();
	$file    = plugin_dir_path( __FILE__ ) . 'css/primeestates.css';
	if ( file_exists( $file ) ) {
		$css_sig = md5_file( $file );
		if ( false === strpos( $current, '/* PRIME ESTATES CSS */' ) || get_option( 'pe_css_sig' ) !== $css_sig ) {
			// Drop the old copy (with or without the end marker), then add the current one.
			$current = preg_replace( '#/\* PRIME ESTATES CSS \*/.*?/\* END PRIME ESTATES CSS \*/#s', '', $current, 1, $n );
			if ( ! $n ) {
				$current = preg_replace( '#/\* PRIME ESTATES CSS \*/.*?(?=/\* PRIME IMAGES \*/|$)#s', '', $current, 1 );
			}
			$current  = rtrim( $current ) . "\n" . file_get_contents( $file );
			update_option( 'pe_css_sig', $css_sig );
			delete_option( 'pe_img_sig' ); // The photo block must come after the main CSS.
			$current = preg_replace( '#\n?/\* PRIME IMAGES \*/.*?/\* END PRIME IMAGES \*/#s', '', $current );
		}
	}

	// Photo backgrounds: rebuilt whenever the set of image files in images/ changes.
	$dir  = plugin_dir_path( __FILE__ ) . 'images/';
	$url  = plugin_dir_url( __FILE__ ) . 'images/';
	$have = array();
	foreach ( array( 'hero', 'about', 'cta' ) as $n ) {
		if ( file_exists( $dir . $n . '.jpg' ) ) {
			$have[ $n ] = esc_url_raw( $url . $n . '.jpg' );
		}
	}
	$sig = md5( wp_json_encode( $have ) );
	if ( get_option( 'pe_img_sig' ) !== $sig ) {
		$current = preg_replace( '#\n?/\* PRIME IMAGES \*/.*?/\* END PRIME IMAGES \*/#s', '', $current );
		$pick = function ( $first, $second ) use ( $have ) {
			return isset( $have[ $first ] ) ? $have[ $first ] : ( isset( $have[ $second ] ) ? $have[ $second ] : '' );
		};
		$map = array(
			'.pe-hero'            => $pick( 'hero', 'hero' ),
			'.pe-banner--props'   => $pick( 'hero', 'about' ),
			'.pe-banner--services'=> $pick( 'about', 'hero' ),
			'.pe-banner--about'   => $pick( 'about', 'hero' ),
			'.pe-banner--contact' => $pick( 'cta', 'hero' ),
			'.pe-about-img'       => $pick( 'about', 'hero' ),
			'.pe-cta'             => $pick( 'cta', 'hero' ),
		);
		$css = "\n/* PRIME IMAGES */\n";
		foreach ( $map as $sel => $img ) {
			if ( '' !== $img ) {
				$css .= $sel . "{background-image:url('" . $img . "');}\n";
			}
		}
		$css    .= '/* END PRIME IMAGES */';
		$current .= $css;
		update_option( 'pe_img_sig', $sig );
	}
	if ( $current !== wp_get_custom_css() ) {
		wp_update_custom_css_post( $current );
	}

	// Astra options, once.
	if ( ! get_option( 'pe_opts_done' ) ) {
		$opts = get_option( 'astra-settings', array() );
		if ( is_array( $opts ) ) {
			$opts['footer-copyright-editor'] = 'Copyright [copyright] [current_year] [site_title]. All rights reserved. | <a href="/privacy/">Privacy Policy</a>';
			$opts['scroll-to-top-enable']    = 1;
			update_option( 'astra-settings', $opts );
		}
		update_option( 'pe_opts_done', 1 );
	}
}
