<?php
/**
 * Maintenance Mode
 *
 * Toggle the constant below to show/hide the maintenance page.
 * Logged-in admins always see the real site.
 *
 * @package portfolio-salma
 */

define( 'PORTFOLIO_MAINTENANCE_MODE', true );

if ( PORTFOLIO_MAINTENANCE_MODE ) {
	add_action( 'template_redirect', function() {
		if ( is_user_logged_in() ) {
			return;
		}
		$html = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Maintenance</title></head>';
		$html .= '<body style="margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#171412;font-family:sans-serif;text-align:center;padding:2rem;">';
		$html .= '<div><h1 style="color:#F5F5F0;font-size:2rem;margin-bottom:1rem;">Under Construction</h1>';
		$html .= '<p style="color:rgba(245,245,240,0.6);font-size:1.1rem;">This site is being updated. Check back soon.</p></div>';
		$html .= '</body></html>';
		status_header( 503 );
		header( 'Retry-After: 3600' );
		echo $html;
		exit;
	});
}
