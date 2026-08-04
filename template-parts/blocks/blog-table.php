<?php
/**
 * Статья: таблица (layout table).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = tolstenko_block_attributes();
$defaults = tolstenko_blog_content_block_defaults( 'blog_table' );

$use_header = array_key_exists( 'block_blog_table_use_header', $attrs )
	? ! empty( $attrs['block_blog_table_use_header'] )
	: ! empty( $defaults['use_header'] );

$header = isset( $attrs['block_blog_table_header'] ) && is_array( $attrs['block_blog_table_header'] )
	? $attrs['block_blog_table_header']
	: array();
$rows = isset( $attrs['block_blog_table_rows'] ) && is_array( $attrs['block_blog_table_rows'] )
	? $attrs['block_blog_table_rows']
	: array();

$header_cells = array();
foreach ( $header as $cell ) {
	$header_cells[] = is_array( $cell ) ? (string) ( $cell['c'] ?? $cell['text'] ?? '' ) : (string) $cell;
}

$body_rows = array();
foreach ( $rows as $row ) {
	$cells_raw = is_array( $row ) ? ( $row['cells'] ?? $row ) : array();
	if ( ! is_array( $cells_raw ) ) {
		continue;
	}
	$cells = array();
	foreach ( $cells_raw as $cell ) {
		$cells[] = is_array( $cell ) ? (string) ( $cell['c'] ?? $cell['text'] ?? '' ) : (string) $cell;
	}
	if ( implode( '', $cells ) === '' ) {
		continue;
	}
	$body_rows[] = $cells;
}

if ( ! $body_rows && ! array_filter( $header_cells ) ) {
	$header = is_array( $defaults['header'] ?? null ) ? $defaults['header'] : array();
	$rows   = is_array( $defaults['rows'] ?? null ) ? $defaults['rows'] : array();
	$header_cells = array();
	foreach ( $header as $cell ) {
		$header_cells[] = is_array( $cell ) ? (string) ( $cell['c'] ?? $cell['text'] ?? '' ) : (string) $cell;
	}
	$body_rows = array();
	foreach ( $rows as $row ) {
		$cells_raw = is_array( $row ) ? ( $row['cells'] ?? $row ) : array();
		if ( ! is_array( $cells_raw ) ) {
			continue;
		}
		$cells = array();
		foreach ( $cells_raw as $cell ) {
			$cells[] = is_array( $cell ) ? (string) ( $cell['c'] ?? $cell['text'] ?? '' ) : (string) $cell;
		}
		if ( implode( '', $cells ) === '' ) {
			continue;
		}
		$body_rows[] = $cells;
	}
}

if ( ! $body_rows && ! array_filter( $header_cells ) ) {
	return;
}
?>
<div class="single-blog__content-block single-blog__content-block--table br-30">
	<table class="single-blog__content-table" align="left">
		<?php if ( $use_header && array_filter( $header_cells ) ) : ?>
			<thead class="single-blog__content-table-head" align="left">
				<tr>
					<?php foreach ( $header_cells as $cell ) : ?>
						<th class="line-caps-bold-13-15"><?php echo esc_html( $cell ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
		<?php endif; ?>
		<tbody class="single-blog__content-table-body">
			<?php foreach ( $body_rows as $cells ) : ?>
				<tr>
					<?php foreach ( $cells as $cell ) : ?>
						<td class="line-caps-bold-13-15"><?php echo esc_html( $cell ); ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php
