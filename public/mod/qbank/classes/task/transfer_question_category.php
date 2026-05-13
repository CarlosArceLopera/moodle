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

namespace mod_qbank\task;

/**
 * Transfer one top-level question category to its new qbank context.
 *
 * @package    mod_qbank
 * @copyright  2026 onwards Catalyst IT CA {@link https://catalyst-ca.net}
 * @author     Carlos Arce <carlosarcelopera@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transfer_question_category extends transfer_question_categories {

    #[\Override]
    public function execute(): void {
        global $DB;

        $data = $this->get_custom_data();
        if (empty($data->topcategoryid)) {
            mtrace('transfer_question_category task has no topcategoryid. Terminating task.');
            return;
        }

        mtrace("Starting transfer_question_category task for top category {$data->topcategoryid}.");

        $oldtopcategory = $DB->get_record('question_categories', ['id' => $data->topcategoryid, 'parent' => 0]);
        if (!$oldtopcategory) {
            mtrace("Could not find top category {$data->topcategoryid} with parent 0. Terminating task.");
            return;
        }

        $this->execute_for_top_category($oldtopcategory);
        mtrace("Finished transfer_question_category task for top category {$data->topcategoryid}.");
    }
}
