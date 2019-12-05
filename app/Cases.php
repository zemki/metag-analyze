<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helper;

class Cases extends Model
{

    protected $table = 'cases';

    protected $guarded = [];

    public function entries()
    {
        return $this->hasMany(Entry::class, 'case_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function path()
    {
        return "/cases/{$this->id}";
    }


    /**
     * assign a user to this case
     * this will be the user that fills the entries
     * @param User $user user to assign to the case
     */
    public function addUser($user)
    {
        is_array($user) ? $user = \App\User::firstOrCreate($user) : $user = $user;

        $this->user()->associate($user);
        $this->save();
        return $user;
    }

    /**
     * edit the case only if has no entries
     * @return bool
     */
    public function isEditable()
    {
        return $this->entries()->count() > 0 ? false : true;
    }


    public function isConsultable()
    {
        $timestampLastDay = strtotime($this->lastDay());
        $now = strtotime(date("Y-m-d H:i:s"));

        return ($timestampLastDay < $now);
    }


    public function notYetStarted()
    {
        $now = strtotime(date("Y-m-d H:i:s"));
        $timestampFirstDay = strtotime($this->firstDay());

        return $this->lastDay() == "" || ($now < $timestampFirstDay);

    }

    /**
     * write the duration from the database value to a readable format
     * @return string
     */
    public function lastDay()
    {

        $duration = $this->duration;
        //$formattedString = "<p><strong class=\"title\">Duration</strong><br><strong>Hours</strong>: ";
       // $formattedString = Helper::get_string_between($this->duration, 'value:', '|');
        //$formattedString = "Days: " . Helper::get_string_between($duration, 'days:', '|');

        $lastDay = Helper::get_string_between($duration, 'lastDay:', '|');

        return $lastDay;
    }

    /**
     * write the duration from the database value to a readable format
     * @return string
     */
    public function firstDay()
    {

        $duration = $this->duration;
        //$formattedString = "<p><strong class=\"title\">Duration</strong><br><strong>Hours</strong>: ";
       // $formattedString = Helper::get_string_between($this->duration, 'value:', '|');
        //$formattedString = "Days: " . Helper::get_string_between($duration, 'days:', '|');

        $lastDay = Helper::get_string_between($duration, 'firstDay:', '|');

        return $lastDay;
    }

}
