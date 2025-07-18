<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use App\Events\UserSignedUp;
use Illuminate\Support\Carbon;
use App\Models\PendingUser;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Resources\LoggedInUserResource;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use OpenApi\Attributes as OAT;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Jobs\CleanupExpiredPendingUsers;
class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  AuthService  $authService
     * @return void
     */
    public function __construct(private AuthService $authService)
    {
        //
    }

    /**
     * Signup a user.
     *
     * @param  SignupRequest  $request
     * @return JsonResponse
     */
    #[OAT\Post(
        tags: ['auth'],
        path: '/api/signup',
        summary: 'Signup a user',
        operationId: 'AuthController.signup',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/SignupRequest')

        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_CREATED,
                description: 'Created',
                content: new OAT\JsonContent(ref: '#/components/schemas/LoggedInUserResource')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Unprocessable entity',
                content: new OAT\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function signup(SignupRequest $request): JsonResponse
    {
        $request->validate([
            'userName' => 'required|string|unique:pending_users,userName|unique:users,userName',
            'email' => 'required|email|unique:pending_users,email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $email = $request->email; 
        $verificationCode = rand(100000, 999999); 

        PendingUser::create([ //lưu tt tạm vào bảng pending-user
            'userName' => $request->userName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_code' => $verificationCode,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Gửi email xác nhận
        Mail::send('emails.verify-code', ['code' => $verificationCode], function ($message) use ($email) {
            $message->to($email)->subject('Mã xác minh đăng ký tài khoản');
        });
        CleanupExpiredPendingUsers::dispatch($request->email)->delay(now()->addMinutes(10));
        return response()->json(['message' => 'Mã xác nhận đã được gửi tới email.']);
    }
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);
        // kiểm tra mã xác minh 
        $pending = PendingUser::where('email', $request->email)
                    ->where('verification_code', $request->code)
                    ->where('expires_at', '>=', Carbon::now())
                    ->first();

        if (!$pending) {
            return response()->json(['message' => 'Mã xác nhận không đúng hoặc đã hết hạn'], 400);
        }

        // Tạo tài khoản chính thức
        $user = User::create([
            'userName' => $pending->userName,
            'email' => $pending->email,
            'password' => $pending->password,
        ]);
        $user->roles()->attach(2); 
        
        $pending->delete();

        return response()->json(['message' => 'Tài khoản đã được xác thực và tạo thành công.']);
    }
    public function resendCode(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:pending_users,email',
    ]);

    $pendingUser = PendingUser::where('email', $request->email)->first();

    if (!$pendingUser) {
        return response()->json(['message' => 'Email không tồn tại hoặc đã được xác minh.'], 404);
    }

    // Tạo mã mới
    $verificationCode = rand(100000, 999999);

    // Cập nhật mã và hạn
    $pendingUser->update([
        'verification_code' => $verificationCode,
        'expires_at' => Carbon::now()->addMinutes(1),
    ]);

    // Gửi lại email
    Mail::send('emails.verify-code', ['code' => $verificationCode], function ($message) use ($request) {
        $message->to($request->email)->subject('Mã xác minh đăng ký tài khoản (Gửi lại)');
    });

    return response()->json(['message' => 'Mã xác nhận mới đã được gửi đến email.']);
}
public function cancelPendingRegistration(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    $pendingUser = PendingUser::where('email', $request->email)->first();

    if (!$pendingUser) {
        return response()->json(['message' => 'Không tìm thấy yêu cầu đăng ký đang chờ.'], 404);
    }

    $pendingUser->delete();

    return response()->json(['message' => 'Yêu cầu đăng ký đã được huỷ.']);
}

    /**
     * Login a user.
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    #[OAT\Post(
        tags: ['auth'],
        path: '/api/login',
        summary: 'Login a user',
        operationId: 'AuthController.login',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/LoginRequest')

        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'Ok',
                content: new OAT\JsonContent(ref: '#/components/schemas/LoggedInUserResource')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Unprocessable entity',
                content: new OAT\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthorized',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Invalid credentials.'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->loginUser($request);

        return Response::json(new LoggedInUserResource($user));
    }


    #[OAT\Get(
        path: '/api/users',
        summary: 'List all users',
        tags: ['User'],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'List of users', content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/User')))
        ]
    )]
    public function index()
    {
        return UserResource::collection(User::all());
    }

    #[OAT\Get(
        path: '/api/users/{id}',
        summary: 'Get user by ID',
        tags: ['User'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'User detail', content: new OAT\JsonContent(ref: '#/components/schemas/User')),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'User not found')
        ]
    )]
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        return new UserResource($user);
    }

    #[OAT\Post(
        path: '/api/users',
        summary: 'Create new user',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/StoreUserRequest')
        ),
        tags: ['User'],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_CREATED, description: 'User created', content: new OAT\JsonContent(ref: '#/components/schemas/User'))
        ]
    )]
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['createTime'] = now();
        $user = User::create($data);
        return new UserResource($user);
    }

    #[OAT\Put(
        path: '/api/users/{id}',
        summary: 'Update user',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/UpdateUserRequest')
        ),
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        tags: ['User'],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'User updated', content: new OAT\JsonContent(ref: '#/components/schemas/User')),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'User not found')
        ]
    )]
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $data['lastUpdateTime'] = now();
        $user->update($data);
        return new UserResource($user);
    }

    #[OAT\Delete(
        path: '/api/users/{id}',
        summary: 'Delete user',
        tags: ['User'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_NO_CONTENT, description: 'User deleted'),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'User not found')
        ]
    )]
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $user->delete();
        return response()->json(null, 204);
    }
    // /**
    //  * Logout a user.
    //  *
    //  * @param  Request  $request
    //  * @return JsonResponse
    //  */
    // #[OAT\Post(
    //     tags: ['auth'],
    //     path: '/api/logout',
    //     summary: 'Logout a user',
    //     operationId: 'AuthController.logout',
    //     security: [['BearerToken' => []]],
    //     responses: [
    //         new OAT\Response(
    //             response: HttpResponse::HTTP_NO_CONTENT,
    //             description: 'No content'
    //         ),
    //     ]
    // )]
    // public function logout(Request $request): JsonResponse
    // {
    //     $this->authService->logoutUser($request->user());

    //     return Response::json(null, HttpResponse::HTTP_NO_CONTENT);
    // }
}
