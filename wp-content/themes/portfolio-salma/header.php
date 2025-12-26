<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package portfolio-salma
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'portfolio-salma' ); ?></a>

	<header id="masthead" class="site-header">
		<nav id="site-navigation" class="main-navigation">
			<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
				<span class="menu-toggle-icon"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'portfolio-salma' ); ?></span>
			</button>

			<div class="nav-left">
				<ul id="primary-menu-left" class="nav-menu nav-menu-left">
					<li><a href="#about">ABOUT</a></li>
					<li><a href="#work">WORK</a></li>
				</ul>
			</div>

			<div class="nav-logo">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-link" rel="home">
						<img class="site-logo-icon" src="<?php echo esc_url( get_template_directory_uri() . '/images/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
					</a>
				<?php endif; ?>
			</div>

			<div class="nav-right">
				<ul id="primary-menu-right" class="nav-menu nav-menu-right">
					<li><a href="mailto:salma@salmalopez.ch">EMAIL</a></li>
					<li><a href="https://www.instagram.com/greenlemontree__/" target="_blank" rel="noopener noreferrer">INSTAGRAM</a></li>
				</ul>
			</div>
		</nav><!-- #site-navigation -->
	</header><!-- #masthead -->
