<?php

namespace App\Http\Controllers;

use App\Events\SaveCartridgeHistory;
use App\Models\CartridgeHistory;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Cartrige;
use App\Models\Printmodel;
use App\Models\Cartstorage;
use App\Models\Otdel;
use Throwable;

class CartStorageController extends Controller
{

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('cartridges.index');
    }

    /**
     * @return Application|Factory|View
     */
    public function show($storage = null)
    {
        switch ($storage) {
            case 'storage':
                return view('cartridges.give-form');
            case 'fill':
                return view('cartridges.fill-form');
            case 'work':
                $departments = Otdel::orderBy('otd_name')->get();

                return view('cartridges.form-work', compact('departments'));
            case 'kill':
                return view('cartridges.kill-form');
            default:
                $cartridgeBrands = Cartrige::orderBy('name')->get();
                $printerModels = Printmodel::orderBy('name')->get();

                return view('cartridges.form', compact('cartridgeBrands', 'printerModels'));
        }


    }

    /**
     * @return Application|Factory|View
     */
    public function act()
    {
        $cartridges = Cartstorage::where('act', 1)->get();

        return view('cartridges.act', compact('cartridges'));

    }

    /**
     * @return void
     */
    public function clearAct()
    {
        $cartridges = Cartstorage::where('act', 1)->get();

        foreach ($cartridges as $cartridge) {
            $cartridge->act = 0;
            $cartridge->disloc = Cartstorage::DISLOCATION_STORAGE;
            $cartridge->save();
        }
    }

    /**
     * @return Application|Factory|View
     */
    public function onStorage()
    {
        $cartridges = Cartstorage::where('disloc', 'Склад')->get();

        return view('cartridges.act', compact('cartridges'));

    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     * @throws Exception
     */
    public function save(Request $request)
    {

        $action = $request->get('action', 'registration');
        $shCode = $request->get('sh_code') ?? null;

        if (!$action)
            throw new Exception('no-action');

        $cartridge = Cartstorage::where('sh_code', $shCode)->first();

        $statusFrom = null;

        try {

            switch ($action) {
                case 'registration':

                    if ($cartridge)
                        throw new Exception('error-cartridge-exists');

                    $cartridge = new Cartstorage();
                    $cartridge->id_name = $request->get('id_name');
                    $cartridge->id_mod = $request->get('id_print');
                    $cartridge->sh_code = $shCode;
                    $cartridge->status = Cartstorage::STATUS_STORAGE;
                    $cartridge->disloc = Cartstorage::DISLOCATION_STORAGE;
                    $cartridge->cin = 0;

                    $cartridge->save();


                    break;
                case 'give':

                    if (!$cartridge)
                        throw new Exception('error-null-cartridge');

                    $statusFrom = $cartridge->disloc;

                    if ($cartridge->disloc == Cartstorage::DISLOCATION_STORAGE)
                        throw new Exception('error-dislocate-to-storage');

                    $cartridge->disloc = Cartstorage::DISLOCATION_STORAGE;
                    $cartridge->save();

                    break;
                case 'fill':

                    if (!$cartridge)
                        throw new Exception('error-null-cartridge');

                    $statusFrom = $cartridge->disloc;

                    if ($cartridge->disloc == Cartstorage::DISLOCATION_FILL)
                        throw new Exception('error-dislocate-to-fill');

                    $cartridge->disloc = Cartstorage::DISLOCATION_FILL;
                    $cartridge->cin += 1;
                    $cartridge->act = 1;
                    $cartridge->save();

                    break;
                case 'work':

                    if (!$cartridge)
                        throw new Exception('error-null-cartridge');

                    $statusFrom = $cartridge->disloc;

                    $cartridge->disloc = $request->get('otd');
                    $cartridge->save();
                    break;
                case 'kill':

                    if (!$cartridge)
                        throw new Exception('error-null-cartridge');

                    $statusFrom = $cartridge->disloc;

                    $cartridge->status = Cartstorage::STATUS_KILLED;
                    $cartridge->disloc = Cartstorage::DISLOCATION_RIP;
                    $cartridge->save();
                    break;

            }

            event(new SaveCartridgeHistory($cartridge, $statusFrom));

        } catch (Throwable $e) {
            return response()->json([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]
            ], 400);
        }

    }

    public function history()
    {
        $history = CartridgeHistory::all();
        return view('cartridges.history', compact('history'));
    }
}
