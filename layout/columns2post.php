﻿<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The two column layout.
 *
 * @package   theme_skillbox
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the HTML for the settings bits.
$html = theme_skillbox_get_html_for_settings($OUTPUT, $PAGE);

$left = (!right_to_left());  // To know if to add 'pull-right' and 'desktop-first-column' classes in the layout for LTR.
echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes('two-column'); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>



<div id="page" class="container-fluid">
	<header role="banner" class="navbar navbar-fixed-top<?php echo $html->navbarclass ?> moodle-has-zindex">
		<nav role="navigation" class="site-nav">
			<div class="container-fluid">

				<div class="nav pull-right">
					
					<ul class="nav pull-right">
						<li><?php echo $OUTPUT->user_menu(); ?></li>
						
					</ul>
				</div>
			</div>
		</nav>
	</header>
	<div id="header-img" class="">
		<!--<img src="<?php //echo $OUTPUT->pix_url('header-beeld-2', 'theme'); ?>" style="max-width:100%;" alt="" />-->
		<img src="<?php echo $OUTPUT->pix_url('header-beeld-'.mt_rand(1,10), 'theme'); ?>" style="max-width:100%;" alt="" />
		<a href="/skillbox/?redirect=0"><div id="logo">
			
		</div></a>
	</div>



    <div id="page-content" class="row-fluid">
        <section id="region-main" class="span10">
			<nav class="row top-nav">
				<?php echo $OUTPUT->custom_menu(); ?>
			</nav>
			
			
			<header id="page-header" class="clearfix">
				
				<div id="page-navbar" class="clearfix">
					
					<nav class="breadcrumb-nav"> <?php echo $OUTPUT->navbar(); ?></nav>
					
					<div class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></div>
				</div>
				<div id="breadcrumb-back" class="btn-primary btn-smaller" onClick="window.history.back()" style="">◄ <?php echo get_string('back', 'core' ); ?></div>
				<?php echo $html->heading; ?>
				<div id="course-header">
					<?php echo $OUTPUT->course_header(); ?>
				</div>
			</header>

            <?php
            echo $OUTPUT->course_content_header();
            echo $OUTPUT->main_content();
            echo $OUTPUT->course_content_footer();
            ?>
        </section>
        <?php
        $classextra = '';
        if ($left) {
            $classextra = ' desktop-first-column';
        }
        echo $OUTPUT->blocks('side-post', 'span2 pull-right'.$classextra);
        ?>
    </div>

    <footer id="page-footer">
        <div id="course-footer"><?php echo $OUTPUT->course_footer(); ?></div>
        <p class="helplink"><?php echo $OUTPUT->page_doc_link(); ?></p>
        <?php
        echo $html->footnote;
        echo $OUTPUT->login_info();
        //echo $OUTPUT->home_link();
        echo $OUTPUT->standard_footer_html();
        ?>
    </footer>

    <?php echo $OUTPUT->standard_end_of_body_html() ?>

</div>
</body>
</html>
