<?php

namespace App\Http\Controllers;

use App\Http\Resources\OwnerResource;
use App\Models\Document;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OwnerController extends Controller
{
    public function index($id)
    {
        $owner = Owner::with('kycDocument')->findOrFail($id);
        if ($owner) {
            return response()->json($owner, 201);
        } else {
            return $this->sendError('Token Expired', 'Authentication Failed', 403);
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:owners',
            'phone' => 'required|string|max:255',
            'address1' => 'required|string|max:255',
            'address2' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'ownership_stake' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'document_expiration' => 'required|string|max:255',
            'document' => 'sometimes|file|mimes:pdf',
        ]);

        $owner = new Owner(
            $request->only(
                [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'country',
                    'ownership_stake',
                    'document_type',
                    'document_expiration'
                ]
            )
        );

        $user = auth()->user();
        $owner->user_id = $user->id;

        $owner->save();
        if ($request->hasFile('document')) {
            $document = new Document();
            $document->owner_id = $owner->id;
            $document->url = '/storage/' . $request->file('document')->store('documents', 'public');
            $document->save();
            $owner->kyc_document = $document->id;
            $owner->save();
        }


        return response()->json(new OwnerResource($owner), 201);
    }

    public function update(Request $request, $id)
    {
        try {
            //code...
            // Find the user by ID or fail with a 404 error
            $owner = Owner::findOrFail($id);

            // Validate the incoming request data
            $request->validate([
                'first_name' => 'string|max:255',
                'last_name' => 'string|max:255',
                'email' => 'string|email|max:255|unique:owners,email,' . $id,
                'phone' => 'string|max:255',
                'address1' => 'string|max:255',
                'address2' => 'string|max:255',
                'city' => 'string|max:255',
                'state' => 'string|max:255',
                'zip' => 'string|max:255',
                'country' => 'string|max:255',
                'ownership_stake' => 'string|max:255',
                'document_type' => 'string|max:255',
                'document_expiration' => 'string|max:255',
                'document' => 'sometimes|file|mimes:pdf',
            ]);

            // Update user's information
            $owner->first_name = $request->input('first_name', $owner->first_name);
            $owner->last_name = $request->input('last_name', $owner->last_name);
            $owner->email = $request->input('email', $owner->email);
            $owner->phone = $request->input('phone', $owner->phone);
            $owner->address1 = $request->input('address1', $owner->address1);
            $owner->address2 = $request->input('address2', $owner->address2);
            $owner->city = $request->input('city', $owner->city);
            $owner->state = $request->input('state', $owner->state);
            $owner->zip = $request->input('zip', $owner->zip);
            $owner->country = $request->input('country', $owner->country);
            $owner->ownership_stake = $request->input('ownership_stake', $owner->ownership_stake);
            $owner->document_type = $request->input('document_type', $owner->document_type);
            $owner->document_expiration = $request->input('document_expiration', $owner->document_expiration);

            // Handle avatar update if provided
            if ($request->hasFile('document')) {
                if ($owner->kyc_document) {
                    $document = Document::find($owner->kyc_document);
                    Storage::delete(str_replace('/storage/', '', $document->url));
                    Document::find($owner->kyc_document)->delete();
                }
                $document = new Document();
                $document->owner_id = $owner->id;
                $document->url = '/storage/' . $request->file('document')->store('documents', 'public');
                $document->save();
                $owner->kyc_document = $document->id;
                $owner->save();
            }

            // Save the user
            $owner->save();

            return response()->json($owner, 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function list(Request $request)
    {
        $query = Owner::query();
        $query = $query->with('kycDocument')->where('is_deleted', false);

        // Add filters based on query parameters
        if ($request->has('search') && !empty($request->get('search'))) {
            $search = $request->input('search');
            // Assuming you want to search across multiple properties
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('address1', 'LIKE', "%{$search}%")
                    ->orWhere('address2', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('state', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%")
                    ->orWhere('ownership_stake', 'LIKE', "%{$search}%");
                // Add other properties you want to search by
            });
        }

        // Pagination - 10 users per page
        $owners = $query->paginate(10);

        return response()->json($owners);
    }

    public function delete($id)
    {
        $owner = Owner::findOrFail($id);
        $owner->is_deleted = true;
        $owner->save();

        return response()->json(['message' => 'User marked as deleted successfully']);
    }
}
