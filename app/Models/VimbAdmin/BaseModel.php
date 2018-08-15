<?php

namespace App\Models\VimbAdmin;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_vimbadmin';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';
}