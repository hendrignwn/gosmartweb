<?php

namespace App;

use App\Helpers\FormatConverter;

class OrderDetail extends BaseModel
{
	protected $table = 'order_detail';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 
        'course_id', 
        'on_at', 
        'section', 
        'section_time', 
		'amount', 
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
	
	public function order() 
	{
		return $this->hasOne('\App\Order', 'id', 'order_id');
	}
	
	public function course() 
	{
		return $this->hasOne('\App\Course', 'id', 'course_id');
	}
	
	public function getOnAt($replace = '<br/>')
	{
		return str_replace(',', $replace, $this->on_at);
		
	}
	
	public function getFormattedAmount()
	{
		return FormatConverter::rupiahFormat($this->amount, 2);
	}
}
