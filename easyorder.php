<?php
/**
 * Plugin Name: EasyOrder
 * Description: Easily display products and receive email orders.
 * Version:     0.2.8
 * Author:      Dynamic Technologies
 * Author URI:  https://bedynamic.tech
 */

defined( 'ABSPATH' ) || exit;

// ─── Constants ────────────────────────────────────────────────────────────────

define( 'EO_CPT',    'eo_product' );
define( 'EO_TAX',    'eo_category' );
define( 'EO_STRAIN', 'eo_type' );
define( 'EO_NONCE',  'eo_submit_nonce' );

// ─── Settings Page ────────────────────────────────────────────────────────────

add_action( 'admin_menu', 'eo_register_settings_page' );
function eo_register_settings_page() {
    add_options_page( 'EasyOrder Settings', 'EasyOrder', 'manage_options', 'easyorder-settings', 'eo_render_settings_page' );
}

add_action( 'admin_init', 'eo_register_settings' );
function eo_register_settings() {
    register_setting( 'easyorder_settings', 'easyorder_recipients', [ 'sanitize_callback' => 'eo_sanitize_recipients' ] );
    register_setting( 'easyorder_settings', 'easyorder_send_confirmation', [ 'sanitize_callback' => 'absint' ] );
}

function eo_sanitize_recipients( $value ) {
    $emails = array_filter( array_map( 'trim', explode( ',', $value ) ), 'is_email' );
    return implode( ',', $emails );
}

function eo_render_settings_page() {
    ?>

```
<div class="wrap">
    <h1>EasyOrder Settings</h1>

    <div class="notice notice-info" style="padding:12px 16px;margin-bottom:20px;">
        <p style="margin:0;">The product table automatically hides columns that have no data:</p>
        <ul style="margin:8px 0 0 20px;list-style:disc;">
            <li><strong>SKU</strong> — hidden if no products have a SKU entered</li>
            <li><strong>Type</strong> — hidden if no Types have been created under Products → Types</li>
            <li><strong>Category</strong> — hidden if no Categories have been created under Products → Categories</li>
        </ul>
    </div>
    <form method="post" action="options.php">
        <?php settings_fields( 'easyorder_settings' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="easyorder_recipients">Order Email Recipients</label></th>
                <td>
                    <input
                        type="text"
                        id="easyorder_recipients"
                        name="easyorder_recipients"
                        value="<?php echo esc_attr( get_option( 'easyorder_recipients', '' ) ); ?>"
                        class="regular-text"
                        placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
                    />
                    <p class="description">Comma-separated list of email addresses to receive order notifications. Leave blank to use the site admin email (<strong><?php echo esc_html( get_option( 'admin_email' ) ); ?></strong>).</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Confirmation Email</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="easyorder_send_confirmation"
                            value="1"
                            <?php checked( get_option( 'easyorder_send_confirmation', 1 ), 1 ); ?>
                        />
                        Send a confirmation email to the user after they submit an order request
                    </label>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    <div style="position:fixed;bottom:24px;right:24px;">
        <a href="https://ko-fi.com/T6T61SZTST" target="_blank">
            <img height="36" style="border:0;height:36px;" src="https://storage.ko-fi.com/cdn/kofi6.png?v=6" alt="Buy Me a Coffee at ko-fi.com" />
        </a>
    </div>
</div>
<?php
```

}

// ─── Custom Post Type & Taxonomies ────────────────────────────────────────────

add_action( ‘init’, ‘eo_register_cpt’ );
function eo_register_cpt() {
register_post_type( EO_CPT, [
‘labels’       => [
‘name’               => ‘Products’,
‘singular_name’      => ‘Product’,
‘add_new_item’       => ‘Add New Product’,
‘edit_item’          => ‘Edit Product’,
‘new_item’           => ‘New Product’,
‘view_item’          => ‘View Product’,
‘search_items’       => ‘Search Products’,
‘not_found’          => ‘No products found’,
‘not_found_in_trash’ => ‘No products found in Trash’,
‘menu_name’          => ‘Products’,
],
‘public’       => true,
‘show_in_menu’ => true,
‘show_in_rest’ => false,
‘menu_icon’    => ‘dashicons-cart’,
‘supports’     => [ ‘title’ ],
‘has_archive’  => false,
‘rewrite’      => [ ‘slug’ => ‘products’ ],
] );

```
register_taxonomy( EO_TAX, EO_CPT, [
    'labels'            => [
        'name'          => 'Product Categories',
        'singular_name' => 'Product Category',
        'search_items'  => 'Search Categories',
        'all_items'     => 'All Categories',
        'edit_item'     => 'Edit Category',
        'update_item'   => 'Update Category',
        'add_new_item'  => 'Add New Category',
        'new_item_name' => 'New Category Name',
        'menu_name'     => 'Categories',
    ],
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_in_rest'      => false,
    'show_admin_column' => true,
    'rewrite'           => [ 'slug' => 'product-category' ],
] );

register_taxonomy( EO_STRAIN, EO_CPT, [
    'labels'            => [
        'name'          => 'Types',
        'singular_name' => 'Type',
        'search_items'  => 'Search Types',
        'all_items'     => 'All Types',
        'edit_item'     => 'Edit Type',
        'update_item'   => 'Update Type',
        'add_new_item'  => 'Add New Type',
        'new_item_name' => 'New Type Name',
        'menu_name'     => 'Types',
    ],
    'hierarchical'      => false,
    'show_ui'           => true,
    'show_in_rest'      => false,
    'show_admin_column' => true,
    'rewrite'           => [ 'slug' => 'product-type' ],
] );
```

}

