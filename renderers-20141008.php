<?php
 
class theme_skillbox_core_renderer extends theme_bootstrapbase_core_renderer {
 
}
require_once('renderers/grid_renderer.php');
include_once($CFG->dirroot . "/blocks/course_overview/renderer.php");

class theme_skillbox_block_course_overview_renderer extends block_course_overview_renderer {
	public function course_overview($courses, $overviews) {
		global $CFG;
		require_once($CFG->libdir. '/coursecatlib.php');
        $html = '';
        $config = get_config('block_course_overview');
        $ismovingcourse = false;
        $courseordernumber = 0;
        $maxcourses = count($courses);
        $userediting = false;
        // Intialise string/icon etc if user is editing and courses > 1
        if ($this->page->user_is_editing() && (count($courses) > 1)) {
            $userediting = true;
            $this->page->requires->js_init_call('M.block_course_overview.add_handles');

            // Check if course is moving
            $ismovingcourse = optional_param('movecourse', FALSE, PARAM_BOOL);
            $movingcourseid = optional_param('courseid', 0, PARAM_INT);
        }

        // Render first movehere icon.
        if ($ismovingcourse) {
            // Remove movecourse param from url.
            $this->page->ensure_param_not_in_url('movecourse');

            // Show moving course notice, so user knows what is being moved.
            $html .= $this->output->box_start('notice');
            $a = new stdClass();
            $a->fullname = $courses[$movingcourseid]->fullname;
            $a->cancellink = html_writer::link($this->page->url, get_string('cancel'));
            $html .= get_string('movingcourse', 'block_course_overview', $a);
            $html .= $this->output->box_end();

            $moveurl = new moodle_url('/blocks/course_overview/move.php',
                        array('sesskey' => sesskey(), 'moveto' => 0, 'courseid' => $movingcourseid));
            // Create move icon, so it can be used.
            $movetofirsticon = html_writer::empty_tag('img',
                    array('src' => $this->output->pix_url('movehere'),
                        'alt' => get_string('movetofirst', 'block_course_overview', $courses[$movingcourseid]->fullname),
                        'title' => get_string('movehere')));
            $moveurl = html_writer::link($moveurl, $movetofirsticon);
            $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
        }

        foreach ($courses as $key => $course) {
            // If moving course, then don't show course which needs to be moved.
            if ($ismovingcourse && ($course->id == $movingcourseid)) {
                continue;
            }
            $html .= $this->output->box_start('coursebox span6', "course-{$course->id}");
            $html .= html_writer::start_tag('div', array('class' => 'course_title'));
            // If user is editing, then add move icons.
            if ($userediting && !$ismovingcourse) {
                $moveicon = html_writer::empty_tag('img',
                        array('src' => $this->pix_url('t/move')->out(false),
                            'alt' => get_string('movecourse', 'block_course_overview', $course->fullname),
                            'title' => get_string('move')));
                $moveurl = new moodle_url($this->page->url, array('sesskey' => sesskey(), 'movecourse' => 1, 'courseid' => $course->id));
                $moveurl = html_writer::link($moveurl, $moveicon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'move'));

            }

            // No need to pass title through s() here as it will be done automatically by html_writer.
            $attributes = array('title' => $course->fullname);
            if ($course->id > 0) {
                if (empty($course->visible)) {
                    $attributes['class'] = 'dimmed';
                }
                $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                $coursefullname = format_string(get_course_display_name_for_list($course), true, $course->id);
                $link = html_writer::link($courseurl, $coursefullname, $attributes);
                $html .= $this->output->heading($link, 2, 'title');
            } else {
                $html .= $this->output->heading(html_writer::link(
                    new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                    format_string($course->shortname, true), $attributes) . ' (' . format_string($course->hostname) . ')', 2, 'title');
            }
            $html .= $this->output->box('', 'flush');
            $html .= html_writer::end_tag('div');

            if (!empty($config->showchildren) && ($course->id > 0)) {
                // List children here.
                if ($children = block_course_overview_get_child_shortnames($course->id)) {
                    $html .= html_writer::tag('span', $children, array('class' => 'coursechildren'));
                }
            }
			// show summary
			//if (!empty($course->summary)){
				$coursesummary = format_string($course->summary);
				$html .= html_writer::tag('p', $course->id, array('class'=>'summary'));
			//}
			
			// display course overview files
			$contentimages = $contentfiles = '';
//			get_course_overviewfiles($course-id);
//			foreach ($course->get_course_overviewfiles() as $file) {
//				$isimage = $file->is_valid_image();
//				$url = file_encode_url("$CFG->wwwroot/pluginfile.php",
//						'/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
//						$file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
//				if ($isimage) {
//					$contentimages .= html_writer::tag('div',
//							html_writer::empty_tag('img', array('src' => $url)),
//							array('class' => 'courseimage'));
//				} else {
//					$image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
//					$filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
//							html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
//					$contentfiles .= html_writer::tag('span',
//							html_writer::link($url, $filename),
//							array('class' => 'coursefile fp-filename-icon'));
//				}
//			}
			$html .= $contentimages. $contentfiles;
			
			
            // If user is moving courses, then down't show overview.
            if (isset($overviews[$course->id]) && !$ismovingcourse) {
                $html .= $this->activity_display($course->id, $overviews[$course->id]);
            }

            $html .= $this->output->box('', 'flush');
            $html .= $this->output->box_end();
            $courseordernumber++;
            if ($ismovingcourse) {
                $moveurl = new moodle_url('/blocks/course_overview/move.php',
                            array('sesskey' => sesskey(), 'moveto' => $courseordernumber, 'courseid' => $movingcourseid));
                $a = new stdClass();
                $a->movingcoursename = $courses[$movingcourseid]->fullname;
                $a->currentcoursename = $course->fullname;
                $movehereicon = html_writer::empty_tag('img',
                        array('src' => $this->output->pix_url('movehere'),
                            'alt' => get_string('moveafterhere', 'block_course_overview', $a),
                            'title' => get_string('movehere')));
                $moveurl = html_writer::link($moveurl, $movehereicon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
            }
        }
        // Wrap course list in a div and return.
        return html_writer::tag('div', $html, array('class' => 'course_list'));
    }
	
}
include_once($CFG->dirroot . "/course/format/grid/renderer.php");

