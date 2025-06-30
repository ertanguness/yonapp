<?php

class Route
{
    protected string $basePath = 'pages';
    protected string $defaultPage = 'home';

    public function handle(?string $requestUri): string
    {
        $requestUri = trim($requestUri ?? $this->defaultPage, '/');
        $requestUri = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $requestUri);
        return $requestUri;
    }
}
