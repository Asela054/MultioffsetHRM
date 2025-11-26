<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayrollchartAccount extends Model
{
    protected $table = 'payroll_chart_accounts';
    protected $primaryKey = 'id';

    protected $fillable = [
        'type', 'type_code', 'credit_account_id', 'debit_account_id','status','created_by','updated_by'
    ];
}
