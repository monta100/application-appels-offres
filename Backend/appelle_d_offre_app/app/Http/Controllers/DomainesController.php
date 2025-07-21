<?php

namespace App\Http\Controllers;

use App\Models\Domaine;
use Illuminate\Http\Request;

class DomainesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
 public function index()
{
    $domaines = Domaine::all();
    return response()->json($domaines);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Domaine $domaines)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Domaine $domaines)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Domaine $domaines)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Domaine $domaines)
    {
        //
    }
}
