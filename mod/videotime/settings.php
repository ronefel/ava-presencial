<?php
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
 * Plugin administration pages are defined here.
 *
 * @package     mod_videotime
 * @category    admin
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/videotime/lib.php');

$ADMIN->add('modsettings', new admin_category('modvideotimefolder', new lang_string('pluginname', 'videotime'), $module->is_enabled() === false));

$settings = new admin_settingpage($section, get_string('settings', 'videotime'), 'moodle/site:config', $module->is_enabled() === false);

if ($ADMIN->fulltree) {

    if (videotime_has_pro() && videotime_has_repository()) {
        $settings->add(new admin_setting_configtext('videotime/client_id', get_string('client_id', 'videotime'),
            get_string('client_id_help', 'videotime'), '', PARAM_TEXT));

        $settings->add(new admin_setting_configtext('videotime/client_secret', get_string('client_secret', 'videotime'),
            get_string('client_secret_help', 'videotime'), '', PARAM_TEXT));

        $settings->add(new admin_setting_configcheckbox('videotime/store_pictures', get_string('store_pictures', 'videotime'),
            get_string('store_pictures_help', 'videotime'), 1));
    }

    if (!videotime_has_pro()) {
        $settings->add(new admin_setting_heading('option_responsive', get_string('default', 'videotime') . ' ' .
            get_string('option_responsive', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/responsive', get_string('option_responsive', 'videotime'),
            get_string('option_responsive_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/responsive_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_height', get_string('default', 'videotime') . ' ' .
            get_string('option_height', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/height', get_string('option_height', 'videotime'),
            get_string('option_height_help', 'videotime'), null, PARAM_INT));
        $settings->add(new admin_setting_configcheckbox('videotime/height_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_width', get_string('default') . ' ' .
            get_string('option_width', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/width', get_string('option_width', 'videotime'),
            get_string('option_width_help', 'videotime'), null, PARAM_INT));
        $settings->add(new admin_setting_configcheckbox('videotime/width_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_maxheight', get_string('default') . ' ' .
            get_string('option_maxheight', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/maxheight', get_string('option_maxheight', 'videotime'),
            get_string('option_maxheight_help', 'videotime'), null, PARAM_INT));
        $settings->add(new admin_setting_configcheckbox('videotime/maxheight_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_maxwidth', get_string('default') . ' ' .
            get_string('option_maxwidth', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/maxwidth', get_string('option_maxwidth', 'videotime'),
            get_string('option_maxwidth_help', 'videotime'), null, PARAM_INT));
        $settings->add(new admin_setting_configcheckbox('videotime/maxwidth_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_autoplay', get_string('default') . ' ' .
            get_string('option_autoplay', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/autoplay', get_string('option_autoplay', 'videotime'),
            get_string('option_autoplay_help', 'videotime'), '0'));
        $settings->add(new admin_setting_configcheckbox('videotime/autoplay_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_byline', get_string('default') . ' ' .
            get_string('option_byline', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/byline', get_string('option_byline', 'videotime'),
            get_string('option_byline_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/byline_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_color', get_string('default') . ' ' .
            get_string('option_color', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/color', get_string('option_color', 'videotime'),
            get_string('option_color_help', 'videotime'), '00adef', PARAM_TEXT));
        $settings->add(new admin_setting_configcheckbox('videotime/color_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_muted', get_string('default') . ' ' .
            get_string('option_muted', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/muted', get_string('option_muted', 'videotime'),
            get_string('option_muted_help', 'videotime'), '0'));
        $settings->add(new admin_setting_configcheckbox('videotime/muted_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_playsinline', get_string('default') . ' ' .
            get_string('option_playsinline', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/playsinline', get_string('option_playsinline', 'videotime'),
            get_string('option_playsinline_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/playsinline_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_portrait', get_string('default') . ' ' .
            get_string('option_portrait', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/portrait', get_string('option_portrait', 'videotime'),
            get_string('option_portrait_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/portrait_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_speed', get_string('default') . ' ' .
            get_string('option_speed', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/speed', get_string('option_speed', 'videotime'),
            get_string('option_speed_help', 'videotime'), '0'));
        $settings->add(new admin_setting_configcheckbox('videotime/speed_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_title', get_string('default') . ' ' .
            get_string('option_title', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/title', get_string('option_title', 'videotime'),
            get_string('option_title_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/title_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_transparent', get_string('default') . ' ' .
            get_string('option_transparent', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/transparent', get_string('option_transparent', 'videotime'),
            get_string('option_transparent_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/transparent_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        if (videotime_has_repository()) {
            $settings->add(new admin_setting_heading('label_mode', get_string('default') . ' ' .
                get_string('mode', 'videotime'), ''));
            $settings->add(new admin_setting_configselect('videotime/label_mode', get_string('mode', 'videotime'),
                get_string('mode_help', 'videotime'), 0, [
                    0 => get_string('normal_mode', 'videotime'),
                    1 => get_string('label_mode', 'videotime'),
                    2 => get_string('preview_mode', 'videotime')
                ]));
            $settings->add(new admin_setting_configcheckbox('videotime/label_mode_force', get_string('force', 'videotime'),
                get_string('force_help', 'videotime'), '0'));

            $settings->add(new admin_setting_heading('show_title', get_string('default') . ' ' .
                get_string('show_title', 'videotime'), ''));
            $settings->add(new admin_setting_configcheckbox('videotime/show_title', get_string('show_title', 'videotime'),
                '', '1'));
            $settings->add(new admin_setting_configcheckbox('videotime/show_title_force', get_string('force', 'videotime'),
                get_string('force_help', 'videotime'), '0'));

            $settings->add(new admin_setting_heading('show_description', get_string('default') . ' ' .
                get_string('show_description', 'videotime'), ''));
            $settings->add(new admin_setting_configcheckbox('videotime/show_description', get_string('show_description', 'videotime'),
                '', '1'));
            $settings->add(new admin_setting_configcheckbox('videotime/show_description_force', get_string('force', 'videotime'),
                get_string('force_help', 'videotime'), '0'));

            $settings->add(new admin_setting_heading('show_tags', get_string('default') . ' ' .
                get_string('show_tags', 'videotime'), ''));
            $settings->add(new admin_setting_configcheckbox('videotime/show_tags', get_string('show_tags', 'videotime'),
                '', '1'));
            $settings->add(new admin_setting_configcheckbox('videotime/show_tags_force', get_string('force', 'videotime'),
                get_string('force_help', 'videotime'), '0'));

            $settings->add(new admin_setting_heading('show_duration', get_string('default') . ' ' .
                get_string('show_duration', 'videotime'), ''));
            $settings->add(new admin_setting_configcheckbox('videotime/show_duration', get_string('show_duration', 'videotime'),
                '', '1'));
            $settings->add(new admin_setting_configcheckbox('videotime/show_duration_force', get_string('force', 'videotime'),
                get_string('force_help', 'videotime'), '0'));

            $settings->add(new admin_setting_heading('show_viewed_duration', get_string('default') . ' ' .
                get_string('show_viewed_duration', 'videotime'), ''));
            $settings->add(new admin_setting_configcheckbox('videotime/show_viewed_duration', get_string('show_viewed_duration', 'videotime'),
                '', '1'));
            $settings->add(new admin_setting_configcheckbox('videotime/show_viewed_duration_force', get_string('force', 'videotime'),
                get_string('force_help', 'videotime'), '0'));

            $settings->add(new admin_setting_heading('columns', get_string('default') . ' ' .
                get_string('columns', 'videotime'), ''));
            $settings->add(new admin_setting_configselect('videotime/columns', get_string('columns', 'videotime'),
                get_string('columns_help', 'videotime'), 0, [
                    1 => '1 (100% width)',
                    2 => '2 (50% width)',
                    3 => '3 (33% width)',
                    4 => '4 (25% width'
                ]));
            $settings->add(new admin_setting_configcheckbox('videotime/columns_force', get_string('force', 'videotime'),
                get_string('force_help', 'videotime'), '0'));

            $settings->add(new admin_setting_heading('preview_picture', get_string('default') . ' ' .
                get_string('preview_picture', 'videotime'), ''));
            $settings->add(new admin_setting_configselect('videotime/preview_picture', get_string('preview_picture', 'videotime'),
                get_string('preview_picture_help', 'videotime'), 0, [
                    \videotimeplugin_repository\video_interface::PREVIEW_PICTURE_BIG => '1920 x 1200',
                    \videotimeplugin_repository\video_interface::PREVIEW_PICTURE_MEDIUM => '640 x 400',
                    \videotimeplugin_repository\video_interface::PREVIEW_PICTURE_BIG_WITH_PLAY => '1920 x 1200 ' .
                        get_string('with_play_button', 'videotime'),
                    \videotimeplugin_repository\video_interface::PREVIEW_PICTURE_MEDIUM_WITH_PLAY => '640 x 400 ' .
                        get_string('with_play_button', 'videotime')
                ]));
            $settings->add(new admin_setting_configcheckbox('videotime/preview_picture_force', get_string('force', 'videotime'),
                get_string('force_help', 'videotime'), '0'));
        }
    }
}

$ADMIN->add('modvideotimefolder', $settings);
// Tell core we already added the settings structure.
$settings = null;

if (videotime_has_pro() && videotime_has_repository()) {
    $ADMIN->add('modvideotimefolder', new admin_externalpage(
        'authenticate',
        get_string('authenticate_vimeo', 'videotime'),
        new moodle_url('/mod/videotime/plugin/repository/authenticate.php'),
        'moodle/site:config', true));

    $ADMIN->add('modvideotimefolder', new admin_externalpage(
        'overview',
        get_string('vimeo_overview', 'videotime'),
        new moodle_url('/mod/videotime/plugin/repository/overview.php')));
}