// ─── Meta Box ─────────────────────────────────────────────────────────────────

add_action( ‘add_meta_boxes’, ‘eo_add_meta_boxes’ );
function eo_add_meta_boxes() {
add_meta_box( ‘eo_product_details’, ‘Product Details’, ‘eo_render_meta_box’, EO_CPT, ‘normal’, ‘high’ );
}

function eo_render_meta_box( $post ) {
$sku   = get_post_meta( $post->ID, ‘_eo_sku’,   true );
$price = get_post_meta( $post->ID, ‘_eo_price’, true );
$stock = get_post_meta( $post->ID, ‘_eo_stock’, true );
$categories       = get_terms( [ ‘taxonomy’ => EO_TAX,    ‘hide_empty’ => false ] );
$strains          = get_terms( [ ‘taxonomy’ => EO_STRAIN,  ‘hide_empty’ => false ] );
$selected_cats    = wp_get_post_terms( $post->ID, EO_TAX,    [ ‘fields’ => ‘ids’ ] );
$selected_strains = wp_get_post_terms( $post->ID, EO_STRAIN,  [ ‘fields’ => ‘ids’ ] );

```
wp_nonce_field( 'eo_save_meta', 'eo_meta_nonce' );
?>
<table class="form-table" style="width:100%">
    <tr>
        <th><label for="eo_sku">SKU</label></th>
        <td><input type="text" id="eo_sku" name="eo_sku" value="<?php echo esc_attr( $sku ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="eo_price">Price ($)</label></th>
        <td><input type="number" id="eo_price" name="eo_price" value="<?php echo esc_attr( $price ); ?>" step="0.01" min="0" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="eo_stock">Stock</label></th>
        <td><input type="number" id="eo_stock" name="eo_stock" value="<?php echo esc_attr( $stock ); ?>" min="0" step="1" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="eo_types">Type</label></th>
        <td>
            <?php if ( ! empty( $strains ) && ! is_wp_error( $strains ) ) : ?>
                <select name="eo_types" id="eo_types">
                    <option value="">— None —</option>
                    <?php foreach ( $strains as $strain ) : ?>
                        <option value="<?php echo esc_attr( $strain->term_id ); ?>" <?php selected( in_array( $strain->term_id, (array) $selected_strains, true ) ); ?>>
                            <?php echo esc_html( $strain->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <p style="margin:6px 0 0;"><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=' . EO_STRAIN . '&post_type=' . EO_CPT ) ); ?>"><?php echo empty( $strains ) || is_wp_error( $strains ) ? 'Add types first' : 'Manage types'; ?></a></p>
        </td>
    </tr>
    <tr>
        <th><label for="eo_categories">Category</label></th>
        <td>
            <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                <select name="eo_categories" id="eo_categories">
                    <option value="">— None —</option>
                    <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( in_array( $cat->term_id, (array) $selected_cats, true ) ); ?>>
                            <?php echo esc_html( $cat->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <p style="margin:6px 0 0;"><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=' . EO_TAX . '&post_type=' . EO_CPT ) ); ?>"><?php echo empty( $categories ) || is_wp_error( $categories ) ? 'Add categories first' : 'Manage categories'; ?></a></p>
        </td>
    </tr>
</table>
<?php
```

}

add_action( ‘save_post’, ‘eo_save_meta’ );
function eo_save_meta( $post_id ) {
if (
get_post_type( $post_id ) !== EO_CPT ||
! isset( $_POST[‘eo_meta_nonce’] ) ||
! wp_verify_nonce( $_POST[‘eo_meta_nonce’], ‘eo_save_meta’ ) ||
( defined( ‘DOING_AUTOSAVE’ ) && DOING_AUTOSAVE ) ||
! current_user_can( ‘edit_post’, $post_id )
) return;

```
if ( isset( $_POST['eo_sku'] ) ) {
    update_post_meta( $post_id, '_eo_sku', sanitize_text_field( $_POST['eo_sku'] ) );
}
if ( isset( $_POST['eo_price'] ) ) {
    update_post_meta( $post_id, '_eo_price', (string) abs( (float) $_POST['eo_price'] ) );
}
if ( isset( $_POST['eo_stock'] ) ) {
    update_post_meta( $post_id, '_eo_stock', (string) absint( $_POST['eo_stock'] ) );
}

$strain_ids = ! empty( $_POST['eo_types'] ) ? [ intval( $_POST['eo_types'] ) ] : [];
wp_set_post_terms( $post_id, $strain_ids, EO_STRAIN );

$cat_ids = ! empty( $_POST['eo_categories'] ) ? [ intval( $_POST['eo_categories'] ) ] : [];
wp_set_post_terms( $post_id, $cat_ids, EO_TAX );
```

}

