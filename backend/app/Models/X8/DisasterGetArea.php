<?php

namespace App\Model\X8;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisasterGetArea extends Model
{
    protected $connection = 'oracle2';
    protected $table = 'GET_AREA';
}
