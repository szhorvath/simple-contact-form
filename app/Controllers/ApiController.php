<?php

namespace App\Controllers;

class ApiController
{
    public function index($request, $response)
    {
        return $response->withJson([
            'success' => true,
            'data' => $request->getQueryParams(),
            'attrs' => $request->getAttributes(),
            'body' => $request->getParsedBody(),
            'ip' => $request->getServerParams()['REMOTE_ADDR']
        ]);
    }
}
