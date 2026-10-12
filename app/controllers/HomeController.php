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
        $places = $placeSearch->findApprovedForHome();

        $viewData = [
            'title' => 'Khám phá địa điểm',
            'places' => $places,
        ];

        $this->render('home/index', $viewData);
    }
}
