<?php

/**
 * Plugin Name: Vacature Plugin
 * Description: A plugin to manage job applications 
 * Version: 1.2.0
 * 
 * Author: Abderrahman bakkouy
 * Text domain: vacature-plugin
 */

 function vacatures_register_post_type() {
    $labels = array(
        'name'               => 'Vacatures',
        'singular_name'      => 'Vacature',
        'menu_name'          => 'Vacatures',
        'name_admin_bar'     => 'Vacature',
        'add_new'            => 'Nieuwe toevoegen',
        'add_new_item'       => 'Nieuwe vacature toevoegen',
        'new_item'           => 'Nieuwe vacature',
        'edit_item'          => 'Vacature bewerken',
        'view_item'          => 'Vacature bekijken',
        'all_items'          => 'Alle vacatures',
        'search_items'       => 'Vacatures zoeken',
        'not_found'          => 'Geen vacatures gevonden',
        'not_found_in_trash' => 'Geen vacatures in prullenbak gevonden',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-businessman', // Icoon in admin menu
        'query_var'          => true,
        'rewrite'            => array('slug' => 'vacature'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
    );

    register_post_type('vacature', $args);
}
add_action('init', 'vacatures_register_post_type');

// In uw plugin (vacatures-plugin.php)

add_action('acf/init', function() {
    if( function_exists('acf_add_local_field_group') ) {
        acf_add_local_field_group(array(
            'key' => 'group_vacature_info',
            'title' => 'Vacature Info',
            'fields' => array(
                array(
                    'key' => 'field_text',
                    'label' => 'Text',
                    'name' => 'text',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_locatie',
                    'label' => 'Locatie',
                    'name' => 'locatie',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_button',
                    'label' => 'Button',
                    'name' => 'button',
                    'type' => 'link',
                ),
                array(
                    'key' => 'field_img',
                    'label' => 'Afbeelding',
                    'name' => 'img',
                    'type' => 'image',
                ),
                array(
                    'key' => 'field_text1',
                    'label' => 'Text 1',
                    'name' => 'text1',
                    'type' => 'text',
					'default_value' => 'Gevraagd',
                ),
                array(
                    'key' => 'field_repeater1',
                    'label' => 'Repeater 1',
                    'name' => 'repeater1',
                    'type' => 'repeater',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_repeater1_item',
                            'label' => 'Item',
                            'name' => 'item',
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_text2',
                    'label' => 'Text 2',
                    'name' => 'text2',
                    'type' => 'text',
					'default_value' => 'Aanbod',
                ),
                array(
                    'key' => 'field_repeater2',
                    'label' => 'Repeater 2',
                    'name' => 'repeater2',
                    'type' => 'repeater',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_repeater2_item',
                            'label' => 'Item',
                            'name' => 'item',
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_text_extra',
                    'label' => 'Text',
                    'name' => 'text_extra',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_textarea',
                    'label' => 'Text Area',
                    'name' => 'text_area',
                    'type' => 'textarea',
                ),
                array(
                    'key' => 'field_button_extra',
                    'label' => 'Button Extra',
                    'name' => 'button_extra',
                    'type' => 'link',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'vacature',
                    ),
                ),
            ),
        ));
    }
});


