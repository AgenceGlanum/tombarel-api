<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function index()
    {
        return Example::all();
    }

    public function store(Request $request)
    {
        $example = Example::create($request->all());
        return response()->json($example, 201);
    }

    public function update(Request $request, $id)
    {
        $example = Example::findOrFail($id);
        $example->update($request->all());
        return response()->json($example, 200);
    }

    public function destroy($id)
    {
        Example::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
