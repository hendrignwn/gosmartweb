<?php

namespace App;

class PrivateDetail extends BaseModel
{
	protected $table = 'private_detail';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'private_id', 
        'course_id', 
        'on_at', 
        'section', 
        'section_time', 
		'student_details',
		'teacher_details',
		'checklist', 
		'checklist_at', 
		'created_at', 
		'updated_at', 
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
	
	public function course() 
	{
		return $this->hasOne('\App\Course', 'id', 'course_id');
	}
	
	public function privateModel()
	{
		return $this->hasOne('\App\PrivateModel', 'id', 'private_id');
	}
	
	public function getOnAt($replace = '<br/>')
	{
		return str_replace(',', $replace, $this->on_at);
	}
	
	public function parseStudentDetails()
	{
		return json_decode($this->student_details, true);
	}
	
	public function getStudentDetailsHtml()
	{
		$studentDetails = $this->parseStudentDetails();
		if (count($studentDetails) == 0) {
			return null;
		}
		
		$html = '<ul>';
		foreach($studentDetails as $detail) {
			$detail['check'] = ($detail['check']==1) ? "True" : "False";
			$html .= "<li><b>On At : {$detail['on_at']}</b></li>";
			$html .= "<li style='list-style:none;'>Check   : {$detail['check']}</li>";
			$html .= "<li style='list-style:none;'>Check At : {$detail['check_at']}</li>";
		}
		
		return $html;
	}
	
	public function parseTeacherDetails()
	{
		return json_decode($this->teacher_details, true);
	}
	
	public function getTeacherDetailsHtml()
	{
		$studentDetails = $this->parseTeacherDetails();
		if (count($studentDetails) == 0) {
			return null;
		}
		
		$html = '<ul>';
		foreach($studentDetails as $detail) {
			$detail['check'] = ($detail['check']==1) ? "True" : "False";
			$html .= "<li><b>On At : {$detail['on_at']}</b></li>";
			$html .= "<li style='list-style:none;'>Check   : {$detail['check']}</li>";
			$html .= "<li style='list-style:none;'>Check At : {$detail['check_at']}</li>";
		}
		
		return $html;
	}
	
	public function getChecklistLabel()
	{
		return $this->checklist == 1 ? "True" : "False";
	}

}
