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

use core\context\module;
use core\exception\moodle_exception;

/**
 * Testable version of the transfer_question_category class.
 *
 * @package    mod_qbank
 * @copyright  2026 onwards Catalyst IT CA {@link https://catalyst-ca.net}
 * @author     Carlos Arce <carlosarcelopera@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_transfer_question_category extends transfer_question_category {
    /** @var int tracks the number of test transfers. */
    private static int $testcounter = 0;

    /**
     * Reset the shared transfer counter.
     *
     * @return void
     */
    public static function reset_counter(): void {
        self::$testcounter = 0;
    }

    /**
     * Summary of move_question_category
     * @param \stdClass $oldtopcategory
     * @param module $newcontext
     * @return void
     */
    #[\Override]
    protected function move_question_category(\stdClass $oldtopcategory, module $newcontext): array {
        if (self::$testcounter >= 1) {
            // We simulate a failure after successfully transferring one top category
            // and creating the corresponding transfer_questions tasks.
            throw new moodle_exception('This is a mocked exception for testing purposes.');
        }
        self::$testcounter++;
        return parent::move_question_category($oldtopcategory, $newcontext);
    }
}
