<?php
/**
 * Reaction class.
 *
 * @package BuddyBoss\Core
 * @since BuddyBoss [BBVERSION]
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BB_Reaction' ) ) {

	/**
	 * BuddyBoss Reaction object.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	class BB_Reaction {

		/**
		 * The single instance of the class.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @var self
		 */
		private static $instance = null;

		/**
		 * Post type.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @var mixed|null
		 */
		private static $post_type;

		/**
		 * User reaction table name.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @var string
		 */
		public static $user_reaction_table = '';

		/**
		 * Reaction data table name.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @var string
		 */
		public static $reaction_data_table = '';

		/**
		 * Get the instance of this class.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @return Controller|BB_Reaction|null
		 */
		public static function instance() {

			if ( null === self::$instance ) {
				$class_name     = __CLASS__;
				self::$instance = new $class_name();
			}

			return self::$instance;
		}

		/**
		 * Constructor method.
		 *
		 * @since BuddyBoss [BBVERSION]
		 */
		public function __construct() {
			self::$post_type = 'bb_reaction';

			self::create_table();

			// Register post type.
			add_action( 'bp_register_post_types', array( $this, 'bb_register_post_type' ), 10 );
		}

		/**
		 * Created custom table for reactions.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @return void
		 */
		public static function create_table() {
			$sql             = array();
			$wpdb            = $GLOBALS['wpdb'];
			$charset_collate = $wpdb->get_charset_collate();
			$bp_prefix       = bp_core_get_table_prefix();

			// Ensure that dbDelta() is defined.
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			// User reaction table.
			$bb_user_reactions         = $bp_prefix . 'bb_user_reactions';
			self::$user_reaction_table = $bb_user_reactions;

			// Table already exists, so maybe upgrade instead?
			$user_reactions_table_exists = $wpdb->query( "SHOW TABLES LIKE '{$bb_user_reactions}';" ); // phpcs:ignore
			if ( ! $user_reactions_table_exists ) {
				$sql[] = "CREATE TABLE IF NOT EXISTS {$bb_user_reactions} (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					user_id bigint(20) NOT NULL,
					reaction_id bigint(20) NOT NULL,
					item_type varchar(20) NOT NULL,
					item_id bigint(20) NOT NULL,
					date_created datetime NOT NULL,
					PRIMARY KEY (id),
					KEY user_id (user_id),
					KEY reaction_id (reaction_id),
					KEY item_type (item_type),
					KEY item_id (item_id),
					KEY date_created (date_created)
				) {$charset_collate};";
			}

			// Reaction data table.
			$bb_reactions_data         = $bp_prefix . 'bb_reactions_data';
			self::$reaction_data_table = $bb_reactions_data;

			// Table already exists, so maybe upgrade instead?
			$reactions_data_table_exists = $wpdb->query( "SHOW TABLES LIKE '{$bb_reactions_data}';" ); // phpcs:ignore
			if ( ! $reactions_data_table_exists ) {
				$sql[] = "CREATE TABLE IF NOT EXISTS {$bb_reactions_data} (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					`name` varchar(255)  NOT NULL,
					`value` longtext DEFAULT NULL,
					rel1 varchar(20) NOT NULL,
					rel2 varchar(20) NOT NULL,
    				rel3 varchar(20) NOT NULL,
					`date` datetime NOT NULL,
					PRIMARY KEY (id),
					KEY `name` (`name`),
					KEY rel1 (rel1),
					KEY rel2 (rel2),
					KEY rel3 (rel3),
					KEY `date` (`date`)
				) {$charset_collate};";
			}

			if ( ! empty( $sql ) ) {
				dbDelta( $sql );
			}
		}

		/**
		 * Register post type.
		 *
		 * @since BuddyBoss [BBVERSION]
		 */
		public function bb_register_post_type() {
			if ( bp_is_root_blog() && ! is_network_admin() ) {
				register_post_type(
					self::$post_type,
					apply_filters(
						'bb_register_reaction_post_type',
						array(
							'description'         => __( 'Reactions', 'buddyboss' ),
							'labels'              => $this->bb_get_reaction_post_type_labels(),
							'menu_icon'           => 'dashicons-reaction-alt',
							'public'              => false,
							'show_ui'             => false,
							'show_in_rest'        => false,
							'exclude_from_search' => true,
							'show_in_admin_bar'   => false,
							'show_in_nav_menus'   => true,
						)
					)
				);
			}
		}

		/**
		 * Return labels used by the reaction post type.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @return array
		 */
		public function bb_get_reaction_post_type_labels() {

			/**
			 * Filters reaction post type labels.
			 *
			 * @since BuddyBoss [BBVERSION]
			 *
			 * @param array $value Associative array (name => label).
			 */
			return apply_filters(
				'bb_get_reaction_post_type_labels',
				array(
					'add_new'               => __( 'New Reaction', 'buddyboss' ),
					'add_new_item'          => __( 'Add New Reaction', 'buddyboss' ),
					'all_items'             => __( 'All Reactions', 'buddyboss' ),
					'edit_item'             => __( 'Edit Reaction', 'buddyboss' ),
					'filter_items_list'     => __( 'Filter Reaction list', 'buddyboss' ),
					'items_list'            => __( 'Reaction list', 'buddyboss' ),
					'items_list_navigation' => __( 'Reaction list navigation', 'buddyboss' ),
					'menu_name'             => __( 'Reactions', 'buddyboss' ),
					'name'                  => __( 'Reactions', 'buddyboss' ),
					'new_item'              => __( 'New Reaction', 'buddyboss' ),
					'not_found'             => __( 'No reactions found', 'buddyboss' ),
					'not_found_in_trash'    => __( 'No reactions found in trash', 'buddyboss' ),
					'search_items'          => __( 'Search Reactions', 'buddyboss' ),
					'singular_name'         => __( 'Reaction', 'buddyboss' ),
					'uploaded_to_this_item' => __( 'Uploaded to this reaction', 'buddyboss' ),
					'view_item'             => __( 'View Reaction', 'buddyboss' ),
				)
			);
		}

		/**
		 * Add new reaction.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @param array $args {
		 *                    Reaction arguments.
		 * @type string $name Name of the reaction.
		 * @type string $icon Icon filename or uploaded file.
		 *                    }
		 *
		 * @return int|void|WP_Error
		 */
		public function bb_add_reaction( $args ) {
			$r = bp_parse_args(
				$args,
				array(
					'name' => '',
					'icon' => '',
				)
			);

			$post_title = ! empty( $r['name'] ) ? sanitize_title( $r['name'] ) : '';
			if ( empty( $post_title ) ) {
				return;
			}

			// Validate if a duplicate name exists before adding.
			$existing_reaction = get_page_by_path( $post_title, OBJECT, self::$post_type );
			if ( $existing_reaction ) {
				return;
			}

			$post_content = array(
				'name' => $r['name'],
				'icon' => ! empty( $r['icon'] ) ? $r['icon'] : '',
			);

			// Prepare reaction data.
			$reaction_data = array(
				'post_title'   => $r['name'],
				'post_name'    => $post_title,
				'post_type'    => self::$post_type,
				'post_status'  => 'publish',
				'post_content' => maybe_serialize( $post_content ),
				'post_author'  => bp_loggedin_user_id(),
			);

			// Insert the new reaction.
			$reaction_id = wp_insert_post( $reaction_data );

			// If the reaction was successfully added, update the transient.
			if ( ! is_wp_error( $reaction_id ) ) {
				// Update bb_reactions transient.
				$this->bb_update_reactions_transient();
			}

			return $reaction_id;
		}

		/**
		 * Update the bb_reactions transient.
		 *
		 * @since BuddyBoss [BBVERSION]
		 */
		private function bb_update_reactions_transient() {

			// Fetch existing reactions.
			$all_reactions  = $this->bb_get_reactions();
			$reactions_data = array();
			if ( ! empty( $all_reactions ) ) {
				foreach ( $all_reactions as $reaction ) {
					$reaction_data = ! empty( $reaction->post_content ) ? maybe_unserialize( $reaction->post_content ) : '';
					if (
						! empty( $reaction_data ) &&
						is_array( $reaction_data ) &&
						isset( $reaction_data['name'] ) &&
						isset( $reaction_data['icon'] )
					) {
						$reactions_data[] = array(
							'id'   => $reaction->ID,
							'name' => $reaction_data['name'],
							'icon' => $reaction_data['icon'],
						);
					}
				}
			}

			$reactions_data = ! empty( $reactions_data ) ? maybe_serialize( $reactions_data ) : '';
			// Update the transient.
			set_transient( 'bb_reactions', $reactions_data );
		}

		/**
		 * Get all reaction data.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @return array
		 */
		private function bb_get_reactions() {
			$args = array(
				'fields'                 => array( 'ids', 'post_title', 'post_content' ),
				'post_type'              => self::$post_type,
				'posts_per_page'         => - 1,
				'orderby'                => 'menu_order',
				'post_status'            => 'publish',
				'suppress_filters'       => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);

			return get_posts( $args );
		}

		/**
		 * Remove reaction.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @param int $reaction_id Reaction id.
		 *
		 * @return void
		 */
		public function bb_remove_reaction( $reaction_id ) {
			if ( empty( $reaction_id ) ) {
				return;
			}

			// Check if the reaction post exists.
			$reaction = get_post( $reaction_id );
			if ( ! isset( $reaction->post_type ) || self::$post_type !== $reaction->post_type ) {
				return;
			}

			$success = wp_delete_post( $reaction_id, true );

			if ( ! empty( $success ) && ! is_wp_error( $success ) ) {
				$this->bb_update_reactions_transient();
			}
		}

		/**
		 * Function to add user reaction.
		 *
		 * @snce BuddyBoss [BBVERSION]
		 *
		 * @param array $args Arguments of user reaction.
		 *
		 * @return int $user_reaction_id
		 */
		public function bb_add_user_item_reaction( $args ) {
			global $wpdb;

			$r = bp_parse_args(
				$args,
				array(
					'reaction_id'  => '',
					'item_type'    => '',
					'item_id'      => '',
					'user_id'      => bp_loggedin_user_id(),
					'date_created' => bp_core_current_time(),
					'error_type'   => 'bool'
				)
			);

			/**
			 * Fires before the add user item reaction in DB.
			 *
			 * @snce BuddyBoss [BBVERSION]
			 *
			 * @param array $r Args of user item reactions.
			 */
			do_action( 'bb_before_add_user_item_reaction', $r );

			// Reaction need reaction ID.
			if ( empty( $r['reaction_id'] ) ) {
				if ( 'wp_error' === $r['error_type'] ) {
					return new WP_Error( 'bb_user_reactions_empty_reaction_id', __( 'The reaction ID is required to add reaction.', 'buddyboss' ) );
				}
				return false;
				// Reaction need item type.
			} elseif ( empty( $r['item_type'] ) ) {
				if ( 'wp_error' === $r['error_type'] ) {
					return new WP_Error( 'bb_user_reactions_empty_item_type', __( 'The item type is required to add reaction.', 'buddyboss' ) );
				}
				return false;
				// Reaction need item id.
			} elseif ( empty( $r['item_id'] ) ) {
				if ( 'wp_error' === $r['error_type'] ) {
					return new WP_Error( 'bb_user_reactions_empty_item_id', __( 'The item id is required to add reaction.', 'buddyboss' ) );
				}
				return false;
			}

			$sql          = "SELECT * FROM " . self::$user_reaction_table . " WHERE item_type = %s AND item_id = %d AND user_id = %d";
			$get_reaction = $wpdb->get_row( $wpdb->prepare( $sql, $r['item_type'], $r['item_id'], $r['user_id'] ) );

			if ( $get_reaction ) {
				$sql = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"UPDATE " . self::$user_reaction_table . " SET
						reaction_id = %d,
						date_created = %s
					WHERE
						id = %d
					",
					$r['reaction_id'],
					$r['date_created'],
					$get_reaction->id
				);
			} else {
				$sql = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"INSERT INTO " . self::$user_reaction_table . " (
						user_id, 
						reaction_id, 
						item_type, 
						item_id, 
						date_created
					) VALUES (
						%d, %d, %s, %d, %s
					)",
					$r['user_id'],
					$r['reaction_id'],
					$r['item_type'],
					$r['item_id'],
					$r['date_created']
				);
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( false === $wpdb->query( $sql ) ) {
				if ( 'wp_error' === $r['error_type'] ) {
					return new WP_Error( 'bb_reaction_cannot_add', __( 'There is an error while adding the reaction.', 'buddyboss' ) );
				} else {
					return false;
				}
			}

			$user_reaction_id = $wpdb->insert_id;

			/**
			 * Fires after the add user item reaction in DB.
			 *
			 * @snce BuddyBoss [BBVERSION]
			 *
			 * @param array $r Args of user item reactions.
			 */
			do_action( 'bb_after_add_user_item_reaction', $user_reaction_id, $r );

			return $user_reaction_id;
		}

		/**
		 * Remove single user reaction based on reaction id.
		 *
		 * @snce BuddyBoss [BBVERSION]
		 *
		 * @param int $user_reaction_id ID of the user reaction.
		 *
		 * @return bool True on success, false on failure or if no user reaction
		 *              is found for the user.
		 */
		public function bb_remove_user_item_reaction( $user_reaction_id ) {
			global $wpdb;

			if ( empty( $user_reaction_id ) ) {
				return false;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$get = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::$user_reaction_table . " WHERE id=%d", $user_reaction_id ) );

			if ( empty( $get ) ) {
				return false;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$deleted = $wpdb->delete(
				self::$user_reaction_table,
				array(
					'id' => $get->id,
				)
			);

			return $deleted;
		}

		/**
		 * Remove user reactions based on args.
		 *
		 * @snce BuddyBoss [BBVERSION]
		 *
		 * @param array $args Args of user reactions.
		 *
		 * @return bool
		 */
		public function bb_remove_user_item_reactions( $args ) {
			global $wpdb;

			$r = bp_parse_args(
				$args,
				array(
					'reaction_id' => '',
					'item_id'     => '',
					'user_id'     => bp_loggedin_user_id(),
					'error_type'  => 'bool',
				)
			);

			// Reaction need reaction ID.
			if ( empty( $r['reaction_id'] ) ) {
				if ( 'wp_error' === $r['error_type'] ) {
					return new WP_Error( 'bb_user_reactions_empty_reaction_id', __( 'The reaction ID is required to remove reaction.', 'buddyboss' ) );
				}
				return false;
				// Reaction need item id.
			} elseif ( empty( $r['item_id'] ) ) {
				if ( 'wp_error' === $r['error_type'] ) {
					return new WP_Error( 'bb_user_reactions_empty_item_id', __( 'The item id is required to remove reaction.', 'buddyboss' ) );
				}
				return false;
			}

			$sql = "SELECT * FROM " . self::$user_reaction_table . " WHERE reaction_id = %d AND item_id = %d AND user_id = %d";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$get_reaction = $wpdb->get_row( $wpdb->prepare( $sql, $r['item_type'], $r['item_id'], $r['user_id'] ) );

			if ( empty( $get_reaction ) ) {
				return false;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$deleted = $wpdb->delete(
				self::$user_reaction_table,
				array(
					'id' => $get_reaction->id,
				)
			);

			return $deleted;
		}
	}
}
