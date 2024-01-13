<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Mail\ForgotPasswordMail;
use App\Mail\WelcomeUserMail;
use App\Models\Entity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
	//

	public function index()
	{
		$user = auth()->user();
		if ($user) {
			$success['token'] = $user->createToken('user')->plainTextToken;
			$success['user'] = new UserResource($user);
			return $this->sendResponse($success, 'User Information');
		} else {
			return $this->sendError('Token Expired', 'Authentication Failed', 403);
		}
	}

	public function login(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|string',
			'password' => 'required|string',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error', $validator->errors(), 403);
		}

		if (Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password])) {
			$user = Auth::user();

			// Check if the user is marked as deleted
			if ($user->is_deleted) {
				return $this->sendError('Account not available', 'Your account is no longer active. Please contact support if you believe this is an error.', 401);
			}
			if (!$user->email_verified_at) {
				return $this->sendError('Account Access Restricted', 'Your account cannot be accessed at this time. Please verify your email address to proceed. Verification instructions have been sent to your registered email.', 401);
			}

			// Check if 'remember_me' is true in the request
			if ($request->has('remember_me') && $request->remember_me) {
				$tokenResult = $user->createToken('user', ['*'], now()->addYear());
			} else {
				$tokenResult = $user->createToken('user');
			}

			$success['token'] = $tokenResult->plainTextToken;
			$success['user'] = new UserResource($user);
			return $this->sendResponse($success, 'Login successful.');
		} else {
			return $this->sendError('Wrong email or Password!', 'Wrong email or Password!', 401);
		}
	}
	public function update(Request $request)
	{
		try {
			$user = auth()->user();

			// Validate the incoming request data
			$request->validate([
				'name' => 'string|max:255',
				'email' => 'string|email|max:255|unique:users,email,' . $user->id,
				'phone' => 'string|max:255',
				'address' => 'string|max:255',
				'address2' => 'string|max:255',
				'role' => 'in:admin,user',
				'avatar' => 'sometimes|file|image|max:2048', // 2MB Max
			]);

			// Update user's information
			$user->name = $request->input('name', $user->name);
			$user->email = $request->input('email', $user->email);
			$user->phone = $request->input('phone', $user->phone);
			$user->address = $request->input('address', $user->address);
			$user->address2 = $request->input('address2', $user->address2);
			$user->role = $request->input('role', $user->role);

			$pathPrefix = env('FILE_PATH_PREFIX', '/storage/');
			// Handle avatar update if provided
			if ($request->hasFile('avatar')) {
				// Delete old avatar if it exists and isn't a default
				if ($user->photo && !str_contains($user->photo, 'default_avatar.jpg')) {
					Storage::delete($user->photo);
				}

				// Store new avatar
				$user->photo = $pathPrefix . $request->file('avatar')->store('avatars', 'public');
			}

			// Save the user
			$user->save();

			return response()->json($user, 200);
		} catch (\Throwable $th) {
			throw $th;
		}
	}
	/**
	 * Logout the user.
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function logout(Request $request)
	{
		auth()->user()->currentAccessToken()->delete();

		return response()->json(['message' => 'Successfully logged out']);
	}


	public function register(Request $request)
	{
		// Validation rules
		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:users',
			'password' => 'required|string|min:6|confirmed',
		]);

		// Check if validation fails
		if ($validator->fails()) {
			return $this->sendError('Validation Error', $validator->errors(), 403);
		}

		// Create user
		$user = User::create([
			'name' => $request->name,
			'email' => $request->email,
			'password' => Hash::make($request->password),
			'role' => User::ROLE_USER
		]);

		// Create token for the registered user
		$success['token'] = $user->createToken('user')->plainTextToken;
		$success['user'] = new UserResource($user);

		// Return success response
		return $this->sendResponse($success, 'Registration successful.');
	}

	public function updatePassword(Request $request)
	{
		$user = auth()->user();

		$request->validate([
			'currentPassword' => 'required|string|max:255',
			'newPassword' => 'required|string|min:8|different:currentPassword',
			'confirmPassword' => 'required|string|same:newPassword',
		]);

		// Check if the current password is correct
		if (!Hash::check($request->currentPassword, $user->password)) {
			return response()->json(['message' => 'Current password is incorrect'], 403);
		}

		// Update the password
		$user->password = Hash::make($request->newPassword);
		$user->save();

		return response()->json(['message' => 'Password updated successfully']);
	}

	public function generatePersonName($name)
	{
		// Remove whitespace and convert to uppercase
		$name = strtoupper(str_replace(' ', '', $name));

		// Take the first 2 letters
		$personName = substr($name, 0, 2);

		// Check if this personName is unique (you need to implement this function)
		while (!$this->isUniquePersonName($personName)) {
			// If not unique, try adding another letter or changing letters
			$personName = $this->modifyPersonName($personName, $name);
		}

		return $personName;
	}

	private function isUniquePersonName($personName)
	{
		// Implement a check to see if $personName is unique in your system
		// For example, querying your database
		// Return true if unique, false otherwise
		$nameExist = User::where('person', $personName)->first();
		if ($nameExist)
			return false;
		else
			return true;
	}

	private function modifyPersonName($currentName, $fullName)
	{
		static $counter = 0;

		// Simple strategy to modify the name:
		// Cycle through letters of the full name
		if ($counter < strlen($fullName)) {
			$newName = substr($currentName, 0, 1) . $fullName[$counter];
		} else {
			// If cycled through all letters, start adding a third letter
			$newName = substr($fullName, 0, 2) . $fullName[$counter - strlen($fullName)];
		}

		$counter++;
		return $newName;
	}


	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'company' => 'required|string|max:100',
			'email' => 'required|string|email|max:255|unique:users',
			'phone' => 'required|string|max:255',
			'address' => 'required|string|max:255',
			'address2' => 'required|string|max:255',
			'avatar' => 'sometimes|file|image|max:2048',
			'city' => 'required|string|max:50',
			'state' => 'required|string|max:50',
			'zip' => 'required|string|max:10',
			'country' => 'required|string|max:50',
			'role' => 'required|in:admin,user', // Ensure role is either 'admin' or 'user'
		]);

		$user = new User($request->only(['name', 'company', 'email', 'phone', 'address', 'address2', 'role', 'city', 'state', 'zip', 'country']));
		$person = $this->generatePersonName($request->get('name'));
		$user->person = $person;
		$pathPrefix = env('FILE_PATH_PREFIX', '/storage/');

		if ($request->hasFile('avatar')) {
			$user->photo = $pathPrefix . $request->file('avatar')->store('avatars', 'public');
		} else {
			// Set default avatar path if no avatar is uploaded
			$user->photo = '/img/default_avatar.png'; // Make sure this path is correct
		}

		$user->password = Hash::make('Password'); // Set a default password or generate one
		$user->email_verified_at = null;
		$user->save();

		// Generate a password reset token
		$token = Password::getRepository()->create($user);

		// Send the email
		Mail::to($user->email)->send(new WelcomeUserMail($user, $token));

		return response()->json(new UserResource($user), 201);
	}

	public function resetUserPassword(Request $request)
	{
		// Validate the request data
		$request->validate([
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|confirmed|min:8',
		]);

		// Attempt to reset the user's password
		$status = Password::reset(
			$request->only('email', 'password', 'password_confirmation', 'token'),
			function ($user, $password) {
				// Save the new password and any other password-related cleanup tasks
				$user->password = Hash::make($password);
				$user->email_verified_at = Carbon::now();
				$user->save();
			}
		);

		if ($status == Password::PASSWORD_RESET) {
			return $this->sendResponse(["success" => true], 'User Information');
		} else {
			return $this->sendError("Token Expired", 'Authentication Failed', 403);
		}
	}

	public function forgotPassword(Request $request)
	{
		// Validate the request data
		$request->validate([
			'email' => 'required|email',
		]);

		$user = User::where('email', $request->input('email'))->first();
		if (!$user) {
			return $this->sendError("User Not Found", 'Authentication Failed', 403);
		}
		// Generate a password reset token
		$token = Password::getRepository()->create($user);

		// Send the email
		Mail::to($user->email)->send(new ForgotPasswordMail($user, $token));

		return $this->sendResponse(["success" => true, 'user' => $user], 'User Information');
	}

	public function getUser($id)
	{
		$user = User::findOrFail($id);
		if ($user) {
			return response()->json($user, 201);
		} else {
			return $this->sendError('Token Expired', 'Authentication Failed', 403);
		}
	}

	public function updateUser(Request $request, $id)
	{
		try {
			//code...
			// Find the user by ID or fail with a 404 error
			$user = User::findOrFail($id);

			// Validate the incoming request data
			$request->validate([
				'name' => 'string|max:255',
				'company' => 'string|max:100',
				'email' => 'string|email|max:255|unique:users,email,' . $id,
				'phone' => 'string|max:255',
				'address' => 'string|max:255',
				'address2' => 'string|max:255',
				'role' => 'in:admin,user',
				'city' => 'string|max:50',
				'state' => 'string|max:50',
				'zip' => 'string|max:10',
				'country' => 'string|max:50',
				'avatar' => 'sometimes|file|image|max:2048', // 2MB Max
			]);

			// Update user's information
			$user->name = $request->input('name', $user->name);
			$user->company = $request->input('company', $user->company);
			$user->email = $request->input('email', $user->email);
			$user->phone = $request->input('phone', $user->phone);
			$user->address = $request->input('address', $user->address);
			$user->address2 = $request->input('address2', $user->address2);
			$user->city = $request->input('city', $user->city);
			$user->state = $request->input('state', $user->state);
			$user->zip = $request->input('zip', $user->zip);
			$user->country = $request->input('country', $user->country);
			$user->role = $request->input('role', $user->role);

			$pathPrefix = env('FILE_PATH_PREFIX', '/storage/');
			// Handle avatar update if provided
			if ($request->hasFile('avatar')) {
				// Delete old avatar if it exists and isn't a default
				if ($user->photo && !str_contains($user->photo, 'default_avatar.jpg')) {
					Storage::delete($user->photo);
				}

				// Store new avatar
				$user->photo = $pathPrefix . $request->file('avatar')->store('avatars', 'public');
			}

			// Save the user
			$user->save();

			return response()->json($user, 200);
		} catch (\Throwable $th) {
			throw $th;
		}
	}


	public function getUsers(Request $request)
	{
		$query = User::query();
		$query = $query->where('is_deleted', false);
		$field = $request->input('field');
		$order = $request->input('order');
		$query->orderBy($field, $order);

		// Add filters based on query parameters
		if ($request->has('search')) {
			$search = $request->input('search');
			// Assuming you want to search across multiple properties
			$query->where(function ($query) use ($search) {
				$query->where('name', 'LIKE', "%{$search}%")
					->orWhere('email', 'LIKE', "%{$search}%")
					->orWhere('company', 'LIKE', "%{$search}%")
					->orWhere('address', 'LIKE', "%{$search}%")
					->orWhere('address2', 'LIKE', "%{$search}%")
					->orWhere('city', 'LIKE', "%{$search}%")
					->orWhere('state', 'LIKE', "%{$search}%")
					->orWhere('country', 'LIKE', "%{$search}%");
				// Add other properties you want to search by
			});
		}

		// Pagination - 10 users per page
		$users = $query->paginate(10);

		return response()->json($users);
	}

	public function stats()
	{
		try {
			$user = auth()->user();

			$stats = [];
			$users = $user->role == 'admin' ? User::where('role', 'user')->where('is_deleted', false)->get() : [];
			$admins = $user->role == 'admin' ? User::where('role', 'admin')->where('is_deleted', false)->get() : [];
			$own_companies = Entity::where('user_id', $user->id)->get();
			$other_companies = Entity::where('user_id', '!=', $user->id)->get();
			$stats['users'] = $users;
			$stats['admins'] = $admins;
			$stats['own_companies'] = $own_companies;
			$stats['other_companies'] = $other_companies;

			$query = $user->role == 'admin' ? Entity::query()->orderBy('created_at', 'desc') : Entity::where('person', $user->person)->orderBy('created_at', 'desc');
			$entities = $query->paginate(10);
			$stats['entities'] = $entities;

			return response()->json($stats);
		} catch (\Throwable $th) {
			throw $th;
		}
	}
	public function delete($id)
	{
		$user = User::findOrFail($id);
		$user->is_deleted = true;
		$user->save();

		return response()->json(['message' => 'User marked as deleted successfully']);
	}
}
