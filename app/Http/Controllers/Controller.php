<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(title: "Laravel API", version: "1.0")]
#[OA\Components(securitySchemes: [
    new OA\SecurityScheme(securityScheme: "bearerAuth", type: "http", scheme: "bearer")
])]

#[OA\Schema(schema: "Success", properties: [
    new OA\Property(property: "status", type:"integer", example:200),
    new OA\Property(property: "message", type: "string", example: "string")
])]
#[OA\Schema(schema: "Error", properties: [
    new OA\Property(property: "status", type:"integer", example:400),
    new OA\Property(property: "message", type: "string", example: "string")
])]
#[OA\Schema(schema: "Unauthorized", properties: [
    new OA\Property(property: "status", type:"integer", example:401),
    new OA\Property(property: "message", type: "string", example: "string")
])]
#[OA\Schema(schema: "NotFoundError", properties: [
    new OA\Property(property: "status", type:"integer", example:404),
    new OA\Property(property: "message", type: "string", example: "string")
])]
#[OA\Schema(schema: "ValidationError", properties: [
    new OA\Property(property: "message", type: "string", example: "string"),
    new OA\Property(property: "errors", type:"object", example:"{}")
])]
#[OA\Schema(schema: "ServerError", properties: [
    new OA\Property(property: "status", type:"integer", example:500),
    new OA\Property(property: "message", type: "string", example: "string")
])]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
