<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\PlaceSearch;

final class HomeController extends Controller
{
    public function index(): void
    {
        $placeSearch = new PlaceSearch();

        $viewData = [
            'title' => 'Khám phá Việt Nam',
            'places' => $placeSearch->findApprovedForHome(),
            'categories' => $placeSearch->findActiveCategories(),
        ];

        $this->render('home/index', $viewData);
    }
}
