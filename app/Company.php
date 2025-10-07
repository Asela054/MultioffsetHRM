<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'code', 'address', 'mobile','land','email','epf','etf','bank_account_name','bank_account_number',
        'bank_account_branch_code','employer_number','zone_code','ref_no','vat_reg_no','svat_no'
    ];

}
