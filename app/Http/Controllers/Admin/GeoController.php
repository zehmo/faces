<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\Town;

class GeoController extends Controller
{
    public function lgas($stateId)
    {
        $lgas = Lga::where('state_id', $stateId)->orderBy('name')->get(['id', 'name']);
        return response()->json($lgas);
    }

    public function towns($lgaId)
    {
        $towns = Town::where('lga_id', $lgaId)->orderBy('name')->get(['id', 'name']);
        return response()->json($towns);
    }
}
