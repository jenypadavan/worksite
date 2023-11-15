<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cartstorage extends Model
{
     public function cartName(){

        return $this->hasOne(Cartrige::class,'id','id_name');
    }

    public function printName(){

        return $this->hasOne(Printmodel::class,'id','id_mod');
    }


}
