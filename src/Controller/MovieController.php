<?php

namespace App\Controller;

use App\Service\InertiaService;
use App\Service\MovieGeneratorService;
use App\Service\ZipBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class MovieController extends AbstractController
{
    public function __construct(
        private InertiaService $inertia,
        private MovieGeneratorService $movieGenerator
    ) {
    }

    #[Route('/', name: 'app_movie_store', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $seed = (string) $request->query->get('seed', '58933423');
        $locale = (string) $request->query->get('locale', 'en_US');
        $likes = (float) $request->query->get('likes', 3.7);
        $reviews = (float) $request->query->get('reviews', 2.3);
        $page = max(1, (int) $request->query->get('page', 1));
        $view = (string) $request->query->get('view', 'table');

        $likes = max(0.0, min(10.0, $likes));
        $reviews = max(0.0, min(10.0, $reviews));

        $batchData = $this->movieGenerator->generateBatch($seed, $locale, $likes, $reviews, $page, 10);

        return $this->inertia->render('MovieStore/Index', [
            'seed' => $seed,
            'locale' => $locale,
            'likes' => $likes,
            'reviews' => $reviews,
            'page' => $page,
            'view' => $view,
            'locales' => MovieGeneratorService::LOCALES,
            'batch' => $batchData,
        ]);
    }

    #[Route('/api/movies', name: 'app_movie_batch', methods: ['GET'])]
    public function batch(Request $request): JsonResponse
    {
        $seed = (string) $request->query->get('seed', '58933423');
        $locale = (string) $request->query->get('locale', 'en_US');
        $likes = (float) $request->query->get('likes', 3.7);
        $reviews = (float) $request->query->get('reviews', 2.3);
        $page = max(1, (int) $request->query->get('page', 1));

        $likes = max(0.0, min(10.0, $likes));
        $reviews = max(0.0, min(10.0, $reviews));

        $batchData = $this->movieGenerator->generateBatch($seed, $locale, $likes, $reviews, $page, 10);

        return new JsonResponse($batchData);
    }

    #[Route('/export', name: 'app_movie_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        $seed = (string) $request->query->get('seed', '58933423');
        $locale = (string) $request->query->get('locale', 'en_US');
        $likes = (float) $request->query->get('likes', 3.7);
        $reviews = (float) $request->query->get('reviews', 2.3);
        $page = max(1, (int) $request->query->get('page', 1));

        $batchData = $this->movieGenerator->generateBatch($seed, $locale, $likes, $reviews, $page, 10);

        $zipBuilder = new ZipBuilder();

        $jsonContent = json_encode($batchData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $zipBuilder->addFile("movies_page_{$page}.json", $jsonContent);

        foreach ($batchData['movies'] as $movie) {
            $safeTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $movie['title']);
            $txt = "=== MOVIE SHOWCASE SUMMARY ===\n";
            $txt .= "Index: {$movie['index']}\n";
            $txt .= "Title: {$movie['title']}\n";
            $txt .= "Year: {$movie['year']}\n";
            $txt .= "Genre: {$movie['genre']}\n";
            $txt .= "Duration: {$movie['duration']}\n";
            $txt .= "Director: {$movie['director']}\n";
            $txt .= "Cast: " . implode(', ', $movie['actors']) . "\n";
            $txt .= "Synopsis: {$movie['synopsis']}\n";
            $txt .= "Likes Count: {$movie['likes_count']}\n";
            $txt .= "Reviews Count: {$movie['reviews_count']}\n\n";

            if (!empty($movie['reviews'])) {
                $txt .= "=== AUDIENCE REVIEWS ===\n";
                foreach ($movie['reviews'] as $r) {
                    $txt .= "- [Rating: {$r['rating']} Stars] {$r['author']} ({$r['company']}): \"{$r['comment']}\"\n";
                }
            }

            $zipBuilder->addFile("trailers/{$movie['index']}_{$safeTitle}.txt", $txt);
        }

        $zipContent = $zipBuilder->getZipContent();

        $response = new Response($zipContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "movies_export_seed_{$seed}_p{$page}.zip"
        );

        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
