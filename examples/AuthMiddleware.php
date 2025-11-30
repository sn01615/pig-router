<?php

class AuthMiddleware
{
    public function handle()
    {
        // 简单的身份验证示例
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo "Unauthorized";
            return false;
        }
        return true;
    }
}
