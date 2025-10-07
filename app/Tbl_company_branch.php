<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_company_branch extends Model
{
    protected $table = 'tbl_company_branch';
    protected $primaryKey = 'idtbl_company_branch';

    protected $fillable = [
        'branch', 'code', 'address1','address2', 'mobile','phone','email','status','updateuser','tbl_user_idtbl_user','tbl_company_idtbl_company'
    ];

    public $timestamps = false;
}
