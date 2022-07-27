<?php

namespace briza\manager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class image extends Model
{
    use HasFactory;

    protected $fillable = [
        'image','table','related_table_id','path'
    ];
}
