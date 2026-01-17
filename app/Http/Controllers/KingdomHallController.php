<?php

namespace App\Http\Controllers;

use App\Models\KingdomHall;
use Illuminate\Http\Request;

class KingdomHallController extends Controller
{
    public function index()
    {
        $kingdomHalls = KingdomHall::withCount(['contacts', 'scheduledMaintenances'])
            ->orderBy('name')
            ->get();

        return response()->json([
            "data" => $kingdomHalls
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $kingdomHall = KingdomHall::create($validated);

        return redirect()->route('kingdom-halls.show', $kingdomHall)
            ->with('success', 'Kingdom Hall created successfully.');
    }

    public function update(Request $request, KingdomHall $kingdomHall)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $kingdomHall->update($validated);

        return redirect()->route('kingdom-halls.show', $kingdomHall)
            ->with('success', 'Kingdom Hall updated successfully.');
    }

    public function destroy(KingdomHall $kingdomHall)
    {
        $kingdomHall->update(['active' => false]);

        return redirect()->route('kingdom-halls.index')
            ->with('success', 'Kingdom Hall deactivated successfully.');
    }
}
