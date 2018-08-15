<?php

namespace App\Models\Mum;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_mum';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}