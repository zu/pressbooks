<?php
/**
 * @author  Pressbooks <code@pressbooks.com>
 * @license GPLv2 (or any later version)
 */


/**
 * Donham features we inject ourselves, (not user options)
 *
 * @param $css
 *
 * @return string
 */
function donham_theme_pdf_css_override( $css ) {

	// Translate "Part" to whatever language this book is in
	$css .= '#toc .part a::before { content: "' . __( 'part', 'pressbooks' ) . ' "counter(part) ". "; }' . "\n";
	$css .= 'div.part-title-wrap > h3.part-number:before { content: "' . __( 'part', 'pressbooks' ) . ' "; }' . "\n";

	return $css;
}
add_filter( 'pb_pdf_css_override', 'donham_theme_pdf_css_override' );


/**
 * Donham features we inject ourselves, (not user options)
 *
 * @param $css
 *
 * @return string
 */
function donham_theme_ebook_css_override( $css ) {

	// Translate "Part" to whatever language this book is in
	$css .= 'div.part-title-wrap > h3.part-number:before { content: "' . __( 'part', 'pressbooks' ) . ' "; }' . "\n";

	return $css;
}
add_filter( 'pb_epub_css_override', 'donham_theme_ebook_css_override' );