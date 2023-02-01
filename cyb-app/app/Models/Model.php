<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
     public static function beginTransaction()
     {
          self::getConnectionResolver()->connection()->beginTransaction();
     }

     public static function commit()
     {
         self::getConnectionResolver()->connection()->commit();
     }

     public static function rollBack()
     {
         self::getConnectionResolver()->connection()->rollBack();
     }    
}