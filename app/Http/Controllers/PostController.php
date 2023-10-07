<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\PostAddRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

class PostController extends Controller
{
    const BASE_URL = '/api/v1';

    #[OA\Get(
        path: self::BASE_URL . "/posts",
        summary: "List of posts.",
        description: "It returns a list of posts.",
        tags: ["posts"],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Successful operation.",
        content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/PostItem"))
    )]
    #[OA\Schema(schema: "PostItem", properties: [
        new OA\Property(property: "id", type:"integer", example:1),
        new OA\Property(property: "title", type: "string", example: "Title"),
        new OA\Property(property: "likes", type: "integer", example: 15),
        new OA\Property(property: "updated_at", type: "string", example: "04.10.2023 07:56:21")
    ])]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    public function list(): ResourceCollection
    {
        return PostResource::collection(Post::all());
    }

    #[OA\Get(
        path: self::BASE_URL . "/posts/{id}",
        summary: "One post.",
        description: "It returns one post.",
        tags: ["posts"],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Post id."),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Successful operation.",
        content: new OA\JsonContent(ref: "#/components/schemas/PostItem")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    #[OA\Response(
        response: 404,
        description: "Post not found.",
        content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError")
    )]
    public function one(int $id): PostResource
    {
        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException) {
            return $this->postNotFoundResponse();
        }

        return PostResource::make($post);
    }

    #[OA\Post(
        path: self::BASE_URL . "/posts",
        summary: "Add post.",
        description: "It adds a new post to the DB.",
        tags: ["posts"],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/PostItemAdd")
        )
    )]
    #[OA\Schema(schema: "PostItemAdd", properties: [
        new OA\Property(property: "title", type: "string", example: "Title")
    ])]
    #[OA\Response(
        response: 200,
        description: "Successful operation.",
        content: new OA\JsonContent(ref: "#/components/schemas/PostItem")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    #[OA\Response(
        response: 422,
        description: "Validation error.",
        content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
    )]
    public function add(PostAddRequest $request): PostResource
    {
        $data = $request->validated();
        $post = Post::create($data);

        $freshPost = $post->fresh();

        return PostResource::make($freshPost);
    }

    #[OA\Patch(
        path: self::BASE_URL . "/posts/{id}",
        summary: "Update post.",
        description: "It updates the post in the DB.",
        tags: ["posts"],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Post id."),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/PostItemUpdate")
        )
    )]
    #[OA\Schema(schema: "PostItemUpdate", properties: [
        new OA\Property(property: "title", type: "string", example: "Title"),
        new OA\Property(property: "likes", type: "integer", example: 15)
    ])]
    #[OA\Response(
        response: 200,
        description: "Successful operation.",
        content: new OA\JsonContent(ref: "#/components/schemas/PostItem")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    #[OA\Response(
        response: 404,
        description: "Post not found.",
        content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError")
    )]
    public function update(PostUpdateRequest $request, int $id): PostResource
    {
        $data = $request->validated();

        try {
            $post = Post::findOrFail($id);
            $post->update($data);
        } catch (ModelNotFoundException) {
            return $this->postNotFoundResponse();
        }

        return PostResource::make($post);
    }

    #[OA\Delete(
        path: self::BASE_URL . "/posts/{id}",
        summary: "Delete post.",
        description: "API endpoint for post deleting.",
        tags: ["posts"],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Post id."),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Successful operation.",
        content: new OA\JsonContent(ref: "#/components/schemas/Success")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    #[OA\Response(
        response: 404,
        description: "Post not found.",
        content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError")
    )]
    public function delete($id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            $post->delete();
        } catch (ModelNotFoundException) {
            return $this->postNotFoundResponse();
        }

        return response()->json([
            'status' => '200',
            'message' => 'Successful operation.'
        ]);
    }

    /**
     * Prepare post Not Found response
     * 
     * @return JsonResponse
     */
    private function postNotFoundResponse(): JsonResponse
    {
        return response()->json([
            'status' => '404',
            'message' => 'Post not found.'
        ], 404);
    }
}
