<?php

namespace App\Controller;

use App\Request\ProductCreateRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Mall\Common\AppResponse;

#[Controller(prefix: '/product')]
class ProductController
{

    #[PostMapping(path: "create")]
    public function create(ProductCreateRequest $request)
    {
        $data = $request->validated();
        return AppResponse::ok("ok");
    }

}