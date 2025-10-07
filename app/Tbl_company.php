<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_company extends Model
{
    protected $table = 'tbl_company';
    protected $primaryKey = 'idtbl_company';

    protected $fillable = [
        'company', 'code', 'address1','address2', 'mobile','phone','email','status','updateuser','tbl_user_idtbl_user'
    ];

    public $timestamps = false;
}
