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
 * Open ID authentication. This file is a simple login entry point for OAuth identity providers.
 *
 * @package auth_oauth2
 * @copyright 2017 Damyon Wiese
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require_once('../../config.php');

$issuerid = required_param('id', PARAM_INT);
$wantsurlparam = optional_param('wantsurl', '', PARAM_LOCALURL);
$wantsurl = $wantsurlparam ? new moodle_url($wantsurlparam) : new moodle_url('/');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/auth/oauth2/login.php', ['id' => $issuerid]));

$incomingsesskey = optional_param('sesskey', '', PARAM_RAW);

$code = optional_param('code', null, PARAM_RAW);
$error = optional_param('error', null, PARAM_RAW);

if ($error) {
    $message = optional_param('error_description', null, PARAM_RAW);
    $SESSION->loginerrormsg = $message ?? $error;
    redirect(new moodle_url(get_login_url()));
}

if ($code === null && !confirm_sesskey($incomingsesskey)) {
    $PAGE->set_pagelayout('login');
    $PAGE->set_title(get_string('oauth2sessionexpired', 'auth_oauth2'));
    $PAGE->set_heading(format_string($SITE->fullname));

    echo $OUTPUT->header();

    $data = [
        'notification' => $OUTPUT->notification(
            get_string('oauth2sessionexpired_desc', 'auth_oauth2'),
            'info'
        ),
        'hasidps' => false,
        'idps' => [],
        'fallbackmessage' => get_string('oauth2sessionexpired_noservice', 'auth_oauth2'),
        'loginurl' => (new moodle_url(get_login_url()))->out(false),
    ];

    try {
        $issuer = new \core\oauth2\issuer($issuerid);
    } catch (Exception $e) {
        $issuer = null;
    }

    if ($issuer && $issuer->is_available_for_login()) {
        $authurl = new moodle_url(
            '/auth/oauth2/login.php',
            [
            'id' => $issuerid,
            'sesskey' => sesskey(),
            ]
        );

        $data['hasidps'] = true;
        $data['idps'][] = [
            'url' => $authurl->out(false),
            'name' => format_string($issuer->get('name')),
            'iconurl' => $issuer->get('image'),
        ];
    }
    echo $OUTPUT->render_from_template('auth_oauth2/sessionexpired', $data);
    echo $OUTPUT->footer();
    exit;
}

if (!\auth_oauth2\api::is_enabled()) {
    throw new \moodle_exception('notenabled', 'auth_oauth2');
}

$issuer = new \core\oauth2\issuer($issuerid);
if (!$issuer->is_available_for_login()) {
    throw new \moodle_exception('issuernologin', 'auth_oauth2');
}

$returnparams = ['wantsurl' => $wantsurl->out(false), 'id' => $issuerid];
if ($code === null) {
    $returnparams['sesskey'] = sesskey();
}
$returnurl = new moodle_url('/auth/oauth2/login.php', $returnparams);

$client = \core\oauth2\api::get_user_oauth_client($issuer, $returnurl);

if ($client) {
    if (!$client->is_logged_in()) {
        redirect($client->get_login_url());
    }

    $auth = new \auth_oauth2\auth();
    $auth->complete_login($client, $wantsurl);
} else {
    throw new moodle_exception('Could not get an OAuth client.');
}
