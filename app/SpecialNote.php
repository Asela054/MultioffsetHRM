<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpecialNote extends Model
{
    protected $table = 'special_notes';
    
    protected $fillable = ['period_id', 'note', 'created_by', 'updated_by'];

    public function details()
    {
        return $this->hasMany('App\SpecialNoteDetail', 'note_id', 'id');
    }

    public function paymentPeriod()
    {
        return $this->belongsTo('App\PaymentPeriod', 'period_id', 'id');
    }
}