<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Affiliate;
use App\Services\CategoryService;
use Psr\Http\Message\ResponseInterface as Response;

final class CategoryListController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function __invoke(Affiliate $affiliate): Response
    {
        return $this->json(['data' => $this->categoryService->listPublished($affiliate)]);
    }
}