// Zorg dat elke vacature een blijvende meta 'vacature_id' krijgt met de eigen post ID
add_action('save_post_vacature', 'vacature_ensure_meta_id', 10, 3);
function vacature_ensure_meta_id($post_id, $post, $update) {
	// Veiligheidschecks
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (wp_is_post_revision($post_id)) {
		return;
	}
	if ($post->post_type !== 'vacature') {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	$existing = get_post_meta($post_id, 'vacature_id', true);
	if (empty($existing)) {
		update_post_meta($post_id, 'vacature_id', $post_id);
	}
}

// Toon een directe link met ?id= in de admin meldingen na opslaan/publiceren
add_filter('post_updated_messages', function($messages) {
	global $post;
	if ($post && $post->post_type === 'vacature') {
		$post_id = $post->ID;
		$base    = trailingslashit(home_url('vacatures-pagina'));
		$link    = esc_url($base . '?id=' . $post_id);
		$anchor  = '<a href="' . $link . '">' . __('Bekijk vacature pagina', 'vacature-plugin') . '</a>';

		$messages['vacature'][1]  = __('Vacature bijgewerkt.', 'vacature-plugin') . ' ' . $anchor;
		$messages['vacature'][6]  = __('Vacature gepubliceerd.', 'vacature-plugin') . ' ' . $anchor;
		$messages['vacature'][10] = __('Concept bijgewerkt.', 'vacature-plugin') . ' ' . $anchor;
	}
	return $messages;
});


// Forceer standaard URL naar /vacatures-pagina/?id=<ID> voor vacatures
add_filter('post_type_link', function($permalink, $post, $leavename, $sample) {
	if ($post->post_type === 'vacature') {
		$base = trailingslashit(home_url('vacatures-pagina'));
		return $base . '?id=' . $post->ID;
	}
	return $permalink;
}, 10, 4);

// Redirect directe single vacature URLs naar de standaard pagina met ?id=
add_action('template_redirect', function() {
	if (is_admin()) return;
	if (defined('REST_REQUEST') && REST_REQUEST) return;
	if (is_feed()) return;
	if (is_singular('vacature')) {
		global $post;
		if (!$post) return;
		$target = trailingslashit(home_url('vacatures-pagina')) . '?id=' . $post->ID;
		$current = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if (trailingslashit($current) !== trailingslashit($target)) {
			wp_safe_redirect($target, 301);
			exit;
		}
	}
});


// Admin submenu: Inkomende vacatures (ACF Form inzendingen)
add_action('admin_menu', function() {
	// Bereken aantal onbekeken AF entries
	$unread_count = 0;
	if (post_type_exists('af_entry')) {
		$q = new WP_Query(array(
			'post_type' => 'af_entry',
			'post_status' => 'any',
			'posts_per_page' => 1,
			'no_found_rows' => false,
			'fields' => 'ids',
			'meta_query' => array(
				'relation' => 'OR',
				array('key' => 'vp_viewed', 'compare' => 'NOT EXISTS'),
				array('key' => 'vp_viewed', 'value' => '1', 'compare' => '!='),
			),
		));
		$unread_count = intval($q->found_posts);
		wp_reset_postdata();
	}

	$menu_title = __('Inkomende vacatures', 'vacature-plugin');
	if ($unread_count > 0) {
		$menu_title .= ' <span class="update-plugins count-' . esc_attr($unread_count) . '"><span class="plugin-count">' . esc_html($unread_count) . '</span></span>';
	}

	add_submenu_page(
		'edit.php?post_type=vacature',
		__('Inkomende vacatures', 'vacature-plugin'),
		$menu_title,
		'edit_posts',
		'vacature-incoming-entries',
		'vacature_render_incoming_entries_page'
	);
});

function vacature_detect_submission_post_types() {
	// Bekende/waarschijnlijke CPT slugs gebruikt door ACF Form/Hookturn
	$candidates = array(
		'acf_form_entry',
		'acf_form_submission',
		'acf-form-submission',
		'acf_entry',
		'form_submission',
		// Advanced Forms Pro
		'af_entry',
	);
	$found = array();
	foreach ($candidates as $cpt) {
		if (post_type_exists($cpt)) {
			$found[] = $cpt;
		}
	}
	return $found;
}

function vacature_render_incoming_entries_page() {
	if (!current_user_can('edit_posts')) {
		wp_die(__('Je hebt geen rechten om deze pagina te bekijken.', 'vacature-plugin'));
	}

	$form_title_filter = isset($_GET['form_title']) ? sanitize_text_field(wp_unslash($_GET['form_title'])) : '';
	$form_key_filter   = isset($_GET['form_key']) ? sanitize_text_field(wp_unslash($_GET['form_key'])) : '';
	$debug_mode        = isset($_GET['debug']) ? (bool) intval($_GET['debug']) : false;
	$entry_id_view     = isset($_GET['entry_id']) ? absint($_GET['entry_id']) : 0;

	// Advanced Forms Pro: probeer form ID op te lossen aan de hand van form key
	$af_form_id = 0;
	if (!empty($form_key_filter)) {
		// Veel installaties gebruiken 'af_form' CPT voor formulieren
		if (post_type_exists('af_form')) {
			$af_q = new WP_Query(array(
				'post_type' => 'af_form',
				'post_status' => 'any',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => array(
					'relation' => 'OR',
					array('key' => 'key', 'value' => $form_key_filter, 'compare' => '='),
					array('key' => '_key', 'value' => $form_key_filter, 'compare' => '='),
				),
			));
			if ($af_q->have_posts()) {
				$af_form_id = (int) $af_q->posts[0];
			}
			wp_reset_postdata();
		}
	}
	$post_type_filter = isset($_GET['submission_cpt']) ? sanitize_key($_GET['submission_cpt']) : 'af_entry';
	$available_cpts = vacature_detect_submission_post_types();

	echo '<div class="wrap">';
echo '<h1>' . esc_html__('Inkomende vacatures', 'vacature-plugin') . '</h1>';

	// Detail weergave wanneer een entry is gekozen
	if ($entry_id_view) {
		$entry_post = get_post($entry_id_view);
		if ($entry_post && $entry_post->post_type === 'af_entry') {
			// markeer als bekeken
			update_post_meta($entry_post->ID, 'vp_viewed', '1');
			$back_url = add_query_arg(array('post_type' => 'vacature', 'page' => 'vacature-incoming-entries'), admin_url('edit.php'));
			$edit_link = get_edit_post_link($entry_post->ID);
			$form_id = get_post_meta($entry_post->ID, 'entry_form', true);
			$form_title = $form_id ? get_the_title(intval($form_id)) : '';

			echo '<style>.vp-card{background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:16px;margin:16px 0;box-shadow:0 1px 2px rgba(0,0,0,0.04)}.vp-grid{display:grid;gap:16px}@media(min-width:900px){.vp-grid{grid-template-columns:1fr 2fr}}.vp-pill{display:inline-block;background:#f0f6ff;color:#0a4b78;border:1px solid #b3d4ff;border-radius:999px;padding:2px 8px;font-size:11px;margin-left:6px}.vp-table{width:100%;border-collapse:collapse}.vp-table th,.vp-table td{padding:8px 10px;border-bottom:1px solid #eee;vertical-align:top}</style>';
			echo '<div class="vp-card">';
			echo '<div class="vp-grid">';
				// Linkerkolom
				echo '<div>';
				echo '<h2 style="margin-top:0;">' . sprintf(esc_html__('Entry #%d', 'vacature-plugin'), intval($entry_post->ID)) . '</h2>';
				echo '<p><strong>' . esc_html__('Datum:', 'vacature-plugin') . '</strong> ' . esc_html(get_the_date('', $entry_post)) . '</p>';
				if (!empty($form_id)) {
					echo '<p><strong>' . esc_html__('Formulier:', 'vacature-plugin') . '</strong> ' . esc_html($form_title) . ' <span class="vp-pill">ID ' . intval($form_id) . '</span></p>';
				}
				echo '<p>';
				echo '<a class="button" href="' . esc_url($back_url) . '">' . esc_html__('Terug naar overzicht', 'vacature-plugin') . '</a> ';
				if ($edit_link) {
					echo '<a class="button" href="' . esc_url($edit_link) . '">' . esc_html__('Bewerken', 'vacature-plugin') . '</a>';
				}
				echo '</p>';
				echo '</div>';
				// Rechterkolom: velden
				echo '<div>';
				$rows = array();
				if (function_exists('get_fields')) {
					$acf_fields = get_fields($entry_post->ID);
					if (is_array($acf_fields)) {
						foreach ($acf_fields as $k => $v) { $rows[$k] = $v; }
					}
				}
				$known_meta = array('voornaam_achternaam','emailadres','telefoonnummer','woonplaats','vacature_functie','cv_document','bericht','contactvoorkeur');
				foreach ($known_meta as $mk) {
					$val = get_post_meta($entry_post->ID, $mk, true);
					if ($val !== '' && !array_key_exists($mk, $rows)) { $rows[$mk] = $val; }
				}
				if (!empty($rows)) {
					echo '<style>.vp-detail-grid{display:grid;grid-template-columns:1fr;gap:16px}@media(min-width:900px){.vp-detail-grid{grid-template-columns:1fr 1fr}}.vp-item p{margin:.2rem 0}.vp-label{color:#6b7280;font-weight:600}.vp-value{color:#1f2937}.vp-card-inner{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;box-shadow:0 1px 2px rgba(0,0,0,.04)}.vp-card-inner:hover{box-shadow:0 4px 12px rgba(0,0,0,.08);transition:box-shadow .3s ease}</style>';
					echo '<div class="vp-detail-grid">';
					foreach ($rows as $field_key => $value) {
						$label = ucwords(str_replace('_', ' ', $field_key));
						$render = '';
						if ($field_key === 'emailadres' && is_string($value)) {
							$render = '<a href="mailto:' . esc_attr($value) . '" class="vp-value">' . esc_html($value) . '</a>';
						} elseif ($field_key === 'telefoonnummer' && is_string($value)) {
							$tel = preg_replace('/\s+/', '', $value);
							$render = '<a href="tel:' . esc_attr($tel) . '" class="vp-value">' . esc_html($value) . '</a>';
						} elseif ($field_key === 'cv_document' && !empty($value)) {
							$att_id = 0;
							if (is_numeric($value)) { $att_id = intval($value); }
							if (is_array($value) && isset($value['ID'])) { $att_id = intval($value['ID']); }
							$url = $att_id ? wp_get_attachment_url($att_id) : (is_string($value) ? $value : '');
							if ($url) {
								$filename = basename(parse_url($url, PHP_URL_PATH));
								$render = '<a class="button" target="_blank" rel="noopener" href="' . esc_url($url) . '">' . esc_html__('Bekijk / Download', 'vacature-plugin') . ' ' . esc_html($filename) . '</a>';
							} else {
								$render = esc_html__('(Geen bestand gevonden)', 'vacature-plugin');
							}
						} else {
							if (is_array($value)) { $render = esc_html(wp_json_encode($value)); }
							else { $render = nl2br(esc_html((string) $value)); }
						}
						echo '<div class="vp-card-inner vp-item">';
						echo '<p class="vp-label">' . esc_html($label) . '</p>';
						echo '<p class="vp-value">' . $render . '</p>';
						echo '</div>';
					}
					echo '</div>';
				} else {
					echo '<p>' . esc_html__('Geen velden gevonden voor deze entry.', 'vacature-plugin') . '</p>';
				}
				echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}

	if (empty($available_cpts)) {
		echo '<p>' . esc_html__('Geen bekende inzendings post types gevonden. Is de Hookturn ACF Forms plugin actief?', 'vacature-plugin') . '</p>';
		echo '</div>';
		return;
	}

	// Filterformulier
	echo '<form method="get" action="">';
	echo '<input type="hidden" name="post_type" value="vacature" />';
	echo '<input type="hidden" name="page" value="vacature-incoming-entries" />';
	echo '<table class="form-table"><tbody>';
	echo '<tr><th><label for="submission_cpt">' . esc_html__('Inzendings type', 'vacature-plugin') . '</label></th><td>';
	echo '<select id="submission_cpt" name="submission_cpt">';
	echo '<option value="">' . esc_html__('Alle gedetecteerde', 'vacature-plugin') . '</option>';
	foreach ($available_cpts as $cpt) {
		$sel = selected($post_type_filter, $cpt, false);
		echo '<option value="' . esc_attr($cpt) . '" ' . $sel . '>' . esc_html($cpt) . '</option>';
	}
	echo '</select>';
	echo '</td></tr>';
	echo '<tr><th><label for="debug">' . esc_html__('Diagnose modus', 'vacature-plugin') . '</label></th><td>';
	echo '<label><input type="checkbox" id="debug" name="debug" value="1" ' . checked($debug_mode, true, false) . ' /> ' . esc_html__('Toon gevonden post types, recente inzendingen en meta keys', 'vacature-plugin') . '</label>';
	echo '</td></tr>';
	echo '<tr><th><label for="form_title">' . esc_html__('Formuliertitel bevat', 'vacature-plugin') . '</label></th><td>';
	echo '<input type="text" id="form_title" name="form_title" value="' . esc_attr($form_title_filter) . '" class="regular-text" />';
	echo '</td></tr>';
	echo '<tr><th><label for="form_key">' . esc_html__('Form key (exact)', 'vacature-plugin') . '</label></th><td>';
	echo '<input type="text" id="form_key" name="form_key" value="' . esc_attr($form_key_filter) . '" class="regular-text" />';
	echo '</td></tr>';
	echo '</tbody></table>';
	echo '<p><button class="button button-primary" type="submit">' . esc_html__('Filter toepassen', 'vacature-plugin') . '</button></p>';
	echo '</form>';

	// Query samenstellen
	$query_args_base = array(
		'post_status' => 'any',
		'posts_per_page' => 50,
		'orderby' => 'date',
		'order' => 'DESC',
		'suppress_filters' => false,
	);

	$results = array();
	$target_cpts = $post_type_filter ? array($post_type_filter) : $available_cpts;
	foreach ($target_cpts as $cpt) {
		$args = $query_args_base;
		$args['post_type'] = $cpt;
		// Meta filters alleen toepassen indien ingevuld
		$meta_query = array('relation' => 'AND');
		$has_meta_filters = false;
		if (!empty($form_key_filter)) {
			$has_meta_filters = true;
			$exact_keys = array('acf_form_id', 'acf_form_key', 'acf_form', 'form_key');
			$or_exact = array('relation' => 'OR');
			foreach ($exact_keys as $k) {
				$or_exact[] = array(
					'key' => $k,
					'value' => $form_key_filter,
					'compare' => '=',
				);
			}
			$meta_query[] = $or_exact;
		}
		if (!empty($form_title_filter)) {
			$has_meta_filters = true;
			$or_title = array('relation' => 'OR');
			$potential_keys = array('form_title', 'acf_form_title', 'acf_form', 'form_name');
			foreach ($potential_keys as $key) {
				$or_title[] = array(
					'key' => $key,
					'value' => $form_title_filter,
					'compare' => 'LIKE',
				);
			}
			$meta_query[] = $or_title;
		}
		// Advanced Forms Pro entries: CPT 'af_entry' en meta 'entry_form' bevat form post ID
		if ($cpt === 'af_entry') {
			$meta_query_af = array('relation' => 'AND');
			// primary: match op entry_form (form post ID)
			if ($af_form_id) {
				$meta_query_af[] = array('key' => 'entry_form', 'value' => $af_form_id, 'compare' => '=');
			}
			// fallback: als geen ID, probeer key string te matchen in entry_form (sommige installs slaan key op)
			if (!$af_form_id && !empty($form_key_filter)) {
				$meta_query_af[] = array('key' => 'entry_form', 'value' => $form_key_filter, 'compare' => 'LIKE');
			}
			// combineer met bestaande meta_query (form titel LIKE) indien aanwezig
			if ($has_meta_filters && !empty($meta_query) && is_array($meta_query)) {
				$meta_query_af[] = $meta_query;
			}
			if (!empty($meta_query_af)) {
				$args['meta_query'] = $meta_query_af;
			}
		} else {
			if ($has_meta_filters) {
				$args['meta_query'] = $meta_query;
			}
		}

		$q = new WP_Query($args);
		if ($q->have_posts()) {
			while ($q->have_posts()) { $q->the_post();
				$results[] = get_post();
			}
		}
		wp_reset_postdata();
	}

	// Diagnose blok: toon detectie en voorbeelden
	if ($debug_mode) {
		echo '<h2>' . esc_html__('Diagnose', 'vacature-plugin') . '</h2>';
		// Toon gevonden CPTs met aantallen
		echo '<h3>' . esc_html__('Gedetecteerde inzendings post types', 'vacature-plugin') . '</h3>';
		echo '<ul>';
		foreach ($available_cpts as $cpt) {
			$count_obj = wp_count_posts($cpt);
			$total = 0;
			if ($count_obj) {
				foreach ($count_obj as $st => $cnt) { $total += (int) $cnt; }
			}
			echo '<li><strong>' . esc_html($cpt) . '</strong> — ' . intval($total) . ' ' . esc_html__('totaal', 'vacature-plugin') . '</li>';
		}
		echo '</ul>';

		// Laat laatste 3 posts van elk CPT zien met meta keys
		echo '<h3>' . esc_html__('Recente inzendingen en meta keys (per type)', 'vacature-plugin') . '</h3>';
		foreach ($target_cpts as $cpt) {
			echo '<h4>' . esc_html($cpt) . '</h4>';
			$sample_q = new WP_Query(array(
				'post_type' => $cpt,
				'post_status' => 'any',
				'posts_per_page' => 3,
				'orderby' => 'date',
				'order' => 'DESC',
			));
			if ($sample_q->have_posts()) {
				echo '<ol>';
				while ($sample_q->have_posts()) { $sample_q->the_post();
					$pid = get_the_ID();
					echo '<li>#' . intval($pid) . ' — ' . esc_html(get_the_title()) . '<br />';
					$meta = get_post_meta($pid);
					if (!empty($meta)) {
						echo '<small>' . esc_html__('Meta keys:', 'vacature-plugin') . ' ' . esc_html(implode(', ', array_keys($meta))) . '</small>';
					} else {
						echo '<small>' . esc_html__('Geen meta gevonden.', 'vacature-plugin') . '</small>';
					}
					echo '</li>';
				}
				echo '</ol>';
			} else {
				echo '<p><em>' . esc_html__('Geen recente items.', 'vacature-plugin') . '</em></p>';
			}
			wp_reset_postdata();
		}
	}

	if (empty($results)) {
		echo '<p>' . esc_html__('Geen inzendingen gevonden met de huidige filters.', 'vacature-plugin') . '</p>';
		echo '</div>';
		return;
	}

	// Tabel met inzendingen
	echo '<style>.vp-list-grid{display:grid;grid-template-columns:1fr;gap:16px}@media(min-width:900px){.vp-list-grid{grid-template-columns:1fr 1fr}}.vp-list-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;box-shadow:0 1px 2px rgba(0,0,0,.04);transition:box-shadow .3s ease}.vp-list-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.08)}.vp-list-title{font-size:16px;font-weight:600;margin:0 0 8px}.vp-list-meta{color:#6b7280;font-size:12px;margin-bottom:12px}.vp-list-grid .vp-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}.vp-list-label{color:#6b7280;font-weight:600;margin:0}.vp-list-value{color:#1f2937;margin:0}.vp-list-actions{margin-top:14px;display:flex;gap:8px;flex-wrap:wrap}</style>';
	echo '<div class="vp-list-grid">';
	foreach ($results as $entry) {
		$edit_link = get_edit_post_link($entry->ID);
		$fields_html = '';
		$fields_map = array();
		if (function_exists('get_fields')) {
			$fields = get_fields($entry->ID);
			if (!empty($fields) && is_array($fields)) {
				$fields_map = $fields;
			}
		}
		// bekende velden tonen indien aanwezig
		$show_fields = array(
			'voornaam_achternaam' => __('Naam', 'vacature-plugin'),
			'emailadres' => __('Emailadres', 'vacature-plugin'),
			'telefoonnummer' => __('Telefoon', 'vacature-plugin'),
			'woonplaats' => __('Woonplaats', 'vacature-plugin'),
			'vacature_functie' => __('Functie', 'vacature-plugin'),
		);
		$view_link = add_query_arg(array(
			'post_type' => 'vacature',
			'page' => 'vacature-incoming-entries',
			'entry_id' => $entry->ID,
		), admin_url('edit.php'));

		echo '<div class="vp-list-card">';
		$is_unread = get_post_meta($entry->ID, 'vp_viewed', true) !== '1';
		echo '<style>.vp-badge-new{display:inline-block;background:#ef4444;color:#fff;border-radius:999px;padding:2px 8px;font-size:11px;margin-left:8px}</style>';
		$card_title = esc_html(get_the_title($entry));
		if ($is_unread) {
			$card_title .= ' <span class="vp-badge-new">' . esc_html__('Nieuw', 'vacature-plugin') . '</span>';
		}
		echo '<h3 class="vp-list-title">' . $card_title . '</h3>';
		echo '<div class="vp-list-meta">#' . intval($entry->ID) . ' · ' . esc_html(get_the_date('', $entry)) . ' · ' . esc_html($entry->post_type) . '</div>';
		echo '<div class="vp-row">';
		foreach ($show_fields as $key => $label) {
			$value = '';
			if (array_key_exists($key, $fields_map)) { $value = $fields_map[$key]; }
			else { $value = get_post_meta($entry->ID, $key, true); }
			if ($value === '' || $value === null) { continue; }
			if (is_array($value)) { $value = wp_json_encode($value); }
			$extra_class = ($key === 'vacature_functie') ? ' style="font-weight:700;font-size:14px;color:#111827;"' : '';
			echo '<div>';
			echo '<p class="vp-list-label">' . esc_html($label) . '</p>';
			echo '<p class="vp-list-value"' . $extra_class . '>' . esc_html((string) $value) . '</p>';
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="vp-list-actions">';
		echo '<a class="button button-small" href="' . esc_url($view_link) . '">' . esc_html__('Bekijken', 'vacature-plugin') . '</a>';
		if ($edit_link) {
			echo '<a class="button button-small" href="' . esc_url($edit_link) . '">' . esc_html__('Bewerken', 'vacature-plugin') . '</a>';
		}
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';

	echo '</div>';
}

// === Automatische buttonlink voor Vacature Info ===
// Vul het ACF-linkveld 'button' automatisch met de vacaturelink als het leeg is.
add_filter('acf/load_value/key=field_button', function($value, $post_id, $field) {
    if (empty($value) && get_post_type($post_id) === 'vacature') {
        $url = trailingslashit(home_url('vacatures-pagina')) . '?id=' . $post_id;
        $value = array(
            'title' => 'Bekijk vacature',
            'url'   => $url,
            'target'=> '_self',
        );
    }
    return $value;
}, 10, 3);

// Zorg dat bij opslaan ook automatisch de button wordt ingevuld als die nog leeg is
add_action('save_post_vacature', function($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if ($post->post_type !== 'vacature') return;

    $button = get_field('button', $post_id);
    if (empty($button)) {
        $url = trailingslashit(home_url('vacatures-pagina')) . '?id=' . $post_id;
        update_field('button', array(
            'title' => 'Bekijk vacature',
            'url'   => $url,
            'target'=> '_self',
        ), $post_id);
    }
}, 10, 3);

?>
