<?php

class CorsMiddleware
{
    public function handle()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH");
        header("Access-Control-Allow-Headers: Content-Type");
        return true;
    }
}
