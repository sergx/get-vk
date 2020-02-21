<?php

namespace Modules\Task\Entities;

use Illuminate\Database\Eloquent\Model;

class TaskData extends Model
{
    protected $fillable = [];

    protected $table = "task_data";

    // public function PLACEHOLDER(){
    //     return $this->hasMany('Modules\PLACEHOLDER\Entities\PLACEHOLDER');
    //     return $this->hasOne('Modules\PLACEHOLDER\Entities\PLACEHOLDER');
    //     return $this->belongsTo('Modules\Org\Entities\Org');
    //     return $this->belongsToMany('Modules\Product\Entities\Product')->withPivot('price', 'quantity')->withTimestamps();
    //     return $this->belongsTo('App\User');
    // }

    public function task(){
        return $this->belongsTo('Modules\Task\Entities\Task');
    }
}
