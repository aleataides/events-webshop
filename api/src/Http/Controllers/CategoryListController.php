<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CategoryListController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function __invoke(Request $request): Response
    {
        return $this->json(['data' => $this->categoryService->listPublished($this->affiliate($request))]);
    }
}
