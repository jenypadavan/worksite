<?php

namespace App\Http\Controllers;

use App\Events\SaveCartridgeHistory;
use App\Exports\StatisticsExports;
use App\Models\CartridgeHistory;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\DB;
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
        return view('cartridges.history');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getHistory(Request $request): JsonResponse
    {

        $order = $request->get('order');
        $columns = $request->get('columns');
        $startDate = $request->get('startDate') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->get('endDate') ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $length = $request->get('length', 10);
        $startRecord = $request->get('start', 0);

        $orderColumn = 'created_at';
        $orderDir = 'desc';

        if ($order) {
            $orderColumn = $columns[$order[0]['column']]['name'];
            $orderDir = $order[0]['dir'];
        }

        $history = CartridgeHistory::query()->select('cartridge_id');

        switch ($orderColumn) {
            case 'cartridge_id':
                $history = $history
                    ->leftJoin('cartstorages', 'cartstorages.id', '=', 'cartridge_history.cartridge_id')
                    ->leftJoin('cartriges', 'cartstorages.id_name', '=', 'cartriges.id')
                    ->leftJoin('printmodels', 'cartstorages.id_mod', '=', 'printmodels.id')
                    ->orderBy('cartriges.name', $orderDir)
                    ->orderBy('printmodels.name', $orderDir);
                break;
            case  'sh_code':
                $history = $history
                    ->leftJoin('cartstorages', 'cartstorages.id', '=', 'cartridge_history.cartridge_id')
                    ->orderBy('cartstorages.sh_code', $orderDir);
                break;
            case 'on_fill':
                $history = $history->select(['cartridge_id', DB::raw("COUNT(if(status_from='на заправке',1,null)) AS cnt")])
                    ->orderBy('cnt', $orderDir);
                break;
            case 'from_fill':
                $history = $history->select(['cartridge_id', DB::raw("COUNT(if(status_to='на заправке',1,null)) AS cnt")])
                    ->orderBy('cnt', $orderDir);
                break;
            case 'to_department':
                $history = $history
                    ->select(['cartridge_id', DB::raw('COUNT(if(status_from<>"на заправке" and status_from<>"Склад" and status_from is not NULL and status_from<>"rip",1,null)) as cnt')])
                    ->orderBy('cnt', $orderDir);
                break;
            case 'from_department':
                $history = $history
                    ->select(['cartridge_id', DB::raw('COUNT(if(status_to<>"на заправке" and status_to<>"Склад" and status_to<>"rip",1,null)) as cnt')])
                    ->orderBy('cnt', $orderDir);
                break;
            default:
                $history = $history->orderBy($orderColumn, $orderDir);
                break;
        }

        $history = $history->groupBy('cartridge_history.cartridge_id');

        $totalRecords = $history->get()->count();

        $history = $history->where('cartridge_history.created_at', '>=', Carbon::parse($startDate . ' 00:00:00'))->where('cartridge_history.created_at', '<=', Carbon::parse($endDate . ' 23:59:59'));

        $filteredRecords = $history->get()->count();

        $history = $history->offset($startRecord)->limit($length)->get();

        $data = [];
        foreach ($history as $hs) {
            $data[] = [
                'sh_code' => $hs->cartridge->sh_code,
                'cartridge_id' => $hs->cartridge->cartName->name . " " . $hs->cartridge->printName->name,
                'on_fill' => $hs->getOnFill($startDate, $endDate),
                'from_fill' => $hs->getFromFill($startDate, $endDate),
                'to_department' => $hs->getToDepartment($startDate, $endDate),
                'from_department' => $hs->getFromDepartment($startDate, $endDate),
                'on_storage' => $hs->onStorage($endDate),
            ];
        }

        $res['recordsTotal'] = $totalRecords;
        $res['recordsFiltered'] = $filteredRecords;
        $res['data'] = $data;
        return response()->json($res);
    }

    public function download(Request $request)
    {

        return (new StatisticsExports($request))->download("Statistics.xlsx");

    }
}
