<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PollDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();

         $existingToken = $request->user()->tokens()->where('name', 'spa-token')->first();
    if (!$existingToken) {
        $tokenResult = $request->user()->createToken('spa-token');
        $apiToken = $tokenResult->plainTextToken;
    } else {
        // On ne peut pas récupérer le plainTextToken après création
        // Donc on en recrée un nouveau à chaque fois
        $existingToken->delete();
        $tokenResult = $request->user()->createToken('spa-token');
        $apiToken = $tokenResult->plainTextToken;
    }

    return view('polls.dashboard', [
        'polls' => $polls,
        'apiToken' => $apiToken,
    ]);
    }
}