class theme_skillbox_format_grid_renderer extends format_grid_renderer {
    private $topic0_at_top; // Boolean to state if section zero is at the top (true) or in the grid (false).
    private $courseformat; // Our course format object as defined in lib.php.
    private $settings; // Settings array.
    private $shadeboxshownarray = array(); // Value of 1 = not shown, value of 2 = shown - to reduce ambiguity in JS.

    /**
     * Constructor method, calls the parent constructor - MDL-21097
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->courseformat = course_get_format($page->course);
        $this->settings = $this->courseformat->get_settings();

        /* Since format_grid_renderer::section_edit_controls() only displays the 'Set current section' control when editing
           mode is on we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any
           other managing capability. */
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'gtopics', 'id' => 'gtopics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('sectionname', 'format_grid');
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param array $mods
     * @param array $modnames
     * @param array $modnamesused
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;

        $summarystatus = $this->courseformat->get_summary_visibility($course->id);
        $context = context_course::instance($course->id);
        $editing = $PAGE->user_is_editing();
        $hascapvishidsect = has_capability('moodle/course:viewhiddensections', $context);

        if ($editing) {
            $streditsummary = get_string('editsummary');
            $urlpicedit = $this->output->pix_url('t/edit');
        } else {
            $urlpicedit = false;
            $streditsummary = '';
        }

