<?php

namespace App\Service;

class MovieGeneratorService
{
    private array $localeCache = [];
    private string $localesDir;

    public const LOCALES = [
        'en_US' => 'English (USA)',
        'de_DE' => 'German (Germany)',
        'uk_UA' => 'Ukrainian (Ukraine)',
        'bn_BD' => 'Bengali (Bangladesh)',
    ];

    public function __construct()
    {
        $dir = dirname(__DIR__, 2);
        $this->localesDir = $dir . '/config/locales';
    }

    public function generateBatch(string $seed, string $locale, float $avgLikes, float $avgReviews, int $page = 1, int $perPage = 10): array
    {
        if (!array_key_exists($locale, self::LOCALES)) {
            $locale = 'en_US';
        }

        $movies = [];
        $startIndex = ($page - 1) * $perPage + 1;

        for ($i = 0; $i < $perPage; $i++) {
            $seqIndex = $startIndex + $i;
            $movies[] = $this->generateMovie($seed, $locale, $seqIndex, $avgLikes, $avgReviews);
        }

        return [
            'movies' => $movies,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => 1000,
        ];
    }

    public function generateMovie(string $userSeed, string $locale, int $seqIndex, float $avgLikes, float $avgReviews): array
    {
        $localeData = $this->loadLocaleData($locale);
        $coreSeedStr = "{$userSeed}_{$locale}_{$seqIndex}";
        $coreHash = md5($coreSeedStr);

        $rng = function (int $offset, int $min, int $max) use ($coreHash): int {
            $slice = substr($coreHash, ($offset * 2) % 24, 8);
            $val = hexdec($slice);
            return $min + ($val % ($max - $min + 1));
        };

        $rngFloat = function (string $key) use ($userSeed, $seqIndex): float {
            $hash = md5("{$userSeed}_{$seqIndex}_{$key}");
            $val = hexdec(substr($hash, 0, 8));
            return $val / 4294967295.0;
        };

        $year = $rng(1, 1985, 2026);
        $isSeries = ($rng(15, 0, 10) > 5);
        $seasonsCount = $isSeries ? $rng(16, 1, 6) : 0;
        $mediaType = $isSeries ? "TV Series" : "Movie";
        $duration = $isSeries ? "{$seasonsCount} Seasons" : ($rng(2, 90, 168) . ' min');

        $rating = number_format(6.5 + ($rng(3, 0, 32) / 10.0), 1);
        $ageRating = ['PG-13', '13+', '16+', 'R', '18+'][$rng(4, 0, 4)];
        $isTop10 = ($rng(5, 0, 10) > 6);

        $genre = $this->pickRandom($localeData['genres'], $rng(6, 0, 100));
        $title = $this->generateTitle($localeData, $coreHash);
        $actors = $this->generateActors($localeData, $coreHash, 3);
        $director = $this->pickRandom($localeData['directors'], $rng(7, 0, 100));
        $synopsis = $this->pickRandom($localeData['synopses'], $rng(8, 0, 100));
        $company = $this->pickRandom($localeData['companies'], $rng(9, 0, 100));

        $trailerSpec = $this->buildTrailerSpec($title, $localeData, $rng);

        $likesCount = $this->calculateProbabilisticCount($avgLikes, $rngFloat('likes_prob'));
        $likesUsers = $this->generateLikesUsers($localeData, $userSeed, $seqIndex, $likesCount);

        $reviewsCount = $this->calculateProbabilisticCount($avgReviews, $rngFloat('reviews_prob'));
        $reviews = $this->generateReviews($localeData, $userSeed, $seqIndex, $reviewsCount);

        return [
            'id' => $seqIndex,
            'index' => $seqIndex,
            'title' => $title,
            'year' => $year,
            'media_type' => $mediaType,
            'is_series' => $isSeries,
            'seasons_count' => $seasonsCount,
            'duration' => $duration,
            'rating' => $rating,
            'age_rating' => $ageRating,
            'is_top10' => $isTop10,
            'genre' => $genre,
            'actors' => $actors,
            'director' => $director,
            'synopsis' => $synopsis,
            'company' => $company,
            'trailer' => $trailerSpec,
            'likes_count' => $likesCount,
            'likes_users' => $likesUsers,
            'reviews_count' => $reviewsCount,
            'reviews' => $reviews,
        ];
    }

