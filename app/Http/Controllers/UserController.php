<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Register;
use App\Http\Requests\UpdateProfile;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function register(Register $request)
    {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                // Store the image and add the path to the data array
                $data['image'] = $request->file('image')->store('user_image');
            } else {
                $data['image'] = null;
            }
            $user = User::create($data);

        return ResponseHelper::jsonResponse(UserResource::make($user), 'registered successfully', 200, true);

    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $user = User::where('mobile',$request->mobile)->first();

        if(!$user){
            return ResponseHelper::jsonResponse(null,'user not found',404,true);
        }

        $token = JWTAuth::fromUser($user);

        return ResponseHelper::jsonResponse($token, 'logged in successfully', 200, true);
    }

    public function updateProfile(UpdateProfile $request,User $user){
        $data = $request->validated();
        if ($request->hasFile('image')) {
            if (!empty($user->image) && Storage::exists($user->image)) {
                Storage::delete($user->image);
            }
            $data['image']= request()->file('image')->store('user_image');
        }else{
            unset($data['image']);
        }
        $user->update($data);
        return ResponseHelper::jsonResponse(UserResource::make($user),'user updated successfully',200,true);

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user()->only('id','first_name','last_name','mobile','location','image');
//        return response()->json(auth()->user());
        return ResponseHelper::jsonResponse($user,auth()->user()->first_name.' '.auth()->user()->last_name.' profile',200,true);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return ResponseHelper::jsonResponse(null,'loggedout successfully');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 99999
        ]);
    }
}
