<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class UserController extends Controller
{

    function user_registration(Request $request) {
        try {
            // Validation
            $request->validate([
                'name' => 'required|string|max:30',
                'email' => 'required|string|email|max:30|unique:users,email',
                'mobile' => 'required|string|max:30|unique:users,mobile',
                'password' => 'required|string|max:30',
            ]);
    
            // User creation
            User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'password' => Hash::make($request->input('password')),
            ]);
    
            // Success response
            return response()->json([
                'status' => 'success',
                'message' => 'User signed up successfully!',
            ]);
        } catch (Exception $e) {
            // Failure response
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ]);
        }
    }
    


    function user_login(Request $request) {
        try {
            // Validation
            $request->validate([
                'email' => 'required|string|email|max:30',
                'password' => 'required|string|max:30',
            ]);
    
            // Find user by email
            $user = User::where('email', $request->input('email'))->first();
            
            // Check if user exists and password matches
            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid email or password',
                ]);
            }
    
            // Generate a token for the user
            $token = $user->createToken('authToken')->plainTextToken;
    
            // Success response
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'token' => $token,
            ]);
        } catch (Exception $e) {
            // Failure response
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ]);
        }
    }

    

    // = user profile

    function user_profile(Request $request){
        return Auth::user();
    }

    function user_logout(Request $request){
        $request->user()->tokens()->delete();
        return redirect('/');
    }

    // function user_logout(Request $request) {
    //     // Delete all tokens for the authenticated user
    //     $request->user()->tokens()->delete();
    
    //     // Return success response
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Logged out successfully',
    //     ]);
    // }
    

    function update_profile(Request $request){
        try{
            $request->validate([
                'name' => 'required|string|max:30',
                'email' => 'required|string|email|max:30',
                'mobile' => 'required|string|max:30',
            ]);

            User::where('id', '=', Auth::id())->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'profile update successfully !',
            ]);
        }
        catch(Exception $e){
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ]);
        }
    }


    function otp(Request $request) {
        try {
            // Validate the email field
            $request->validate([
                'email' => 'required|string|email|max:30',
            ]);
    
            // Get the email from the request
            $email = $request->input('email');
    
            // Check if the user with the provided email exists
            $user = User::where('email', $email)->first();
    
            if (!$user) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Email not found',
                ]);
            }
    
            // Generate the OTP (One-Time Password)
            $otp = rand(100000, 999999);
    
            // Send the OTP via email
            Mail::to($email)->send(new OtpMail($otp));
    
            // Update the user with the new OTP
            $user->update([
                'otp' => $otp,
            ]);
    
          
            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully',
            ]);
    
        } catch (Exception $e) {
          
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ]);
        }
    }



    // ==== verify Otp =======//
    function otp_verify(Request $request){
        try{
            $request->validate([
                'email' => 'required|string|email|max:30',
                'otp'=>'required|string|min:6',
            ]);
            $email = $request->input('email');
            $otp = $request->input('otp');
            $user = User::where('email', '=', $request->input('email'))->where('otp', '=', $request->input('otp'))->first();
            if(!$user){
                return response()->json([
                    'status'=>'fail',
                    'message'=> 'Unauthorized user !'
                ]);
            }
            $user->update([
                'otp' => '0',
            ]);
            $token = $user->createToken('authToken')->plainTextToken;
    
            // Success response
            return response()->json([
                'status' => 'success',
                'message' => 'Otp Verify successful !',
                'token' => $token,
            ]);


        }
        catch(Exception $e){
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
               
            ]);
        }
    }


    function reset_password(Request $request) {
        try {
            // Validate the password and confirmation
            $request->validate([
                'password' => 'required|string|min:6', // Confirms password_confirmation
            ]);
    
            // Get the authenticated user's ID
            $id = Auth::id();
            
            // Ensure the user is authenticated
            if (!$id) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User not authenticated',
                ], 401);
            }
    
         
            $password = $request->input('password');
    
            // Update the user's password
            User::where('id', '=', $id)->update([
                'password' => Hash::make($password),
            ]);
    
            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully!',
            ]);
    
        } catch (\Throwable $e) {
            // Handle any errors
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    
}