add_action( ‘template_redirect’, ‘eo_redirect_if_logged_out’ );
function eo_redirect_if_logged_out() {
if ( ! is_singular() || is_user_logged_in() ) return;
$post = get_post();
if ( $post && has_shortcode( $post->post_content, ‘easyorder_form’ ) ) {
wp_safe_redirect( wp_login_url( get_permalink() ) );
exit;
}
}

// ─── Shortcode: [easyorder_form] ──────────────────────────────────────────

add_shortcode( ‘easyorder_form’, ‘eo_render_shortcode’ );
function eo_render_shortcode( $atts ) {
if ( ! is_user_logged_in() ) {
return ‘’;
}

```
$products = get_posts( [
    'post_type'      => EO_CPT,
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'post_status'    => 'publish',
] );

if ( empty( $products ) ) {
    return '<p>No products are currently available.</p>';
}

$categories = get_terms( [ 'taxonomy' => EO_TAX,   'hide_empty' => true ] );
$strains    = get_terms( [ 'taxonomy' => EO_STRAIN, 'hide_empty' => true ] );

$has_sku  = false;
$has_type = ! empty( $strains ) && ! is_wp_error( $strains );
$has_cat  = ! empty( $categories ) && ! is_wp_error( $categories );

if ( ! $has_sku ) {
    foreach ( $products as $p ) {
        if ( get_post_meta( $p->ID, '_eo_sku', true ) ) { $has_sku = true; break; }
    }
}

ob_start();
?>
<div id="eo-wrap">

    <div id="eo-success" style="display:none;" class="eo-notice eo-success">
        Your order request has been sent successfully.
    </div>
    <div id="eo-error" style="display:none;" class="eo-notice eo-error">
        Something went wrong. Please try again.
    </div>

    <div class="eo-controls">
        <input type="text" id="eo-search" placeholder="Search products…" style="min-width:220px;" />
        <?php if ( ! empty( $strains ) && ! is_wp_error( $strains ) ) : ?>
        <select id="eo-type-filter">
            <option value="">All Types</option>
            <?php foreach ( $strains as $strain ) : ?>
                <option value="<?php echo esc_attr( $strain->slug ); ?>"><?php echo esc_html( $strain->name ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
        <select id="eo-category-filter">
            <option value="">All Categories</option>
            <?php foreach ( $categories as $cat ) : ?>
                <option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button type="button" id="eo-reset-filters">Reset Filters</button>
        <label class="eo-instock-toggle">
            <input type="checkbox" id="eo-instock-only" /> In stock only
        </label>
    </div>

    <form id="eo-form" novalidate>
        <?php wp_nonce_field( EO_NONCE, EO_NONCE ); ?>
        <input type="hidden" name="action" value="eo_submit" />

        <table class="eo-table">
            <thead>
                <tr>
                    <?php if ( $has_sku ) : ?><th>SKU</th><?php endif; ?>
                    <th class="eo-sortable" data-col="<?php echo $has_sku ? 1 : 0; ?>" data-type="string">Product <span class="eo-sort-icon"></span></th>
                    <?php if ( $has_type ) : ?><th class="eo-sortable" data-col="<?php echo ( $has_sku ? 2 : 1 ); ?>" data-type="string">Type <span class="eo-sort-icon"></span></th><?php endif; ?>
                    <?php if ( $has_cat ) : ?><th class="eo-sortable" data-col="<?php echo ( $has_sku ? 2 : 1 ) + ( $has_type ? 1 : 0 ); ?>" data-type="string">Category <span class="eo-sort-icon"></span></th><?php endif; ?>
                    <th class="eo-sortable" data-col="<?php echo ( $has_sku ? 2 : 1 ) + ( $has_type ? 1 : 0 ) + ( $has_cat ? 1 : 0 ); ?>" data-type="number">Price <span class="eo-sort-icon"></span></th>
                    <th class="eo-sortable" data-col="<?php echo ( $has_sku ? 2 : 1 ) + ( $has_type ? 1 : 0 ) + ( $has_cat ? 1 : 0 ) + 1; ?>" data-type="number">In Stock <span class="eo-sort-icon"></span></th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody id="eo-tbody">
                <?php foreach ( $products as $product ) :
                    $sku          = get_post_meta( $product->ID, '_eo_sku',   true );
                    $price        = get_post_meta( $product->ID, '_eo_price', true );
                    $stock        = (int) get_post_meta( $product->ID, '_eo_stock', true );
                    $strain_terms = get_the_terms( $product->ID, EO_STRAIN );
                    $cat_terms    = get_the_terms( $product->ID, EO_TAX );
                    $strain_name  = ( $strain_terms && ! is_wp_error( $strain_terms ) ) ? $strain_terms[0]->name : '';
                    $strain_slug  = ( $strain_terms && ! is_wp_error( $strain_terms ) ) ? $strain_terms[0]->slug : '';
                    $cat_name     = ( $cat_terms && ! is_wp_error( $cat_terms ) ) ? $cat_terms[0]->name : '';
                    $cat_slug     = ( $cat_terms && ! is_wp_error( $cat_terms ) ) ? $cat_terms[0]->slug : '';
                ?>
                <tr
                    class="eo-row <?php echo $stock === 0 ? 'eo-out-of-stock' : ''; ?>"
                    data-name="<?php echo esc_attr( strtolower( $product->post_title ) ); ?>"
                    data-sku="<?php echo esc_attr( strtolower( $sku ) ); ?>"
                    data-type="<?php echo esc_attr( $strain_slug ); ?>"
                    data-category="<?php echo esc_attr( $cat_slug ); ?>"
                    data-stock="<?php echo esc_attr( $stock ); ?>"
                >
                    <?php if ( $has_sku ) : ?><td data-label="SKU"><?php echo esc_html( $sku ?: '—' ); ?></td><?php endif; ?>
                    <td data-label="Product" class="eo-product-name"><?php echo esc_html( $product->post_title ); ?></td>
                    <?php if ( $has_type ) : ?><td data-label="Type"><?php echo esc_html( $strain_name ?: '—' ); ?></td><?php endif; ?>
                    <?php if ( $has_cat ) : ?><td data-label="Category"><?php echo esc_html( $cat_name ?: '—' ); ?></td><?php endif; ?>
                    <td data-label="Price" data-value="<?php echo esc_attr( $price ?: 0 ); ?>"><?php echo $price ? '$' . number_format( (float) $price, 2 ) : '—'; ?></td>
                    <td data-label="In Stock" data-value="<?php echo esc_attr( $stock ); ?>">
                        <?php if ( $stock > 0 ) : ?>
                            <span class="eo-in-stock"><?php echo $stock; ?></span>
                        <?php else : ?>
                            <span class="eo-no-stock">Out of stock</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Qty">
                        <input
                            type="number"
                            class="eo-qty"
                            name="qty[<?php echo esc_attr( $product->ID ); ?>]"
                            data-id="<?php echo esc_attr( $product->ID ); ?>"
                            data-stock="<?php echo esc_attr( $stock ); ?>"
                            min="0"
                            max="<?php echo esc_attr( $stock ); ?>"
                            value="0"
                            <?php echo $stock === 0 ? 'disabled' : ''; ?>
                        />
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p id="eo-no-results" style="display:none;">No products match your search.</p>

        <div class="eo-fields">
            <label for="eo-sender-notes">Notes (optional)</label>
            <textarea id="eo-sender-notes" name="sender_notes" rows="3" placeholder="Delivery instructions, special requests…"></textarea>
        </div>

        <div class="eo-actions">
            <button type="submit" id="eo-submit" class="wp-element-button button">Send Order Request</button>
            <span id="eo-spinner" style="display:none;">Sending…</span>
        </div>
    </form>
</div>

<style>
    #eo-wrap { width: 100%; font-family: inherit; box-sizing: border-box; }

    /* Notices */
    .eo-notice { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
    .eo-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .eo-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* Controls */
    .eo-controls { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; }
    .eo-controls input[type="text"],
    .eo-controls select { padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; flex: 1; min-width: 140px; box-sizing: border-box; }
    .eo-instock-toggle { display: flex; align-items: center; gap: 6px; font-size: 14px; white-space: nowrap; cursor: pointer; }
    #eo-reset-filters { padding: 8px 14px; border: 1px solid #999; border-radius: 4px; background: #f0f0f0; font-size: 14px; cursor: pointer; white-space: nowrap; color: #333; }
    #eo-reset-filters:hover { background: #e0e0e0; border-color: #777; }

    /* Table */
    .eo-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: auto; }
    .eo-table th { background: #f5f5f5; padding: 10px 12px; text-align: left; border-bottom: 2px solid #ddd; white-space: nowrap; }
    .eo-table td { padding: 10px 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .eo-table tr:hover td { background: #fafafa; }
    .eo-out-of-stock td { opacity: 0.5; }
    .eo-product-name { font-weight: 500; }
    .eo-in-stock { color: #2e7d32; font-weight: 600; }
    .eo-no-stock { color: #b71c1c; font-size: 0.85em; }
    .eo-qty { padding: 6px; border: 1px solid #ccc; border-radius: 4px; text-align: center; width: 68px; box-sizing: border-box; }

    /* Sortable headers */
    .eo-sortable { cursor: pointer; user-select: none; }
    .eo-sortable:hover { background: #ececec; }
    .eo-sort-icon { font-size: 20px; opacity: 0.5; margin-left: 6px; }
    .eo-sortable.asc  .eo-sort-icon,
    .eo-sortable.desc .eo-sort-icon { opacity: 1; }
    .eo-sortable.asc  .eo-sort-icon::before { content: '↑'; }
    .eo-sortable.desc .eo-sort-icon::before { content: '↓'; }
    .eo-sortable:not(.asc):not(.desc) .eo-sort-icon::before { content: '↕'; }

    /* Notes + actions */
    .eo-fields { margin-bottom: 20px; }
    .eo-fields label { display: block; font-weight: 600; margin-bottom: 6px; }
    .eo-fields textarea { padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; width: 100%; box-sizing: border-box; }
    .eo-actions { display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 4px; }
    #eo-spinner { color: #666; font-style: italic; }

    /* ── Mobile ── */
    @media ( max-width: 640px ) {
        .eo-table thead { display: none; }
        .eo-table, .eo-table tbody, .eo-table tr, .eo-table td { display: block; width: 100%; }
        .eo-table tr { border: 1px solid #ddd; border-radius: 6px; margin-bottom: 12px; padding: 4px 0; background: #fff; }
        .eo-table tr:hover td { background: transparent; }
        .eo-table td { border-bottom: 1px solid #f0f0f0; padding: 8px 14px; display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .eo-table td:last-child { border-bottom: none; }
        .eo-table td::before { content: attr(data-label); font-weight: 600; font-size: 13px; color: #555; flex-shrink: 0; }
        .eo-out-of-stock { opacity: 0.55; }
        .eo-qty { width: 80px; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form         = document.getElementById('eo-form');
    const submit       = document.getElementById('eo-submit');
    const spinner      = document.getElementById('eo-spinner');
    const success      = document.getElementById('eo-success');
    const error        = document.getElementById('eo-error');
    const tbody        = document.getElementById('eo-tbody');
    const noResults    = document.getElementById('eo-no-results');
    const search       = document.getElementById('eo-search');
    const strainFilter = document.getElementById('eo-type-filter');
    const catFilter    = document.getElementById('eo-category-filter');
    const stockOnly    = document.getElementById('eo-instock-only');
    const resetBtn     = document.getElementById('eo-reset-filters');

    // ── Qty clamping ──────────────────────────────────────────────────────
    form.querySelectorAll('.eo-qty').forEach(function (input) {
        input.addEventListener('input', function () {
            const max = parseInt(input.getAttribute('max'), 10);
            const val = parseInt(input.value, 10);
            if ( val > max ) input.value = max;
            if ( val < 0 )   input.value = 0;
        });
    });

    // ── Filter ────────────────────────────────────────────────────────────
    function applyFilters() {
        const term   = search       ? search.value.toLowerCase().trim() : '';
        const strain = strainFilter ? strainFilter.value : '';
        const cat    = catFilter    ? catFilter.value    : '';
        const stock  = stockOnly    ? stockOnly.checked  : false;
        let visible  = 0;

        tbody.querySelectorAll('tr.eo-row').forEach(function (row) {
            const matchText   = ! term   || row.dataset.name.includes(term) || row.dataset.sku.includes(term);
            const matchStrain = ! strain || row.dataset.type.split(' ').includes(strain);
            const matchCat    = ! cat    || row.dataset.category.split(' ').includes(cat);
            const matchStock  = ! stock  || parseInt(row.dataset.stock, 10) > 0;
            const show        = matchText && matchStrain && matchCat && matchStock;

            row.style.display = show ? '' : 'none';
            if ( show ) visible++;
        });

        noResults.style.display = visible === 0 ? 'block' : 'none';
    }

    if ( search )       search.addEventListener('input', applyFilters);
    if ( strainFilter ) strainFilter.addEventListener('change', applyFilters);
    if ( catFilter )    catFilter.addEventListener('change', applyFilters);
    if ( stockOnly )    stockOnly.addEventListener('change', applyFilters);

    if ( resetBtn ) resetBtn.addEventListener('click', function () {
        if ( search )       search.value         = '';
        if ( strainFilter ) strainFilter.value   = '';
        if ( catFilter )    catFilter.value      = '';
        if ( stockOnly )    stockOnly.checked    = false;
        applyFilters();
    });

    // ── Sorting ───────────────────────────────────────────────────────────
    document.querySelectorAll('.eo-sortable').forEach(function (th) {
        th.addEventListener('click', function () {
            const col   = parseInt(th.dataset.col, 10);
            const type  = th.dataset.type;
            const dir   = th.classList.contains('asc') ? 'desc' : 'asc';

            document.querySelectorAll('.eo-sortable').forEach(h => h.classList.remove('asc', 'desc'));
            th.classList.add(dir);

            const rows = Array.from(tbody.querySelectorAll('tr.eo-row'));
            rows.sort(function (a, b) {
                const aCell = a.querySelectorAll('td')[col];
                const bCell = b.querySelectorAll('td')[col];
                const aVal  = aCell.dataset.value !== undefined ? aCell.dataset.value : aCell.textContent.trim();
                const bVal  = bCell.dataset.value !== undefined ? bCell.dataset.value : bCell.textContent.trim();
                const cmp   = type === 'number' ? parseFloat(aVal) - parseFloat(bVal) : aVal.localeCompare(bVal);
                return dir === 'asc' ? cmp : -cmp;
            });

            rows.forEach(r => tbody.appendChild(r));
        });
    });

    // ── Submit ────────────────────────────────────────────────────────────
    let confirmed = false;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        success.style.display = 'none';
        error.style.display   = 'none';

        const items = [];
        form.querySelectorAll('.eo-qty').forEach(function (input) {
            const qty = parseInt(input.value, 10);
            if ( qty > 0 ) {
                items.push({
                    id:  input.dataset.id,
                    qty: qty,
                });
            }
        });

        if ( items.length === 0 ) {
            alert('Please enter a quantity for at least one product.');
            return;
        }

        if ( ! confirmed ) {
            confirmed             = true;
            submit.textContent    = 'Are You Sure?';
            submit.style.opacity  = '0.85';
            setTimeout(function () {
                confirmed            = false;
                submit.textContent   = 'Send Order Request';
                submit.style.opacity = '';
            }, 4000);
            return;
        }

        confirmed             = false;
        submit.textContent    = 'Send Order Request';
        submit.style.opacity  = '';
        submit.disabled       = true;
        spinner.style.display = 'inline';

        const data = new FormData(form);
        data.append('items', JSON.stringify(items));

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: data,
        })
        .then(r => r.json())
        .then(function (res) {
            submit.disabled       = false;
            spinner.style.display = 'none';
            if ( res.success ) {
                success.style.display = 'block';
                form.reset();
                form.querySelectorAll('.eo-qty').forEach(i => i.value = 0);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                error.style.display = 'block';
                error.textContent   = res.data || 'Something went wrong. Please try again.';
            }
        })
        .catch(function () {
            submit.disabled       = false;
            spinner.style.display = 'none';
            error.style.display   = 'block';
        });
    });
});
</script>
<?php
return ob_get_clean();
```

}

