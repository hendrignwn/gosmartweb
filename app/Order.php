<?php

namespace App;

use App\Helpers\FormatConverter;

class Order extends BaseModel
{
	const STATUS_CANCELED = 0;
	const STATUS_WAITING_PAYMENT = 1;
	const STATUS_CONFIRMED = 5;
	const STATUS_PAID = 10;
	
	protected $table = 'order';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'teacher_id', 
        'code', 
        'section', 
        'section_time', 
        'admin_fee', 
		'final_amount', 
		'payment_id', 
		'status', 
		'paid_by', 
		'paid_at', 
		'created_at', 
		'updated_at', 
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
		'user_id',
		'teacher_id',
		'paid_by', 
		'paid_at', 
    ];
	
	public function student() 
	{
		return $this->hasOne('\App\User', 'id', 'user_id');
	}
	
	public function teacher() 
	{
		return $this->hasOne('\App\User', 'id', 'teacher_id');
	}
	
	public function payment()
	{
		return $this->hasOne('\App\Payment', 'id', 'payment_id');
	}
	
	public function privateModel()
	{
		return $this->hasOne('\App\PrivateModel', 'order_id', 'id');
	}
	
	public function paidBy() 
	{
		return $this->hasOne('\App\User', 'id', 'paid_by');
	}
	
	public function orderDetails()
	{
		return $this->hasMany('\App\OrderDetail', 'order_id', 'id');
	}
	
	public function orderConfirmation()
	{
		return $this->hasOne('\App\OrderConfirmation', 'order_id', 'id');
	}
	
	public function getFormattedAdminFee()
	{
		return FormatConverter::rupiahFormat($this->admin_fee, 2);
	}
	
	public function getFormattedFinalAmount()
	{
		return FormatConverter::rupiahFormat($this->admin_fee, 2);
	}
	
	public static function statusLabels()
	{
		return [
			self::STATUS_CANCELED => 'Canceled',
			self::STATUS_WAITING_PAYMENT => 'Waiting for Payment',
			self::STATUS_CONFIRMED => 'Confirmed',
			self::STATUS_PAID => 'Paid',
		];
	}
	
	public function getStatusLabel()
	{
		$list = self::statusLabels();
		return isset($list[$this->status]) ? $list[$this->status] : '';
	}
	
	public static function generateCode($prefix = 'INV', $padLength = 4, $separator = '-') 
    {
        $left = strtoupper($prefix) . $separator . date('Y-m') . $separator;
        $leftLen = strlen($left);
        $increment = 1;

        $last = self::where('code', 'LIKE', "'%$left%'")
                ->orderBy('id', 'desc')
                ->limit(1)
                ->first();

        if ($last) {
            $increment = (int) substr($last->code, $leftLen, $padLength);
            $increment++;
        }

        $number = str_pad($increment, $padLength, '0', STR_PAD_LEFT);

        return $left . $number;
    }
	
	/**
	 * insert to private model when status paid
	 * 
	 * @return boolean
	 */
	public function insertToPrivateModel()
	{
		if (isset($this->privateModel)) {
			return;
		}
		
		if ($this->status != self::STATUS_PAID) {
			return;
		}
		
		$private = new PrivateModel();
		$attributes = [
			'user_id' => $this->user_id,
			'teacher_id' => $this->teacher_id,
			'order_id' => $this->id,
			'section' => $this->section,
			'section_time' => $this->section_time,
			'code' => PrivateModel::generateCode(),
			'start_date' => $this->start_date,
			'end_date' => $this->end_date,
			'status' => PrivateModel::STATUS_NOT_YET_GOING,
		];
		$private->fill($attributes);
		$private->save();
		
		if (isset($this->orderDetails)) {
			foreach ($this->orderDetails as $orderDetail) {
				$privateDetail = new PrivateDetail();
				
				$studentDetails = $teacherDetails = [];
				foreach (explode(',', $orderDetail->on_at) as $detail) {
					$studentDetails[] = $teacherDetails[] =  [
						'on_at' => $detail,
						'check' => 0,
						'check_at' => ''
					];
				}
				
				$attributes = [
					'private_id' => $private->id,
					'course_id' => $orderDetail->course_id,
					'on_at' => $orderDetail->on_at,
					'section' => $orderDetail->section,
					'section_time' => $orderDetail->section_time,
					'student_details' => json_encode($studentDetails),
					'teacher_details' => json_encode($teacherDetails),
					'checklist' => 0,
					'checklist_at' => null,
				];
				
				$privateDetail->fill($attributes);
				$privateDetail->save();
			};
		}
		
		return true;
	}
}
