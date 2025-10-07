<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'location','address','branch_code' ,'email','company_id','epf','etf','contactno'];
}
