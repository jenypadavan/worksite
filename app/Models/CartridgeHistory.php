<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property int cartridge_id
 */
class CartridgeHistory extends Model
{

    protected $table = 'cartridge_history';

    protected $fillable = ['status_from', 'status_to', 'cartridge_id'];

    /**
     * @return HasOne
     */
    public function cartridge(): HasOne
    {
        return $this->hasOne(Cartstorage::class, 'id', 'cartridge_id');
    }

    public function getOnFill($startDate, $endDate)
    {
        $query = CartridgeHistory::where(['cartridge_id' => $this->cartridge_id, 'status_from' => Cartstorage::DISLOCATION_FILL]);

        if ($startDate && $endDate)
            $query = $query->where('created_at', '>=', Carbon::parse($startDate . ' 00:00:00'))->where('created_at', '<=', Carbon::parse($endDate . ' 23:59:59'));

        return $query->count();

    }

    public function getFromFill($startDate, $endDate)
    {

        $query = CartridgeHistory::where(['cartridge_id' => $this->cartridge_id, 'status_to' => Cartstorage::DISLOCATION_FILL]);

        if ($startDate && $endDate)
            $query = $query->where('created_at', '>=', Carbon::parse($startDate . ' 00:00:00'))->where('created_at', '<=', Carbon::parse($endDate . ' 23:59:59'));

        return $query->count();
    }

    public function getToDepartment($startDate, $endDate)
    {
        $query = CartridgeHistory::where(['cartridge_id' => $this->cartridge_id])
            ->whereNotNull('status_from')
            ->where('status_from', '<>', Cartstorage::DISLOCATION_FILL)
            ->where('status_from', '<>', Cartstorage::DISLOCATION_STORAGE)
            ->where('status_from', '<>', Cartstorage::DISLOCATION_RIP);

        if ($startDate && $endDate)
            $query = $query->where('created_at', '>=', Carbon::parse($startDate . ' 00:00:00'))->where('created_at', '<=', Carbon::parse($endDate . ' 23:59:59'));

        return $query->count();
    }

    public function getFromDepartment($startDate, $endDate)
    {
        $query = CartridgeHistory::where(['cartridge_id' => $this->cartridge_id])
            ->where('status_to', '<>', Cartstorage::DISLOCATION_FILL)
            ->where('status_to', '<>', Cartstorage::DISLOCATION_STORAGE)
            ->where('status_to', '<>', Cartstorage::DISLOCATION_RIP);

        if ($startDate && $endDate)
            $query = $query->where('created_at', '>=', Carbon::parse($startDate . ' 00:00:00'))->where('created_at', '<=', Carbon::parse($endDate . ' 23:59:59'));

        return $query->count();
    }

    public function onStorage($id_name,$endDate)
    {
        return Cartstorage::where('updated_at','<=',$endDate)->where(['id_name'=>$id_name])->count();
    }
}
