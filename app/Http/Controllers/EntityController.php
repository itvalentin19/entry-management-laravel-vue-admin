<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Director;
use App\Models\Document;
use App\Models\Entity;
use App\Models\Owner;
use App\Models\Person;
use App\Models\Ref;
use App\Models\Service;
use App\Models\Type;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EntityController extends Controller
{
    public function index($id)
    {
        $entity = Entity::with('documents')->findOrFail($id);

        if ($entity) {
            $owners = $entity['owner_ids'] ? $entity->get_owners($entity['owner_ids']) : [];
            // $documents = $entity['document_ids'] ? $entity->get_documents($entity['document_ids']) : [];
            $person = User::where('person', $entity['person'])->first();
            if ($person) {
                $user = new UserResource($person);
                $entity['contact_person'] = $user;
            }
            $directors = Director::where('entity_id', $id)->get();
            $entity['owners'] = $owners;
            $entity['director_list'] = $directors;
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
            'date_created' => 'required|string|max:255',
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
                // 'contact_first_name' => 'required|string|max:255',
                // 'contact_last_name' => 'required|string|max:255',
                // 'contact_phone' => 'required|string|max:255',
                // 'contact_email' => 'required|string|email|max:255',
                'director_list' => 'required|string',
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
                    'contact_first_name',
                    'contact_last_name',
                    'contact_phone',
                    'contact_email',
                    'ein_number',
                    'form_id',
                    'date_created',
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
        if ($entity->directors) {
            $entity->contact_first_name = $request->input('contact_first_name');
            $entity->contact_last_name = $request->input('contact_last_name');
            $entity->contact_phone = $request->input('contact_phone');
            $entity->contact_email = $request->input('contact_email');
            $request->validate([
                'director_list' => 'required|string',
            ]);
            $director_list = json_decode($request->input('director_list'), true);
            $director_ids = [];
            foreach ($director_list as $item) {
                $validator = validator($item, [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255',
                ]);

                if ($validator->fails()) {
                    $errors = $validator->errors();
                    $firstErrorMessage = $errors->first(); // Get the first error message

                    return response()->json([
                        'errors' => $errors,
                        'message' => $firstErrorMessage
                    ], 422);
                }
                $director = new Director();
                $director->first_name = $item['first_name'];
                $director->last_name = $item['last_name'];
                $director->phone = $item['phone'];
                $director->email = $item['email'];
                $director->entity_id = $entity->id;
                $director->save();
                $director_ids[] = $director->id;
            }
            if (count($director_ids) > 0) {
                $entity->director_ids = $director_ids;
            }
        }
        $pathPrefix = env('FILE_PATH_PREFIX', '/storage/');
        if ($request->hasFile('files')) {
            $document_ids = [];
            foreach ($request->file('files') as $file) {
                $document = new Document();
                $document->entity_id = $entity->id;
                $document->url = $pathPrefix . $file->store('documents', 'public');
                $document->save();
                $document_ids[] = $document->id;
            }
            $entity->document_ids = $document_ids;
        }
        $entity->save();


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
                'address_1' => 'string|max:255|nullable',
                'address_2' => 'string|max:255|nullable',
                'city' => 'string|max:255|nullable',
                'state' => 'string|max:255|nullable',
                'zip' => 'string|max:255|nullable',
                'country' => 'string|max:255|nullable',
                'type' => 'string|max:255|nullable',
                'services' => 'string|max:255|nullable',
                'annual_fees' => 'string|max:255|nullable',
                'first_tax_year' => 'string|max:255|nullable',
                'ein_number' => 'string|max:255|nullable',
                'contact_first_name' => 'string|max:255|nullable',
                'contact_last_name' => 'string|max:255|nullable',
                'contact_phone' => 'string|max:255|nullable',
                'contact_email' => 'string|email|max:255|nullable',
                'date_created' => 'string|max:255|nullable',
                'date_signed' => 'string|max:255|nullable',
                'person' => 'string|max:255|nullable',
                'jurisdiction' => 'string|max:255|nullable',
                'owner_ids' => 'string|max:255|nullable',
                'document_ids' => 'string|max:255|nullable',
                'files.*' => 'sometimes|file|mimes:pdf|nullable',
                'notes' => 'string|nullable',
                'ref_by' => 'string|max:255|nullable',
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
            $entity->date_created = $request->input('date_created', $entity->date_created);
            $entity->date_signed = $request->input('date_signed', $entity->date_signed);
            $entity->person = $request->input('person', $entity->person);
            $entity->jurisdiction = $request->input('jurisdiction', $entity->jurisdiction);
            $entity->notes = $request->input('notes', $entity->notes);
            $entity->ref_by = $request->input('ref_by', $entity->ref_by);
            $entity->contact_first_name = $request->input('contact_first_name', $entity->contact_first_name);
            $entity->contact_last_name = $request->input('contact_last_name', $entity->contact_last_name);
            $entity->contact_phone = $request->input('contact_phone', $entity->contact_phone);
            $entity->contact_email = $request->input('contact_email', $entity->contact_email);

            $entity->directors = $request->input('directors') == "true";
            $entity->form_id = $request->input('form_id') == "true";
            // $entity->contact_first_name = $request->input('contact_first_name');
            // $entity->contact_last_name = $request->input('contact_last_name');
            // $entity->contact_phone = $request->input('contact_phone');
            // $entity->contact_email = $request->input('contact_email');
            if ($entity->directors) {
                $request->validate([
                    'director_list' => 'required|string',
                ]);
                $director_list = json_decode($request->input('director_list'), true);
                $director_ids = [];
                $existing_ids = [];
                foreach ($director_list as $item) {
                    $validator = validator($item, [
                        'first_name' => 'string|max:255',
                        'last_name' => 'string|max:255',
                        'phone' => 'string|max:255',
                        'email' => 'string|email|max:255',
                    ]);

                    if ($validator->fails()) {
                        $errors = $validator->errors();
                        $firstErrorMessage = $errors->first(); // Get the first error message

                        return response()->json([
                            'errors' => $errors,
                            'message' => $firstErrorMessage
                        ], 422);
                    }

                    $director = Director::where('email', $item['email'])->where('entity_id', $entity->id)->first();
                    if ($director) {
                        $director->first_name = $item['first_name'];
                        $director->last_name = $item['last_name'];
                        $director->phone = $item['phone'];
                        $director->save();
                        $existing_ids[] = $director->id;
                    } else {
                        $director = new Director();
                        $director->first_name = $item['first_name'];
                        $director->last_name = $item['last_name'];
                        $director->phone = $item['phone'];
                        $director->email = $item['email'];
                        $director->entity_id = $entity->id;
                        $director->save();
                    }
                    $director_ids[] = $director->id;
                }
                if (count($director_ids) > 0) {
                    $entity->director_ids = $director_ids;
                }

                $previous_ids = $request->input('director_ids') ? json_decode($request->input('director_ids'), true) : [];
                $removed_ids = array_diff($previous_ids ?? [], $existing_ids);
                if (count($removed_ids) > 0) {
                    foreach ($removed_ids as $id) {
                        try {
                            Director::find($id)->delete();
                        } catch (\Throwable $th) {
                        }
                    }
                }
            } else {
                Director::where('entity_id', $entity->id)->delete();
            }

            $entity->owner_ids = array_map('intval', explode(',', $request->input('owner_ids')));

            $type_name = $request->input('type');
            $type_exist = Type::where('name', $type_name)->first();
            if (!$type_exist && $type_name) {
                $type = new Type();
                $type->name = $type_name;
                $type->save();
            }

            $person_type = $request->input('person');
            $person_exist = Person::where('type', $person_type)->first();
            if (!$person_exist && $person_type) {
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

            $pathPrefix = env('FILE_PATH_PREFIX', '/storage/');

            $removedIds = array_diff($entity->document_ids ?? [], $document_ids);
            if (count($removedIds) > 0) {
                foreach ($removedIds as $id) {
                    try {
                        $document = Document::find($id);
                        if ($document) {
                            Storage::delete(str_replace($pathPrefix, '', $document->url));
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
                    $document->url = $pathPrefix . $file->store('documents', 'public');
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
        $field = $request->input('field');
        $order = $request->input('order');
        $query->orderBy($field, $order);

        // Add filters based on query parameters
        if ($request->has('search') && !empty($request->get('search'))) {
            $search = $request->input('search');
            // Assuming you want to search across multiple properties
            $query->where(function ($query) use ($search) {
                $query->where('firm_name', 'LIKE', "%{$search}%")
                    ->orWhere('entity_name', 'LIKE', "%{$search}%")
                    ->orWhere('address_1', 'LIKE', "%{$search}%")
                    ->orWhere('address_2', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('state', 'LIKE', "%{$search}%")
                    ->orWhere('zip', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%")
                    ->orWhere('services', 'LIKE', "%{$search}%")
                    ->orWhere('person', 'LIKE', "%{$search}%")
                    ->orWhere('jurisdiction', 'LIKE', "%{$search}%")
                    ->orWhere('notes', 'LIKE', "%{$search}%")
                    ->orWhere('ref_by', 'LIKE', "%{$search}%")
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
        $users = User::whereNotNull('person')->orderBy('person', 'ASC')->pluck('person')->all();
        $owners = Owner::where('is_deleted', false)->select('id', 'first_name', 'last_name')->orderBy('first_name', 'ASC')->get();

        $data = [];
        $data['services'] = $services;
        $data['types'] = $types;
        $data['refs'] = $refs;
        $data['users'] = $users;
        $data['owners'] = $owners;

        return response()->json($data);
    }

    function convertDateString($dateString)
    {
        // Create a DateTime object from the date string
        $date = DateTime::createFromFormat('m/d/y', $dateString);

        // Check if the date is valid
        if ($date) {
            // Format the date as a string in the desired format (e.g., Y-m-d for 2023-11-01)
            $formattedDateString = $date->format('Y-m-d');
            return $formattedDateString;
        } else {
            return null;
        }
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $user = auth()->user();

        $path = $request->file('file')->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $entities = [];
        foreach ($worksheet->getRowIterator() as $row) {
            // Assuming the first row is the header
            if ($row->getRowIndex() == 1) {
                continue;
            }
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $entityData = [];
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }

            // Map cells to entity attributes
            $entityData['firm_name'] = $cells[0] ?? null;
            $entityData['entity_name'] = $cells[1] ?? null;
            $entityData['address_1'] = $cells[2] ?? null;
            $entityData['address_2'] = $cells[3] ?? null;
            $entityData['city'] = $cells[4] ?? null;
            $entityData['state'] = $cells[5] ?? null;
            $entityData['zip'] = $cells[6] ?? null;
            $entityData['country'] = $cells[7] ?? null;
            $entityData['type'] = $cells[8] ?? null;
            $entityData['contact_first_name'] = $cells[9] ?? null;
            $entityData['contact_last_name'] = $cells[10] ?? null;
            $entityData['contact_phone'] = $cells[11] ?? null;
            $entityData['contact_email'] = $cells[12] ?? null;
            $entityData['services'] = $cells[13] ?? null;
            $entityData['annual_fees'] = $cells[14] ?? null;
            $entityData['first_tax_year'] = $cells[15] ?? null;
            $entityData['directors'] = $cells[16] ? $cells[16] == 'Yes' : null;
            $entityData['ein_number'] = $cells[17] ?? null;
            $entityData['date_created'] = $cells[18] ? $this->convertDateString($cells[18]) : null;
            $entityData['form_id'] = $cells[19] ? $cells[19] == 'Yes' : null;
            $entityData['date_signed'] = $cells[20] ? $this->convertDateString($cells[20]) : null;
            $entityData['person'] = $cells[21] ?? null;
            $entityData['jurisdiction'] = $cells[22] ?? null;
            $entityData['owners'] = $cells[23] ? explode(',', $cells[23]) : null;
            $entityData['notes'] = $cells[25] ?? null;
            $entityData['ref_by'] = $cells[26] ?? null;
            $entityData['doing_business_as'] = $cells[27] ?? null;
            $entityData['document_ids'] = $user->id;
            $entityData['user_id'] = $user->id;

            // Add the entity to the array
            $entities[] = $entityData;
        }

        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($entities as $entityData) {
                // Assuming 'Entity' is your Eloquent model
                if ($entityData['firm_name']) {
                    $count++;
                    Entity::create($entityData);
                    $type_name = $entityData['type'];
                    if ($type_name) {
                        $type = Type::where('name', $type_name)->first();
                        if (!$type) {
                            $type = new Type();
                            $type->name = $type_name;
                            $type->save();
                        }
                    }

                    $person_type = $entityData['person'];
                    $person_exist = Person::where('type', $person_type)->first();
                    if (!$person_exist && $person_type) {
                        $person = new Person();
                        $person->type = $person_type;
                        $person->save();
                    }

                    $refs = explode(',', $entityData['ref_by']) ?? [];
                    foreach ($refs as $ref_name) {
                        $ref_exist = Ref::where('name', $ref_name)->first();
                        if (!$ref_exist && $ref_name) {
                            $ref = new Ref();
                            $ref->name = $ref_name;
                            $ref->save();
                        }
                    }

                    $services = explode(',', $entityData['services']) ?? [];
                    foreach ($services as $service_name) {
                        $service_exist = Service::where('name', $service_name)->first();
                        if (!$service_exist && $service_name) {
                            $service = new Service();
                            $service->name = $service_name;
                            $service->save();
                        }
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => $count . ' entities added successfully!', 'entities' => $entities]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $entity = Entity::findOrFail($id);
        $entity->is_deleted = true;
        $entity->save();

        return response()->json(['message' => 'User marked as deleted successfully']);
    }
}