        echo html_writer::start_tag('div', array('id' => 'gridmiddle-column'));
        echo $this->output->skip_link_target();

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        // Start at 1 to skip the summary block or include the summary block if it's in the grid display.
        $this->topic0_at_top = $summarystatus->showsummary == 1;
        if ($this->topic0_at_top) {
            $this->topic0_at_top = $this->make_block_topic0($course, $sections, $modinfo, $editing, $urlpicedit,
                    $streditsummary, false);
            // For the purpose of the grid shade box shown array topic 0 is not shown.
            $this->shadeboxshownarray[0] = 1;
        }
        echo html_writer::start_tag('div', array('id' => 'gridiconcontainer', 'class'=> 'block', 'role' => 'navigation',
            'aria-label' => get_string('gridimagecontainer', 'format_grid')));
        echo html_writer::start_tag('ul', array('class' => 'gridicons content'));
        // Print all of the imaege containers.
        $this->make_block_icon_topics($context->id, $modinfo, $course, $editing, $hascapvishidsect, $urlpicedit);
        echo html_writer::end_tag('ul');
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('id' => 'gridshadebox'));
        echo html_writer::tag('div', '', array('id' => 'gridshadebox_overlay', 'style' => 'display:none;'));
        echo html_writer::start_tag('div', array('id' => 'gridshadebox_content', 'class' => 'hide_content',
            'role' => 'region',
            'aria-label' => get_string('shadeboxcontent', 'format_grid')));

        echo html_writer::tag('img', '', array('id' => 'gridshadebox_close', 'style' => 'display:none;',
            'src' => $this->output->pix_url('close', 'format_grid'),
            'role' => 'link',
            'aria-label' => get_string('closeshadebox', 'format_grid')));
        echo html_writer::tag('img', '', array('id' => 'gridshadebox_left', 'class' => 'gridshadebox_arrow',
            'style' => 'display:none;',
            'src' => $this->output->pix_url('arrow_l', 'format_grid'),
            'role' => 'link',
            'aria-label' => get_string('previoussection', 'format_grid')));
        echo html_writer::tag('img', '', array('id' => 'gridshadebox_right', 'class' => 'gridshadebox_arrow',
            'style' => 'display:none;',
            'src' => $this->output->pix_url('arrow_r', 'format_grid'),
            'role' => 'link',
            'aria-label' => get_string('nextsection', 'format_grid')));
        echo $this->start_section_list();
        // If currently moving a file then show the current clipboard.
        $this->make_block_show_clipboard_if_file_moving($course);

        // Print Section 0 with general activities.
        if (!$this->topic0_at_top) {
            $this->make_block_topic0($course, $sections, $modinfo, $editing, $urlpicedit, $streditsummary, false);
        }

        // Now all the normal modules by topic.
        // Everything below uses "section" terminology - each "section" is a topic/module.
        $this->make_block_topics($course, $sections, $modinfo, $editing, $hascapvishidsect, $streditsummary,
                $urlpicedit, false);

        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::tag('div', '&nbsp;', array('class' => 'clearer'));
        echo html_writer::end_tag('div');

        $sectionredirect = null;
        if ($course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
            // Get the redirect URL prefix for keyboard control with the 'Show one section per page' layout.
            $sectionredirect = $this->courseformat->get_view_url(null)->out(true);
        }

        // Initialise the shade box functionality:...
        $PAGE->requires->js_init_call('M.format_grid.init', array(
            $PAGE->user_is_editing(),
            $sectionredirect,
            $course->numsections,
            json_encode($this->shadeboxshownarray)));
        // Initialise the key control functionality...
        $PAGE->requires->yui_module('moodle-format_grid-gridkeys', 'M.format_grid.gridkeys.init', null, null, true);
    }

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if (has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $strmarkedthissection = get_string('markedthissection', 'format_grid');
                $controls[] = html_writer::link($url,
                                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
                                        'class' => 'icon ', 'alt' => $strmarkedthissection)),
                                    array('title' => $strmarkedthissection, 'class' => 'editing_highlight'));
            } else {
                $strmarkthissection = get_string('markthissection', 'format_grid');
                $url->param('marker', $section->section);
                $controls[] = html_writer::link($url,
                                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
                                    'class' => 'icon', 'alt' => $strmarkthissection)),
                                array('title' => $strmarkthissection, 'class' => 'editing_highlight'));
            }
        }

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
    }

    // Grid format specific code.
    /**
     * Makes section zero.
     */
    private function make_block_topic0($course, $sections, $modinfo, $editing, $urlpicedit, $streditsummary,
            $onsectionpage) {
        $section = 0;
        if (!array_key_exists($section, $sections)) {
            return false;
        }

        $thissection = $modinfo->get_section_info($section);
        if (!is_object($thissection)) {
            return false;
        }

        if ($this->topic0_at_top) {
            echo html_writer::start_tag('ul', array('class' => 'gtopics-0'));
        }

        $sectionname = get_section_name($course, $thissection);
        echo html_writer::start_tag('li', array(
            'id' => 'section-0',
            'class' => 'section main' . ($this->topic0_at_top ? '' : ' grid_section hide_section'),
            'role' => 'region',
            'aria-label' => $sectionname)
        );

        echo html_writer::tag('div', '&nbsp;', array('class' => 'right side'));

        echo html_writer::start_tag('div', array('class' => 'content'));

        if (!$onsectionpage) {
            echo $this->output->heading($sectionname, 3, 'sectionname');
        }

        echo html_writer::start_tag('div', array('class' => 'summary'));

        echo $this->format_summary_text($thissection);

        if ($editing) {
            $link = html_writer::link(
                            new moodle_url('editsection.php', array('id' => $thissection->id)),
                                html_writer::empty_tag('img', array('src' => $urlpicedit,
                                                                     'alt' => $streditsummary,
                                                                     'class' => 'iconsmall edit')),
                                                        array('title' => $streditsummary));
            echo $this->topic0_at_top ? html_writer::tag('p', $link) : $link;
        }
        echo html_writer::end_tag('div');

        echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);

        if ($editing) {
            echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section, 0, 0);

            if ($this->topic0_at_top) {
                $strhidesummary = get_string('hide_summary', 'format_grid');
                $strhidesummaryalt = get_string('hide_summary_alt', 'format_grid');

                echo html_writer::link(
                        $this->courseformat->grid_moodle_url('mod_summary.php', array(
                            'sesskey' => sesskey(),
                            'course' => $course->id,
                            'showsummary' => 0)), html_writer::empty_tag('img', array(
                            'src' => $this->output->pix_url('into_grid', 'format_grid'),
                            'alt' => $strhidesummaryalt)) . '&nbsp;' . $strhidesummary, array('title' => $strhidesummaryalt));
            }
        }
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('li');

        if ($this->topic0_at_top) {
            echo html_writer::end_tag('ul');
        }
        return true;
    }

    /**
     * Makes the grid image containers.
     */
    private function make_block_icon_topics($contextid, $modinfo, $course, $editing, $hascapvishidsect,
            $urlpicedit) {
        global $USER, $CFG;

        if ($this->settings['newactivity'] == 2) {
            $currentlanguage = current_language();
            if (!file_exists("$CFG->dirroot/course/format/grid/pix/new_activity_" . $currentlanguage . ".png")) {
                $currentlanguage = 'en';
            }
            $url_pic_new_activity = $this->output->pix_url('new_activity_' . $currentlanguage, 'format_grid');

            // Get all the section information about which items should be marked with the NEW picture.
            $sectionupdated = $this->new_activity($course);
        }

        if ($editing) {
            $streditimage = get_string('editimage', 'format_grid');
            $streditimagealt = get_string('editimage_alt', 'format_grid');
        }

        // Get the section images for the course.
        $sectionimages = $this->courseformat->get_images($course->id);

        // CONTRIB-4099:...
        $gridimagepath = $this->courseformat->get_image_path();

        // Start at 1 to skip the summary block or include the summary block if it's in the grid display.
        for ($section = $this->topic0_at_top ? 1 : 0; $section <= $course->numsections; $section++) {
            $thissection = $modinfo->get_section_info($section);

            // Check if section is visible to user.
            $showsection = $hascapvishidsect || ($thissection->visible && ($thissection->available ||
                    $thissection->showavailability || !$course->hiddensections));

            if ($showsection) {
                // We now know the value for the grid shade box shown array.
                $this->shadeboxshownarray[$section] = 2;

                $sectionname = $this->courseformat->get_section_name($thissection);

                /* Roles info on based on: http://www.w3.org/TR/wai-aria/roles.
                   Looked into the 'grid' role but that requires 'row' before 'gridcell' and there are none as the grid
                   is responsive, so as the container is a 'navigation' then need to look into converting the containing
                   'div' to a 'nav' tag (www.w3.org/TR/2010/WD-html5-20100624/sections.html#the-nav-element) when I'm
                   that all browsers support it against the browser requirements of Moodle. */
                $liattributes = array(
					'class' => 'coursebox span6',
                    'role' => 'region',
                    'aria-label' => $sectionname
                );
                if ($this->courseformat->is_section_current($section)) {
                    $liattributes['class'] = 'currenticon';
                }
                echo html_writer::start_tag('li', $liattributes);

                // Ensure the record exists.
                if  (($sectionimages === false) || (!array_key_exists($thissection->id, $sectionimages))) {
                    // get_image has 'repair' functionality for when there are issues with the data.
                    $sectionimage = $this->courseformat->get_image($course->id, $thissection->id);
                } else {
                    $sectionimage = $sectionimages[$thissection->id];
                }

                // If the image is set then check that displayedimageindex is greater than 0 otherwise create the displayed image.
                // This is a catch-all for existing courses.
                if (isset($sectionimage->image) && ($sectionimage->displayedimageindex < 1)) {
                    // Set up the displayed image:...
                    $sectionimage->newimage = $sectionimage->image;
                    $sectionimage = $this->courseformat->setup_displayed_image($sectionimage, $contextid,
                        $this->settings);
                    if (format_grid::is_developer_debug()) {
                        error_log('make_block_icon_topics: Updated displayed image for section ' . $thissection->id . ' to ' .
                                $sectionimage->newimage . ' and index ' . $sectionimage->displayedimageindex);
                    }
                }

                if ($course->coursedisplay != COURSE_DISPLAY_MULTIPAGE) {
                    echo html_writer::start_tag('a', array(
                        'href' => '#section-' . $thissection->section,
                        'id' => 'gridsection-' . $thissection->section,
                        'class' => 'gridicon_link',
                        'role' => 'link',
                        'aria-label' => $sectionname));

                    echo html_writer::tag('h2', $sectionname, array('class' => 'title icon_content'));

                    if (($this->settings['newactivity'] == 2) && (isset($sectionupdated[$thissection->id]))) {
                        // The section has been updated since the user last visited this course, add NEW label.
                        echo html_writer::empty_tag('img', array(
                            'class' => 'new_activity',
                            'src' => $url_pic_new_activity,
                            'alt' => ''));
                    }

                    echo html_writer::start_tag('div', array('class' => 'image_holder'));

                    $showimg = false;
                    if (is_object($sectionimage) && ($sectionimage->displayedimageindex > 0)) {
                        $imgurl = moodle_url::make_pluginfile_url(
                            $contextid, 'course', 'section', $thissection->id, $gridimagepath,
                            $sectionimage->displayedimageindex . '_' . $sectionimage->image);
                        $showimg = true;
                    } else if ($section == 0) {
                        $imgurl = $this->output->pix_url('info', 'format_grid');
                        $showimg = true;
                    }
                    if ($showimg) {
                        echo html_writer::empty_tag('img', array(
                            'src' => $imgurl,
                            'alt' => $sectionname,
                            'role' => 'img',
                            'aria-label' => $sectionname));
                    }

                    echo html_writer::end_tag('div');
                    echo html_writer::end_tag('a');

                    if ($editing) {
                        echo html_writer::link(
                                $this->courseformat->grid_moodle_url('editimage.php', array(
                                    'sectionid' => $thissection->id,
                                    'contextid' => $contextid,
                                    'userid' => $USER->id,
                                    'role' => 'link',
                                    'aria-label' => $streditimagealt)), html_writer::empty_tag('img', array(
                                    'src' => $urlpicedit,
                                    'alt' => $streditimagealt,
                                    'role' => 'img',
                                    'aria-label' => $streditimagealt)) . '&nbsp;' . $streditimage,
                                array('title' => $streditimagealt));

                        if ($section == 0) {
                            $strdisplaysummary = get_string('display_summary', 'format_grid');
                            $strdisplaysummaryalt = get_string('display_summary_alt', 'format_grid');

                            echo html_writer::empty_tag('br') . html_writer::link(
                                    $this->courseformat->grid_moodle_url('mod_summary.php', array(
                                        'sesskey' => sesskey(),
                                        'course' => $course->id,
                                        'showsummary' => 1,
                                        'role' => 'link',
                                        'aria-label' => $strdisplaysummaryalt)), html_writer::empty_tag('img', array(
                                        'src' => $this->output->pix_url('out_of_grid', 'format_grid'),
                                        'alt' => $strdisplaysummaryalt,
                                        'role' => 'img',
                                        'aria-label' => $strdisplaysummaryalt)) . '&nbsp;' . $strdisplaysummary, array('title' => $strdisplaysummaryalt));
                        }
                    }
                    echo html_writer::end_tag('li');
                } else {
                    $title = html_writer::tag('h2', $sectionname, array('class' => 'title icon_content'));

                    if (($this->settings['newactivity'] == 2) && (isset($sectionupdated[$thissection->id]))) {
                        $title .= html_writer::empty_tag('img', array(
                                    'class' => 'new_activity',
                                    'src' => $url_pic_new_activity,
                                    'alt' => ''));
                    }

                    $title .= html_writer::start_tag('div', array('class' => 'image_holder'));

                    $showimg = false;
                    if (is_object($sectionimage) && ($sectionimage->displayedimageindex > 0)) {
                        $imgurl = moodle_url::make_pluginfile_url(
                            $contextid, 'course', 'section', $thissection->id, $gridimagepath,
                            $sectionimage->displayedimageindex . '_' . $sectionimage->image);
                        $showimg = true;
                    } else if ($section == 0) {
                        $imgurl = $this->output->pix_url('info', 'format_grid');
                        $showimg = true;
                    }
                    if ($showimg) {
                        $title .= html_writer::empty_tag('img', array(
                                    'src' => $imgurl,
                                    'alt' => $sectionname,
                                    'role' => 'img',
                                    'aria-label' => $sectionname));
                    }

                    $title .= html_writer::end_tag('div');

                    $url = course_get_url($course, $thissection->section);
                    if ($url) {
                        $title = html_writer::link($url, $title, array(
                            'id' => 'gridsection-' . $thissection->section,
                            'role' => 'link',
                            'aria-label' => $sectionname));
                    }
                    echo $title;

                    if ($editing) {
                        echo html_writer::link(
                                $this->courseformat->grid_moodle_url('editimage.php', array(
                                    'sectionid' => $thissection->id,
                                    'contextid' => $contextid,
                                    'userid' => $USER->id,
                                    'role' => 'link',
                                    'aria-label' => $streditimagealt)), html_writer::empty_tag('img', array(
                                    'src' => $urlpicedit,
                                    'alt' => $streditimagealt,
                                    'role' => 'img',
                                    'aria-label' => $streditimagealt)) . '&nbsp;' . $streditimage,
                                array('title' => $streditimagealt));

                        if ($section == 0) {
                            $strdisplaysummary = get_string('display_summary', 'format_grid');
                            $strdisplaysummaryalt = get_string('display_summary_alt', 'format_grid');

                            echo html_writer::empty_tag('br') . html_writer::link(
                                    $this->courseformat->grid_moodle_url('mod_summary.php', array(
                                        'sesskey' => sesskey(),
                                        'course' => $course->id,
                                        'showsummary' => 1,
                                        'role' => 'link',
                                        'aria-label' => $strdisplaysummaryalt)), html_writer::empty_tag('img', array(
                                        'src' => $this->output->pix_url('out_of_grid', 'format_grid'),
                                        'alt' => $strdisplaysummaryalt,
                                        'role' => 'img',
                                        'aria-label' => $strdisplaysummaryalt)) . '&nbsp;' . $strdisplaysummary,
                                    array('title' => $strdisplaysummaryalt));
                        }
                    }
                    echo html_writer::end_tag('li');
                }
            } else {
                // We now know the value for the grid shade box shown array.
                $this->shadeboxshownarray[$section] = 1;
            }
        }
    }

    /**
     * If currently moving a file then show the current clipboard.
     */
    private function make_block_show_clipboard_if_file_moving($course) {
        global $USER;

        if (is_object($course) && ismoving($course->id)) {
            $strcancel = get_string('cancel');

            $stractivityclipboard = clean_param(format_string(
                            get_string('activityclipboard', '', $USER->activitycopyname)), PARAM_NOTAGS);
            $stractivityclipboard .= '&nbsp;&nbsp;('
                    . html_writer::link(new moodle_url('/mod.php', array(
                        'cancelcopy' => 'true',
                        'sesskey' => sesskey())), $strcancel);

            echo html_writer::tag('li', $stractivityclipboard, array('class' => 'clipboard'));
        }
    }

    /**
     * Makes the list of sections to show.
     */
    private function make_block_topics($course, $sections, $modinfo, $editing, $hascapvishidsect, $streditsummary,
            $urlpicedit, $onsectionpage) {
        $context = context_course::instance($course->id);
        unset($sections[0]);
        for ($section = 1; $section <= $course->numsections; $section++) {
            $thissection = $modinfo->get_section_info($section);

            if (!$hascapvishidsect && !$thissection->visible && $course->hiddensections) {
                unset($sections[$section]);
                continue;
            }

            $sectionstyle = 'section main';
            if (!$thissection->visible) {
                $sectionstyle .= ' hidden';
            }
            if ($this->courseformat->is_section_current($section)) {
                $sectionstyle .= ' current';
            }
            $sectionstyle .= ' grid_section hide_section';

            $sectionname = get_section_name($course, $thissection);
            echo html_writer::start_tag('li', array(
                'id' => 'section-' . $section,
                'class' => $sectionstyle,
                'role' => 'region',
                'aria-label' => $sectionname)
            );

            if ($editing) {
                // Note, 'left side' is BEFORE content.
                $leftcontent = $this->section_left_content($thissection, $course, $onsectionpage);
                echo html_writer::tag('div', $leftcontent, array('class' => 'left side'));
                // Note, 'right side' is BEFORE content.
                $rightcontent = $this->section_right_content($thissection, $course, $onsectionpage);
                echo html_writer::tag('div', $rightcontent, array('class' => 'right side'));
            }

            echo html_writer::start_tag('div', array('class' => 'content'));
            if ($hascapvishidsect || ($thissection->visible && $thissection->available)) {
                // If visible.
                echo $this->output->heading($sectionname, 3, 'sectionname');

                echo html_writer::start_tag('div', array('class' => 'summary'));

                echo $this->format_summary_text($thissection);

                if ($editing) {
                    echo html_writer::link(
                            new moodle_url('editsection.php', array('id' => $thissection->id)),
                            html_writer::empty_tag('img', array('src' => $urlpicedit, 'alt' => $streditsummary,
                                'class' => 'iconsmall edit')), array('title' => $streditsummary));
                }
                echo html_writer::end_tag('div');

                echo $this->section_availability_message($thissection,has_capability('moodle/course:viewhiddensections',
                        $context));

                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section, 0);
            } else {
                echo html_writer::tag('h2', $this->get_title($thissection));
                echo html_writer::tag('p', get_string('hidden_topic', 'format_grid'));

                echo $this->section_availability_message($thissection, has_capability('moodle/course:viewhiddensections',
                        $context));
            }

            echo html_writer::end_tag('div');
            echo html_writer::end_tag('li');

            unset($sections[$section]);
        }

        if ($editing) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    // This is not stealth section or it is empty.
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Increase number of sections.
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php', array('courseid' => $course->id,
                'increase' => true,
                'sesskey' => sesskey()));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon . get_accesshide($straddsection), array('class' => 'increase-sections'));

            if ($course->numsections > 0) {
                // Reduce number of sections sections.
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php', array('courseid' => $course->id,
                    'increase' => false,
                    'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon . get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }

            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }
    }

    /**
     * Attempts to return a 40 character title for the section image container.
     * If section names are set, they are used. Otherwise it scans 
     * the summary for what looks like the first line.
     */
    private function get_title($section) {
        $title = is_object($section) && isset($section->name) &&
                is_string($section->name) ? trim($section->name) : '';

        if (!empty($title)) {
            // Apply filters and clean tags.
            $title = trim(format_string($section->name, true));
        }

        if (empty($title)) {
            $title = trim(format_text($section->summary));

            // Finds first header content. If it is not found, then try to find the first paragraph.
            foreach (array('h[1-6]', 'p') as $tag) {
                if (preg_match('#<(' . $tag . ')\b[^>]*>(?P<text>.*?)</\1>#si', $title, $m)) {
                    if (!$this->is_empty_text($m['text'])) {
                        $title = $m['text'];
                        break;
                    }
                }
            }
            $title = trim(clean_param($title, PARAM_NOTAGS));
        }

        if (strlen($title) > 40) {
            $title = $this->text_limit($title, 40);
        }

        return $title;
    }

    /**
     * States if the text is empty.
     * @param type $text The text to test.
     * @return boolean Yes(true) or No(false).
     */
    public function is_empty_text($text) {
        return empty($text) ||
                preg_match('/^(?:\s|&nbsp;)*$/si', htmlentities($text, 0 /* ENT_HTML401 */, 'UTF-8', true));
    }

    /**
     * Cuts long texts up to certain length without breaking words.
     */
    private function text_limit($text, $length, $replacer = '...') {
        if (strlen($text) > $length) {
            $text = wordwrap($text, $length, "\n", true);
            $pos = strpos($text, "\n");
            if ($pos === false) {
                $pos = $length;
            }
            $text = trim(substr($text, 0, $pos)) . $replacer;
        }
        return $text;
    }

    /**
     * Checks whether there has been new activity.
     */
    private function new_activity($course) {
        global $CFG, $USER, $DB;

        $sectionsedited = array();
        if (isset($USER->lastcourseaccess[$course->id])) {
            $course->lastaccess = $USER->lastcourseaccess[$course->id];
        } else {
            $course->lastaccess = 0;
        }

        $sql = "SELECT id, section FROM {$CFG->prefix}course_modules " .
                "WHERE course = :courseid AND added > :lastaccess";

        $params = array(
            'courseid' => $course->id,
            'lastaccess' => $course->lastaccess);

        $activity = $DB->get_records_sql($sql, $params);
        foreach ($activity as $record) {
            $sectionsedited[$record->section] = true;
        }

        return $sectionsedited;
    }
}