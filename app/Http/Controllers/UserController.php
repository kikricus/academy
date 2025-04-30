<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use \Tinify\Tinify;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserIndexRequest;
class UserController extends Controller
{
    public function index(UserIndexRequest $request)
    {
        $count = $request->input('count', 6);
        $page = $request->input('page', 1);
        $users = User::with('position')->paginate($count);

        return response()->json([
            "success" => true,
            "page" => $page,
            "total_pages" => $users->lastPage(),
            "total_users" => $users->total(),
            "count" => $count,
            "links" => [
                "next_url" => $users->nextPageUrl() . "&count=" . $count,
                "prev_url" => $users->previousPageUrl() . "&count=" . $count,
            ],
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'position' => $user->position->name,
                    'position_id' => $user->position->id,
                    "registration_timestamp" => Carbon::parse($user->created_at)->timestamp,
                    'photo' => $user->photo,
                ];
            })
        ]);
    }

    public function show($id)
    {
        if (!is_numeric($id) || intval($id) != $id) {
            return response()->json([
                'success' => false,
                'message' => 'The user ID must be an integer.',
            ], 400);
        }

        $user = User::with('position')->find($id);
        if (!$user){
            return response()->json([
               'success'=>false,
                'message'=>"User not found"
            ],404);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'position' => $user->position->name,
                'position_id' => $user->position_id,
                'photo' => $user->photo,
            ],
        ]);
    }

    public function store(StoreUserRequest $request)
    {

        if (!$request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'The token expired.'
            ], 401);
        }

        $userExist = User::where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->first();


        if ($userExist) {
            return response()->json([
                'success' => false,
                'message' => 'User with this phone or email already exist',
            ], 409);
        }


        $croppedPath = null;
        if ($request->hasFile('photo')) {


            $image = Image::make($request->file('photo'))
                ->fit(70, 70, null, 'center')
                ->encode('jpg', 100);

            $croppedPath = 'cropped/' . uniqid() . '.jpg';

            $fullLocalPath = storage_path('app/public/' . $croppedPath);

            if (!file_exists(dirname($fullLocalPath))) {
                mkdir(dirname($fullLocalPath), 0755, true);
            }

            $image->save($fullLocalPath);


            \Tinify\setKey(env('TINIFY_API_KEY'));
            $source = \Tinify\fromFile($fullLocalPath);
            $source->toFile($fullLocalPath);

        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'position_id' => $request->input('position_id'),
            'photo' => 'https://academy.webinfo.org.ua/storage/' . $croppedPath,
            'password' => Hash::make($request->input('password')),
        ]);
        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'message' => 'New user successfully registered',
        ], 201);
    }

    public function token(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Неверный логин или пароль'
            ], 401);
        }

        $token = $user->createToken('User Token')->plainTextToken;

        return response()->json([
            'success'=>true,
            'token' => $token,
        ]);
    }
}

