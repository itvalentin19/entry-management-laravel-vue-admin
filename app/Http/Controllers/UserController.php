<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
			\Log::info('Request data:', $request->all());

			// Validate the incoming request data
			$request->validate([
				'name' => 'string|max:255',
				'email' => 'string|email|max:255|unique:users,email,' . $user->id,
				'phone' => 'string|max:255',
				'address' => 'string|max:255',
				'role' => 'in:admin,user',
				'avatar' => 'sometimes|file|image|max:2048', // 2MB Max
			]);

			// Update user's information
			$user->name = $request->input('name', $user->name);
			$user->email = $request->input('email', $user->email);
			$user->phone = $request->input('phone', $user->phone);
			$user->address = $request->input('address', $user->address);
			$user->role = $request->input('role', $user->role);

			// Handle avatar update if provided
			if ($request->hasFile('avatar')) {
				// Delete old avatar if it exists and isn't a default
				if ($user->photo && !str_contains($user->photo, 'default_avatar.jpg')) {
					Storage::delete($user->photo);
				}

				// Store new avatar
				$user->photo = '/storage/' . $request->file('avatar')->store('avatars', 'public');
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

	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:users',
			'phone' => 'required|string|max:255',
			'address' => 'required|string|max:255',
			'avatar' => 'sometimes|file|image|max:2048',
			// 2MB Max
			'role' => 'required|in:admin,user', // Ensure role is either 'admin' or 'user'
		]);

		$user = new User($request->only(['name', 'email', 'phone', 'address', 'role']));

		if ($request->hasFile('avatar')) {
			$user->photo = '/storage/' . $request->file('avatar')->store('avatars', 'public');
		} else {
			// Set default avatar path if no avatar is uploaded
			$user->photo = '/img/default_avatar.png'; // Make sure this path is correct
		}

		$user->password = Hash::make('Password'); // Set a default password or generate one
		$user->save();

		return response()->json(new UserResource($user), 201);
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
			\Log::info('Request data:', $request->all());

			// Validate the incoming request data
			$request->validate([
				'name' => 'string|max:255',
				'email' => 'string|email|max:255|unique:users,email,' . $id,
				'phone' => 'string|max:255',
				'address' => 'string|max:255',
				'role' => 'in:admin,user',
				'avatar' => 'sometimes|file|image|max:2048', // 2MB Max
			]);

			// Update user's information
			$user->name = $request->input('name', $user->name);
			$user->email = $request->input('email', $user->email);
			$user->phone = $request->input('phone', $user->phone);
			$user->address = $request->input('address', $user->address);
			$user->role = $request->input('role', $user->role);

			// Handle avatar update if provided
			if ($request->hasFile('avatar')) {
				// Delete old avatar if it exists and isn't a default
				if ($user->photo && !str_contains($user->photo, 'default_avatar.jpg')) {
					Storage::delete($user->photo);
				}

				// Store new avatar
				$user->photo = '/storage/' . $request->file('avatar')->store('avatars', 'public');
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

		// Add filters based on query parameters
		if ($request->has('search')) {
			$search = $request->input('search');
			// Assuming you want to search across multiple properties
			$query->where(function ($query) use ($search) {
				$query->where('name', 'LIKE', "%{$search}%")
					->orWhere('email', 'LIKE', "%{$search}%");
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
			$stats['users'] = $users;
			$stats['admins'] = $admins;
			$stats['own_companies'] = [];
			$stats['other_companies'] = [];

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
