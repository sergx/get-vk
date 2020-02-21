<?php

namespace Modules\Task\Entities;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [];

    // public function PLACEHOLDER(){
    //     return $this->hasMany('Modules\PLACEHOLDER\Entities\PLACEHOLDER');
    //     return $this->hasOne('Modules\PLACEHOLDER\Entities\PLACEHOLDER');
    //     return $this->belongsTo('Modules\Org\Entities\Org');
    //     return $this->belongsToMany('Modules\Product\Entities\Product')->withPivot('price', 'quantity')->withTimestamps();
    //     return $this->belongsTo('App\User');
    // }

    public function project(){
        return $this->belongsTo('Modules\Project\Entities\Project');
    }

    public function task_data(){
        return $this->hasMany('Modules\Task\Entities\TaskData');
    }
}