// ─── AJAX Handler ─────────────────────────────────────────────────────────────

add_action( ‘wp_ajax_eo_submit’, ‘eo_handle_submission’ );

function eo_handle_submission() {
if ( ! is_user_logged_in() ) {
wp_send_json_error( ‘You must be logged in to submit an order.’ );
}

```
if ( ! isset( $_POST[ EO_NONCE ] ) || ! wp_verify_nonce( $_POST[ EO_NONCE ], EO_NONCE ) ) {
    wp_send_json_error( 'Invalid nonce.' );
}

$user         = wp_get_current_user();
$sender_name  = $user->display_name;
$sender_email = $user->user_email;
$sender_notes = sanitize_textarea_field( $_POST['sender_notes'] ?? '' );
$items        = json_decode( stripslashes( $_POST['items'] ?? '[]' ), true );

$saved_recipients  = get_option( 'easyorder_recipients', '' );
$recipient_raw     = $saved_recipients ?: get_option( 'admin_email' );
$recipient         = array_values( array_filter( array_map( 'trim', explode( ',', $recipient_raw ) ), 'is_email' ) );
if ( empty( $recipient ) ) {
    $recipient = [ get_option( 'admin_email' ) ];
}
$send_confirmation  = (bool) get_option( 'easyorder_send_confirmation', 1 );

if ( empty( $items ) ) {
    wp_send_json_error( 'No items selected.' );
}

foreach ( $items as $item ) {
    $post_id = (int) ( $item['id'] ?? 0 );
    if ( ! $post_id ) continue;
    $stock = (int) get_post_meta( $post_id, '_eo_stock', true );
    if ( (int) $item['qty'] > $stock ) {
        $name = get_the_title( $post_id ) ?: "Product #{$post_id}";
        wp_send_json_error( "Requested quantity for \"{$name}\" exceeds available stock." );
    }
}

$site_name = get_bloginfo( 'name' );
$subject   = "Order Request — {$sender_name}";

$rows  = '';
$total = 0;
$i     = 0;

foreach ( $items as $item ) {
    $post_id    = (int) ( $item['id'] ?? 0 );
    $post       = $post_id ? get_post( $post_id ) : null;
    $qty        = (int) ( $item['qty'] ?? 0 );

    if ( ! $post || $post->post_status !== 'publish' || $post->post_type !== EO_CPT ) continue;

    $name       = esc_html( $post->post_title );
    $sku        = esc_html( get_post_meta( $post_id, '_eo_sku',   true ) ?: '—' );
    $price      = get_post_meta( $post_id, '_eo_price', true );
    $price      = $price !== '' ? (float) $price : null;
    $type_terms = get_the_terms( $post_id, EO_STRAIN );
    $strain     = esc_html( ( $type_terms && ! is_wp_error( $type_terms ) ) ? $type_terms[0]->name : '—' );
    $price_str  = $price !== null ? '$' . number_format( $price, 2 ) : '—';
    $line_total = $price !== null ? '$' . number_format( $price * $qty, 2 ) : '—';
    $bg         = $i++ % 2 === 0 ? '#ffffff' : '#f9f9f9';

    if ( $price !== null ) $total += $price * $qty;

    $rows .= "<tr style='background:{$bg};'>
        <td style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;'>{$name}</td>
        <td style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;color:#888;'>{$sku}</td>
        <td style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;'>{$strain}</td>
        <td style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;'>{$price_str}</td>
        <td style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;text-align:center;'>{$qty}</td>
        <td style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;font-weight:600;text-align:right;'>{$line_total}</td>
    </tr>";
}

$total_block = $total > 0
    ? "<p style='margin:16px 0 0;text-align:right;font-size:15px;'><strong>Estimated Total: \$" . number_format( $total, 2 ) . "</strong></p>"
    : '';

$notes_block = $sender_notes
    ? "<p style='margin:20px 0 0;padding:12px 14px;background:#f5f5f5;border-left:3px solid #999;font-size:14px;'><strong>Notes:</strong> " . nl2br( esc_html( $sender_notes ) ) . "</p>"
    : '';

$body = "<!DOCTYPE html>
```

<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:20px;background:#f0f0f0;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0'>
<tr><td align='center'>
<table style='width:94%;max-width:960px;background:#ffffff;border-radius:6px;overflow:hidden;' cellpadding='0' cellspacing='0'>
    <tr>
        <td style='padding:20px 24px;border-bottom:1px solid #eee;'>
            <p style='margin:0;font-size:13px;color:#999;'>{$site_name}</p>
            <h1 style='margin:4px 0 0;font-size:18px;color:#222;'>Order Request</h1>
        </td>
    </tr>
    <tr>
        <td style='padding:14px 24px;border-bottom:1px solid #eee;font-size:14px;color:#444;'>
            From <strong>{$sender_name}</strong> &nbsp;&middot;&nbsp;
            <a href='mailto:{$sender_email}' style='color:#0073aa;text-decoration:none;'>{$sender_email}</a>
        </td>
    </tr>
    <tr>
        <td style='padding:16px 24px 20px;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
                <thead>
                    <tr style='background:#f5f5f5;'>
                        <th style='padding:8px 12px;text-align:left;font-size:12px;color:#777;border-bottom:1px solid #ddd;text-transform:uppercase;letter-spacing:.5px;'>Product</th>
                        <th style='padding:8px 12px;text-align:left;font-size:12px;color:#777;border-bottom:1px solid #ddd;text-transform:uppercase;letter-spacing:.5px;'>SKU</th>
                        <th style='padding:8px 12px;text-align:left;font-size:12px;color:#777;border-bottom:1px solid #ddd;text-transform:uppercase;letter-spacing:.5px;'>Type</th>
                        <th style='padding:8px 12px;text-align:left;font-size:12px;color:#777;border-bottom:1px solid #ddd;text-transform:uppercase;letter-spacing:.5px;'>Price</th>
                        <th style='padding:8px 12px;text-align:center;font-size:12px;color:#777;border-bottom:1px solid #ddd;text-transform:uppercase;letter-spacing:.5px;'>Qty</th>
                        <th style='padding:8px 12px;text-align:right;font-size:12px;color:#777;border-bottom:1px solid #ddd;text-transform:uppercase;letter-spacing:.5px;'>Total</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
            {$total_block}
            {$notes_block}
        </td>
    </tr>
    <tr>
        <td style='padding:12px 24px;background:#f9f9f9;border-top:1px solid #eee;'>
            <p style='margin:0;font-size:11px;color:#bbb;'>Sent via {$site_name}</p>
        </td>
    </tr>
</table>
</td></tr>
</table>
</body>
</html>";

```
$headers = [
    'Content-Type: text/html; charset=UTF-8',
    "Reply-To: {$sender_email}",
];

$admin_sent = wp_mail( $recipient, $subject, $body, $headers );

if ( ! $admin_sent ) {
    error_log( 'EasyOrder: admin email failed to send to ' . implode( ', ', $recipient ) );
}

// ─── Confirmation email to submitter (no pricing) ─────────────────────────

$confirm_rows = '';
$j = 0;

foreach ( $items as $item ) {
    $post_id = (int) ( $item['id'] ?? 0 );
    $post    = $post_id ? get_post( $post_id ) : null;
    $qty     = (int) ( $item['qty'] ?? 0 );

    if ( ! $post || $post->post_status !== 'publish' || $post->post_type !== EO_CPT ) continue;

    $name       = esc_html( $post->post_title );
    $sku        = esc_html( get_post_meta( $post_id, '_eo_sku', true ) ?: '—' );
    $type_terms = get_the_terms( $post_id, EO_STRAIN );
    $strain     = esc_html( ( $type_terms && ! is_wp_error( $type_terms ) ) ? $type_terms[0]->name : '—' );
    $bg         = $j++ % 2 === 0 ? '#ffffff' : '#f9f9f9';

    $confirm_rows .= "<tr style='background:{$bg};'>
        <td data-label='Product' style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;'>{$name}</td>
        <td data-label='SKU' style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;color:#888;'>{$sku}</td>
        <td data-label='Type' style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;'>{$strain}</td>
        <td data-label='Qty' style='padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;text-align:center;'>{$qty}</td>
    </tr>";
}

$confirm_notes = $sender_notes
    ? "<p style='margin:20px 0 0;padding:12px 14px;background:#f5f5f5;border-left:3px solid #999;font-size:14px;'><strong>Notes:</strong> " . nl2br( esc_html( $sender_notes ) ) . "</p>"
    : '';

$confirm_body = "<!DOCTYPE html>
```

<html>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<style>
  body { margin:0; padding:0; background:#f0f0f0; font-family:Arial,sans-serif; }
  .email-outer { width:100%; background:#f0f0f0; padding:20px 0; }
  .email-wrap  { width:94%; max-width:960px; margin:0 auto; background:#ffffff; border-radius:6px; overflow:hidden; }
  .email-header { padding:20px 24px; border-bottom:1px solid #eee; }
  .email-from   { padding:14px 24px; border-bottom:1px solid #eee; font-size:14px; color:#444; }
  .email-body   { padding:16px 24px 20px; }
  .email-footer { padding:12px 24px; background:#f9f9f9; border-top:1px solid #eee; }
  .item-table   { width:100%; border-collapse:collapse; }
  .item-table th { padding:8px 12px; text-align:left; font-size:12px; color:#777; border-bottom:1px solid #ddd; text-transform:uppercase; letter-spacing:.5px; background:#f5f5f5; }
  .item-table td { padding:10px 12px; border-bottom:1px solid #eee; font-size:14px; }
  .item-table td.center { text-align:center; }
  @media only screen and (max-width:520px) {
    .email-wrap { width:96% !important; }
    .item-table thead { display:none; }
    .item-table tr { display:block; border:1px solid #eee; border-radius:4px; margin-bottom:10px; background:#fff; }
    .item-table td { display:block; text-align:left !important; padding:8px 12px; border-bottom:1px solid #f5f5f5; font-size:13px; }
    .item-table td:last-child { border-bottom:none; }
    .item-table td[data-label]:before { content:attr(data-label) ': '; font-weight:700; color:#555; font-size:11px; text-transform:uppercase; letter-spacing:.4px; display:block; margin-bottom:2px; }
  }
</style>
</head>
<body>
<div class='email-outer'>
<div class='email-wrap'>
  <div class='email-header'>
    <p style='margin:0;font-size:13px;color:#999;'>{$site_name}</p>
    <h1 style='margin:4px 0 0;font-size:18px;color:#222;'>Order Request Received</h1>
  </div>
  <div class='email-from'>
    Hi <strong>{$sender_name}</strong>, your order request has been received and is being reviewed.
  </div>
  <div class='email-body'>
    <table class='item-table'>
      <thead>
        <tr>
          <th>Product</th><th>SKU</th><th>Type</th><th style='text-align:center;'>Qty</th>
        </tr>
      </thead>
      <tbody>{$confirm_rows}</tbody>
    </table>
    {$confirm_notes}
  </div>
  <div class='email-footer'>
    <p style='margin:0;font-size:11px;color:#bbb;'>Sent via {$site_name}</p>
  </div>
</div>
</div>
</body>
</html>";

```
$confirm_headers = [ 'Content-Type: text/html; charset=UTF-8' ];

if ( $send_confirmation ) {
    wp_mail( $sender_email, "Your Order Request — {$site_name}", $confirm_body, $confirm_headers );
}

wp_send_json_success();
```

}