    private function calculateProbabilisticCount(float $avg, float $randomVal): int
    {
        $base = (int) floor($avg);
        $fraction = $avg - $base;
        return $base + ($randomVal < $fraction ? 1 : 0);
    }

    private function loadLocaleData(string $locale): array
    {
        if (isset($this->localeCache[$locale])) {
            return $this->localeCache[$locale];
        }

        $filePath = $this->localesDir . '/' . $locale . '.json';
        if (!file_exists($filePath)) {
            $filePath = $this->localesDir . '/en_US.json';
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true) ?: [];
        $this->localeCache[$locale] = $data;

        return $data;
    }

    private function pickRandom(array $items, int $index): string
    {
        if (empty($items)) {
            return '';
        }
        return $items[$index % count($items)];
    }

    private function generateTitle(array $localeData, string $hash): string
    {
        $v1 = hexdec(substr($hash, 0, 4));
        $v2 = hexdec(substr($hash, 4, 4));

        $adjectives = $localeData['adjectives'] ?? ['The Dark'];
        $nouns = $localeData['nouns'] ?? ['Protocol'];
        $suffixes = $localeData['suffixes'] ?? ['Rising'];

        $adj = $adjectives[$v1 % count($adjectives)];
        $noun = $nouns[$v2 % count($nouns)];

        if ($v1 % 4 === 0) {
            $suf = $suffixes[($v1 + $v2) % count($suffixes)];
            return "{$adj} {$noun}: {$suf}";
        }

        return "{$adj} {$noun}";
    }

    private function generateActors(array $localeData, string $hash, int $count = 3): array
    {
        $firstNames = $localeData['first_names'] ?? ['John'];
        $lastNames = $localeData['last_names'] ?? ['Smith'];

        $actors = [];
        for ($i = 0; $i < $count; $i++) {
            $v1 = hexdec(substr($hash, ($i * 4) % 24, 4));
            $v2 = hexdec(substr($hash, (($i + 1) * 4) % 24, 4));

            $fn = $firstNames[$v1 % count($firstNames)];
            $ln = $lastNames[$v2 % count($lastNames)];
            $actors[] = "{$fn} {$ln}";
        }

        return $actors;
    }

    private function generateLikesUsers(array $localeData, string $seed, int $seqIndex, int $count): array
    {
        $users = [];
        for ($i = 0; $i < min($count, 25); $i++) {
            $hash = md5("{$seed}_{$seqIndex}_like_user_{$i}");
            $actors = $this->generateActors($localeData, $hash, 1);
            $users[] = $actors[0];
        }
        return $users;
    }

    private function generateReviews(array $localeData, string $seed, int $seqIndex, int $count): array
    {
        $reviews = [];
        $comments = $localeData['review_comments'] ?? ['Great movie!'];
        $companies = $localeData['companies'] ?? ['Studio Inc'];

        for ($i = 0; $i < min($count, 20); $i++) {
            $hash = md5("{$seed}_{$seqIndex}_review_{$i}");
            $v = hexdec(substr($hash, 0, 4));
            $v2 = hexdec(substr($hash, 4, 4));

            $author = $this->generateActors($localeData, $hash, 1)[0];
            $company = $companies[$v2 % count($companies)];
            $comment = $comments[$v % count($comments)];
            $rating = 3 + ($v % 3);

            $reviews[] = [
                'id' => "{$seqIndex}_{$i}",
                'author' => $author,
                'company' => $company,
                'rating' => $rating,
                'date' => date('M d, Y', strtotime("-{$i} days")),
                'comment' => $comment,
            ];
        }

        return $reviews;
    }

    private function buildTrailerSpec(string $title, array $localeData, callable $rng): array
    {
        $videos = $localeData['sample_videos'] ?? [];
        $posters = $localeData['sample_posters'] ?? [];
        $duration = $rng(12, 5, 8);
        $rawUrl = count($videos) > 0 ? $videos[$rng(10, 0, count($videos) - 1)] : '';

        return [
            'title' => $title,
            'video_url' => $rawUrl ? $rawUrl . '#t=0,' . $duration : '',
            'poster_url' => count($posters) > 0 ? $posters[$rng(11, 0, count($posters) - 1)] : '',
            'duration' => $duration,
        ];
    }
}
