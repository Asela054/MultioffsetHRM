<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpecialNoteDetail extends Model
{
    protected $table = 'special_note_details';
    
    public $timestamps = false;
    
    protected $fillable = ['note_id', 'emp_id'];

    public function specialNote()
    {
        return $this->belongsTo('App\SpecialNote', 'note_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo('App\Employee', 'emp_id', 'id');
    }
}