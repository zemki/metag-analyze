<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{

	protected $table = 'entries';
	protected $guarded = [];

    /**
     * Cases is intended to be CASE
     * but CASE is a reserved keyword in most programming languages
     * @return relationship with Case model
     */
    public function cases()
    {
    	return $this->belongsTo(Cases::class);
    }

    public function project()
    {
        return$this->belongsTo(Project::class);
    }

    public function place()
    {
    	return $this->belongsTo(Place::class);
    }

    public function communication_partner()
    {
    	return $this->belongsTo(Communication_Partner::class);
    }

    public function media()
    {
    	return $this->belongsTo(Media::class);
    }




}
