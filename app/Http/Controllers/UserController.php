<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\AuthorizesRole;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    use AuthorizesRole;
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'created_at')->get();
        return response()->json(["data" => $users]);
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string|in:User,Super Admin',
                'addresses' => 'required|array',
                'addresses.*.address_line' => 'required|string',
                'addresses.*.city' => 'required|string',
                'addresses.*.state' => 'required|string',
                'addresses.*.zip_code' => 'required|string',
                'addresses.*.checkpoint' => 'boolean',
            ]);

            // Hash the password before saving
            $validatedData['password'] = bcrypt($validatedData['password']);

            // Create the user
            $user = User::create($validatedData);

            // Save each address
            foreach ($request->addresses as $address) {
                $user->addresses()->create($address);
            }

            // Return the user with addresses
            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user->load('addresses')
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getUserDetails($id)
    {
        $user = User::with('addresses')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user], 200);
    }
    public function updateUser(Request $request, $id)
    {
        $this->authorizeRole('Super Admin');
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|string|min:8|confirmed',
                'role' => 'sometimes|string|in:User,Super Admin',
                'addresses' => 'sometimes|array',
                'addresses.*.id' => 'sometimes|exists:addresses,id',
                'addresses.*.address_line' => 'required_with:addresses|string',
                'addresses.*.city' => 'required_with:addresses|string',
                'addresses.*.state' => 'required_with:addresses|string',
                'addresses.*.zip_code' => 'required_with:addresses|string',
                'addresses.*.checkpoint' => 'boolean',
            ]);

            // Update user fields
            if ($request->has('name')) $user->name = $validatedData['name'];
            if ($request->has('email')) $user->email = $validatedData['email'];
            if ($request->has('password')) $user->password = Hash::make($validatedData['password']);
            if ($request->has('role')) $user->role = $validatedData['role'];

            $user->save();

            // Update addresses if provided
            if ($request->has('addresses')) {
                $addresses = $request->input('addresses');

                // Iterate over each address
                foreach ($addresses as $addressData) {
                    if (isset($addressData['id'])) {
                        // Update existing address
                        $address = Address::find($addressData['id']);
                        if ($address && $address->user_id == $user->id) {
                            $address->update($addressData);
                        }
                    } else {
                        // Create new address
                        $user->addresses()->create($addressData);
                    }
                }
            }

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user->load('addresses')
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function destroy(User $user)
    {
        $this->authorizeRole('Super Admin');
        // Delete all addresses associated with the user
        $user->addresses()->delete();

        // Delete the user
        $user->delete();

        return response()->json(['message' => 'Successfully Deleted'], 200);
    }


    // Refresh the JWT token
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function login(Request $request)
    {
        if ($this->validateLogin($request)->fails()) {
            return response()->json($this->validateLogin($request)->errors(), 422);
        } else {
            $token = Auth::guard('api')->attempt($this->loginCredentials($request));

            if (!$token)
                return response()->json(['error' => 'Invalid User Or Password'], 401);

            return $this->respondWithToken($token);
        }
    }
    protected function validateLogin($request)
    {
        return Validator::make($this->loginCredentials($request), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    }
    protected function respondWithToken($token)
    {
        $user = Auth::guard('api')->user();

        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                // Add any other user attributes you wish to share
            ]
        ]);
    }

    protected function loginCredentials(Request $request): array
    {
        return $request->only('email', 'password');
    }


    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Reset link sent to your email.'], 200);
        }

        return response()->json(['message' => 'Unable to send reset link.'], 500);
    }

    // Method to reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully.'], 200);
        }

        return response()->json(['message' => 'Invalid token or email.'], 500);
    }
}
