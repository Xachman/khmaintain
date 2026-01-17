<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('kingdomHall')
            ->orderBy('name')
            ->get();

        return response()->json([
            "data" => $contacts
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kingdom_hall_id' => 'required|exists:kingdom_halls,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'nullable|string|max:100',
            'notify_email' => 'boolean',
            'notify_sms' => 'boolean',
            'active' => 'boolean',
        ]);

        $contact = Contact::create($validated);

        return response()->json([
            "data" => $contact->load('kingdomHall')
        ], 201);
    }

    public function show(Contact $contact)
    {
        $contact->load(['kingdomHall', 'notifications']);

        return response()->json([
            "data" => $contact
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'kingdom_hall_id' => 'sometimes|required|exists:kingdom_halls,id',
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'nullable|string|max:100',
            'notify_email' => 'boolean',
            'notify_sms' => 'boolean',
            'active' => 'boolean',
        ]);

        $contact->update($validated);

        return response()->json([
            "data" => $contact->load('kingdomHall')
        ]);
    }

    public function destroy(Contact $contact)
    {
        $contact->update(['active' => false]);

        return response()->json([
            "message" => "Contact deactivated successfully."
        ]);
    }
}
