<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CartridgeHistory extends Model
{

    protected $table = 'cartridge_history';

    protected $fillable = ['status_from', 'status_to', 'cartridge_id'];

    /**
     * @return HasOne
     */
    public function cartridge(): HasOne
    {
        return $this->hasOne(Cartstorage::class,'id','cartridge_id');
    }
}
