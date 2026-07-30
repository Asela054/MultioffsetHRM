<?php

namespace App\ManPowerEmployee;

use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    protected $table = 'manpower_employee_details';
    protected $primaryKey = 'id';

    protected $fillable =[
        'card_id','date','employee','national_id','off_next_day','company','phone','status','created_by', 'updated_by'
    ];
}
