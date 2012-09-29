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
 * Question type class for the poodllrecording question type.
 *
 * @package    qtype
 * @subpackage poodllrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * The poodllrecording question type.
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording extends question_type {
    public function is_manual_graded() {
        return true;
    }

    public function response_file_areas() {
        return array('answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_poodllrecording_opts',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record('qtype_poodllrecording_opts', array('questionid' => $formdata->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record('qtype_poodllrecording_opts', $options);
        }

	//"import_or_save_files" won't work, because it expects output from an editor which is an array with member itemid
	//the filemanager doesn't produce this, so need to use file save draft area directly
	//$options->backimage = $this->import_or_save_files($formdata->backimage,
	// $context, 'qtype_poodllrecording', 'backimage', $formdata->id);
	
	file_save_draft_area_files($formdata->backimage, $context->id, 'qtype_poodllrecording',
	'backimage', $formdata->id, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
	
	//save the itemid of the backimage filearea
	$options->backimage = $formdata->backimage;

	//save the selected board size
	$options->boardsize=$formdata->boardsize;
    
        $options->responseformat = $formdata->responseformat;
		$options->graderinfo = $this->import_or_save_files($formdata->graderinfo,
                $context, 'qtype_poodllrecording', 'graderinfo', $formdata->id);
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $DB->update_record('qtype_poodllrecording_opts', $options);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->responseformat = $questiondata->options->responseformat;
		$question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
	$question->backimage=$questiondata->options->backimage;
$question->boardsize=$questiondata->options->boardsize;
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
            'audio' => get_string('formataudio', 'qtype_poodllrecording'),
			'video' => get_string('formatvideo', 'qtype_poodllrecording'),
			'mp3' => get_string('formatmp3', 'qtype_poodllrecording'),
			'picture' => get_string('formatpicture', 'qtype_poodllrecording'),
        );
       // 'simplepicture' => get_string('formatsimplepicture', 'qtype_poodllrecording')  
    }




	/**
	* @return array the different board sizes  that the whiteboard supports.
	* internal name => human-readable name.
	*/
	public function board_sizes() {
	return array(
	'320x320' => get_string('x320x320', 'qtype_poodllrecording'),
	'400x600' => get_string('x400x600', 'qtype_poodllrecording'),
	'500x500' => get_string('x500x500', 'qtype_poodllrecording'),
	'600x400' => get_string('x600x400', 'qtype_poodllrecording'),
	'600x800' => get_string('x600x800', 'qtype_poodllrecording'),
	'800x600' => get_string('x800x600', 'qtype_poodllrecording')
	);
	}

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = array();
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_poodllrecording', $lines);
        }
        return $choices;
    }

    /**
     * @return array the choices that should be offered for the number of attachments.
     */
    public function attachment_options() {
        return array(
            0 => get_string('no'),
            1 => '1',
            2 => '2',
            3 => '3',
            -1 => get_string('unlimited'),
        );
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_poodllrecording', 'graderinfo', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_poodllrecording', 'backimage', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_poodllrecording', 'graderinfo', $questionid);
        $fs->delete_area_files($contextid, 'qtype_poodllrecording', 'backimage', $questionid);
    }
}
