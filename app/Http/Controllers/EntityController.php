<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Entity;
use App\Models\Owner;
use App\Models\Person;
use App\Models\Ref;
use App\Models\Service;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EntityController extends Controller
{
    public function index($id)
    {
        $entity = Entity::with('documents')->findOrFail($id);

        if ($entity) {
            $owners = $entity['owner_ids'] ? $entity->get_owners($entity['owner_ids']) : [];
            // $documents = $entity['document_ids'] ? $entity->get_documents($entity['document_ids']) : [];
            $entity['owners'] = $owners;
            // $entity['documents'] = $documents;
            return response()->json($entity, 201);
        } else {
            return $this->sendError('Token Expired', 'Authentication Failed', 403);
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'firm_name' => 'required|string|max:255',
            'doing_business_as' => 'required|string|max:255',
            'entity_name' => 'required|string|max:255',
            'address_1' => 'required|string|max:255',
            'address_2' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'services' => 'required|string|max:255',
            'annual_fees' => 'required|string|max:255',
            'first_tax_year' => 'sometimes|string|max:255',
            'ein_number' => 'required|string|max:255',
            'date_signed' => 'required|string|max:255',
            'person' => 'required|string|max:255',
            'jurisdiction' => 'required|string|max:255',
            'owner_ids' => 'required|string|max:255',
            'files.*' => 'required|file|mimes:pdf|max:2048',
            'notes' => 'sometimes|string',
            'ref_by' => 'required|string|max:255',
        ]);

        if ($request->input('directors') == "true") {
            $request->validate([
                'contact_first_name' => 'required|string|max:255',
                'contact_last_name' => 'required|string|max:255',
                'contact_phone' => 'required|string|max:255',
                'contact_email' => 'required|string|email|max:255',
            ]);
        }

        $entity = new Entity(
            $request->only(
                [
                    'firm_name',
                    'doing_business_as',
                    'entity_name',
                    'address_1',
                    'address_2',
                    'city',
                    'state',
                    'zip',
                    'country',
                    'type',
                    'services',
                    'annual_fees',
                    'first_tax_year',
                    'directors',
                    'ein_number',
                    'form_id',
                    'date_signed',
                    'person',
                    'jurisdiction',
                    'notes',
                    'ref_by'
                ]
            )
        );

        $entity->directors = $request->input('directors') == "true";
        $entity->form_id = $request->input('form_id') == "true";
        if ($entity->directors) {
            $entity->contact_first_name = $request->input('contact_first_name');
            $entity->contact_last_name = $request->input('contact_last_name');
            $entity->contact_phone = $request->input('contact_phone');
            $entity->contact_email = $request->input('contact_email');
        }

        $entity->owner_ids = array_map('intval', explode(',', $request->input('owner_ids')));

        $type_name = $request->input('type');
        $type_exist = Type::where('name', $type_name)->first();
        if (!$type_exist) {
            $type = new Type();
            $type->name = $type_name;
            $type->save();
        }

        $person_type = $request->input('person');
        $person_exist = Person::where('type', $person_type)->first();
        if (!$person_exist) {
            $person = new Person();
            $person->type = $person_type;
            $person->save();
        }

        $refs = explode(',', $request->input('ref_by'));
        foreach ($refs as $ref_name) {
            if ($ref_name) {
                $ref_exist = Ref::where('name', $ref_name)->first();
                if (!$ref_exist) {
                    $ref = new Ref();
                    $ref->name = $ref_name;
                    $ref->save();
                }
            }
        }

        $services = explode(',', $request->input('services'));
        foreach ($services as $service_name) {
            if ($service_name) {
                $service_exist = Service::where('name', $service_name)->first();
                if (!$service_exist) {
                    $service = new Service();
                    $service->name = $service_name;
                    $service->save();
                }
            }
        }

        $user = auth()->user();
        $entity->user_id = $user->id;
        $entity->save();
        if ($request->hasFile('files')) {
            $document_ids = [];
            foreach ($request->file('files') as $file) {
                $document = new Document();
                $document->entity_id = $entity->id;
                $document->url = '/storage/' . $file->store('documents', 'public');
                $document->save();
                $document_ids[] = $document->id;
            }
            $entity->document_ids = $document_ids;
            $entity->save();
        }


        return response()->json($entity, 201);
    }

    public function update(Request $request, $id)
    {
        try {
            //code...
            // Find the user by ID or fail with a 404 error
            $entity = Entity::findOrFail($id);

            // Validate the incoming request data
            $request->validate([
                'firm_name' => 'string|max:255',
                'doing_business_as' => 'string|max:255',
                'entity_name' => 'string|max:255',
                'address_1' => 'string|max:255',
                'address_2' => 'string|max:255',
                'city' => 'string|max:255',
                'state' => 'string|max:255',
                'zip' => 'string|max:255',
                'country' => 'string|max:255',
                'type' => 'string|max:255',
                'services' => 'string|max:255',
                'annual_fees' => 'string|max:255',
                'first_tax_year' => 'string|max:255',
                'ein_number' => 'string|max:255',
                'date_signed' => 'string|max:255',
                'person' => 'string|max:255',
                'jurisdiction' => 'string|max:255',
                'owner_ids' => 'string|max:255',
                'document_ids' => 'string|max:255',
                'files.*' => 'sometimes|file|mimes:pdf',
                'notes' => 'string',
                'ref_by' => 'string|max:255',
            ]);

            // Update user's information
            $entity->firm_name = $request->input('firm_name', $entity->firm_name);
            $entity->doing_business_as = $request->input('doing_business_as', $entity->doing_business_as);
            $entity->entity_name = $request->input('entity_name', $entity->entity_name);
            $entity->address_1 = $request->input('address_1', $entity->address_1);
            $entity->address_2 = $request->input('address_2', $entity->address_2);
            $entity->city = $request->input('city', $entity->city);
            $entity->state = $request->input('state', $entity->state);
            $entity->zip = $request->input('zip', $entity->zip);
            $entity->country = $request->input('country', $entity->country);
            $entity->type = $request->input('type', $entity->type);
            $entity->services = $request->input('services', $entity->services);
            $entity->annual_fees = $request->input('annual_fees', $entity->annual_fees);
            $entity->first_tax_year = $request->input('first_tax_year', $entity->first_tax_year);
            $entity->ein_number = $request->input('ein_number', $entity->ein_number);
            $entity->date_signed = $request->input('date_signed', $entity->date_signed);
            $entity->person = $request->input('person', $entity->person);
            $entity->jurisdiction = $request->input('jurisdiction', $entity->jurisdiction);
            $entity->notes = $request->input('notes', $entity->notes);
            $entity->ref_by = $request->input('ref_by', $entity->ref_by);

            $entity->directors = $request->input('directors') == "true";
            $entity->form_id = $request->input('form_id') == "true";
            $entity->contact_first_name = $entity->directors ? $request->input('contact_first_name') : null;
            $entity->contact_last_name = $entity->directors ? $request->input('contact_last_name') : null;
            $entity->contact_phone = $entity->directors ? $request->input('contact_phone') : null;
            $entity->contact_email = $entity->directors ? $request->input('contact_email') : null;

            $entity->owner_ids = array_map('intval', explode(',', $request->input('owner_ids')));

            $type_name = $request->input('type');
            $type_exist = Type::where('name', $type_name)->first();
            if (!$type_exist) {
                $type = new Type();
                $type->name = $type_name;
                $type->save();
            }

            $person_type = $request->input('person');
            $person_exist = Person::where('type', $person_type)->first();
            if (!$person_exist) {
                $person = new Person();
                $person->type = $person_type;
                $person->save();
            }

            $refs = explode(',', $request->input('ref_by'));
            foreach ($refs as $ref_name) {
                if ($ref_name) {
                    $ref_exist = Ref::where('name', $ref_name)->first();
                    if (!$ref_exist) {
                        $ref = new Ref();
                        $ref->name = $ref_name;
                        $ref->save();
                    }
                }
            }

            $services = explode(',', $request->input('services'));
            foreach ($services as $service_name) {
                if ($service_name) {
                    $service_exist = Service::where('name', $service_name)->first();
                    if (!$service_exist) {
                        $service = new Service();
                        $service->name = $service_name;
                        $service->save();
                    }
                }
            }
            $document_ids = array_map('intval', explode(',', $request->input('document_ids')));

            $removedIds = array_diff($entity->document_ids, $document_ids);
            if (count($removedIds) > 0) {
                foreach ($removedIds as $id) {
                    try {
                        $document = Document::find($id);
                        if ($document) {
                            Storage::delete(str_replace('/storage/', '', $document->url));
                            $document->delete();
                        }
                    } catch (\Throwable $th) {
                    }
                }
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $document = new Document();
                    $document->entity_id = $entity->id;
                    $document->url = '/storage/' . $file->store('documents', 'public');
                    $document->save();
                    $document_ids[] = $document->id;
                }
                $entity->document_ids = $document_ids;
            }

            // Save the Entity
            $entity->save();

            return response()->json($entity, 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function list(Request $request)
    {
        $query = Entity::query();
        $query = $query->with('documents')->where('is_deleted', false);

        // Add filters based on query parameters
        if ($request->has('search') && empty($request->get('search'))) {
            $search = $request->input('search');
            // Assuming you want to search across multiple properties
            $query->where(function ($query) use ($search) {
                $query->where('firm_name', 'LIKE', "%{$search}%")
                    ->orWhere('entity_name', 'LIKE', "%{$search}%")
                    ->orWhere('doing_business_as', 'LIKE', "%{$search}%");
                // Add other properties you want to search by
            });
        }

        // Pagination - 10 users per page
        $entities = $query->paginate(10);
        $ownerIds = $entities->flatMap(function ($entity) {
            return $entity->owner_ids ?? [];
        })->unique()->all();
        // Fetch owners in a single query
        $owners = Owner::whereIn('id', $ownerIds)->get()->keyBy('id');

        // Attach owners to entities
        foreach ($entities as $entity) {
            $entityOwners = collect($entity->owner_ids)
                ->map(function ($id) use ($owners) {
                    return $owners->get($id);
                })->filter();
            $entity->owners = $entityOwners;
        }


        return response()->json($entities);
    }

    public function getProps()
    {
        $services = Service::orderBy('name', 'ASC')->pluck('name')->all();
        $types = Type::orderBy('name', 'ASC')->pluck('name')->all();
        $refs = Ref::orderBy('name', 'ASC')->pluck('name')->all();
        $person_types = Person::orderBy('type', 'ASC')->pluck('type')->all();
        $owners = Owner::where('is_deleted', false)->select('id', 'first_name', 'last_name')->orderBy('first_name', 'ASC')->get();

        $data = [];
        $data['services'] = $services;
        $data['types'] = $types;
        $data['refs'] = $refs;
        $data['person_types'] = $person_types;
        $data['owners'] = $owners;

        return response()->json($data);
    }

    public function delete($id)
    {
        $entity = Entity::findOrFail($id);
        $entity->is_deleted = true;
        $entity->save();

        return response()->json(['message' => 'User marked as deleted successfully']);
    }
}
