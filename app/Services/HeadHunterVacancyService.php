<?php

namespace App\Services;


use App\Models\HeadHunterVacancy;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;



class HeadHunterVacancyService
{
    private $client;
    private $baseUrl = 'https://api.hh.ru/';

    private $uzbekistanCities = [
        162 => 'Ташкент',
        111 => 'Самарканд',
        4 => 'Андижан',
        99 => 'Бухара',
        115 => 'Фергана',
        107 => 'Наманган',
        114 => 'Нукус',
        102 => 'Карши',
        105 => 'Коканд',
        112 => 'Термез',
        113 => 'Ургенч',
    ];

    private $popularKeywords = [
        'разработчик', 'программист', 'менеджер', 'маркетолог', 'дизайнер',
        'бухгалтер', 'администратор', 'продавец', 'консультант', 'водитель',
        'оператор', 'инженер', 'учитель', 'врач', 'медсестра', 'повар',
        'официант', 'курьер', 'грузчик', 'охранник', 'секретарь', 'логист'
    ];

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Linux; Ubuntu 24.04) AppleWebKit/537.36 Chrome/123.0.6312.86 Safari/537.36',
            ]
        ]);
    }

    public function runAutoSync()
    {
        $results = [];
        $totalSaved = 0;
        Log::info('Starting HH auto sync for Uzbekistan');

        foreach ($this->uzbekistanCities as $cityId => $cityName)
        {
            try {
                Log::info("Syncing vacancies for {$cityName}");

                foreach ($this->popularKeywords as $keyword)
                {
                    $saved = $this->syncCityVacancies($cityId, $cityName, $keyword);
                    $totalSaved += $saved;
                    sleep(1);
                }
                $results[$cityName] = 'success';
            } catch (\Exception $e) {
                Log::error("Error syncing {$cityName}: " . $e->getMessage());
                $results[$cityName] = 'error: ' . $e->getMessage();
            }
        }
        $checked = $this->checkExistingVacancies();
        Log::info("HH auto sync completed. Saved: {$totalSaved}, Checked: {$checked}");
        return [
            'saved' => $totalSaved,
            'checked' => $checked,
            'cities' => $results
        ];
    }

    private function syncCityVacancies(int $cityId, string $cityName, string $keyword = null)
    {
        $params = [
            'area' => $cityId,
            'per_page' => 100,
            'page' => 0,
            'order_by' => 'publication_time',
        ];

        if ($keyword) {
            $params['text'] = $keyword;
        }

        $vacanciesData = $this->getVacancies($params);

        for ($page = 0; $page < $vacanciesData['pages']; $page++) {
            $params['page'] = $page;
            $pageData = $this->getVacancies($params);
        }

        if (!$vacanciesData || !isset($vacanciesData['items'])) {
            return 0;
        }

        $saved = 0;
        foreach ($vacanciesData['items'] as $item) {
            if ($this->saveOrUpdateVacancy($item, $cityName)) {
                $saved++;
            }
        }

        return $saved;
    }

    private function saveOrUpdateVacancy(array $item, string $cityName): bool
    {
        try {
            if (!isset($item['id']) || empty($item['id'])) {
                Log::warning('⚠️ Skipping HH vacancy with missing ID', ['item' => $item]);
                return false;
            }

            $salary = isset($item['salary']) ? [
                'from' => $item['salary']['from'] ?? null,
                'to' => $item['salary']['to'] ?? null,
                'currency' => $item['salary']['currency'] ?? null,
                'gross' => $item['salary']['gross'] ?? false,
            ] : null;

            return HeadHunterVacancy::updateOrCreate(
                ['hh_id' => $item['id']],
                [
                    'name' => $item['name'] ?? 'Без названия',
                    'description' => $this->cleanDescription($item['snippet']['responsibility'] ?? ''),
                    'salary' => $salary,
                    'employer_name' => $item['employer']['name'] ?? '',
                    'employer_info' => $item['employer'] ?? [],
                    'area_name' => $cityName,
                    'experience' => $item['experience'] ?? null,
                    'employment' => $item['employment'] ?? null,
                    'schedule' => $item['schedule'] ?? null,
                    'url' => $item['alternate_url'] ?? '',
                    'published_at' => $item['published_at'] ?? now(),
                    'raw_data' => $item,
                    'hh_status' => 'active',
                    'last_checked_at' => now(),
                    'check_attempts' => 0,
                    'check_error' => null,
                ]
            )->wasRecentlyCreated;

        } catch (\Exception $e) {
            Log::error('Error saving HH vacancy: ' . $e->getMessage());
            return false;
        }
    }

    public function checkExistingVacancies(): int
    {
        $vacanciesToCheck = HeadHunterVacancy::active()
            ->needsChecking()
            ->limit(100) // Проверяем по 100 за раз
            ->get();

        $checked = 0;
        $closed = 0;

        foreach ($vacanciesToCheck as $vacancy) {
            try {
                $isActive = $this->checkVacancyStatus($vacancy->hh_id);

                if ($isActive) {
                    $vacancy->markAsActive();
                } else {
                    $vacancy->markAsClosed();
                    $closed++;
                }

                $checked++;

                // Пауза между проверками
                usleep(500000); // 0.5 секунды

            } catch (\Exception $e) {
                $vacancy->recordCheckError($e->getMessage());
                Log::error("Error checking vacancy {$vacancy->hh_id}: " . $e->getMessage());
            }
        }

        if ($closed > 0) {
            Log::info("Closed {$closed} vacancies during status check");
        }

        return $checked;
    }

    private function checkVacancyStatus(string $hhId): bool
    {
        try {
            $response = $this->client->get("vacancies/{$hhId}", [
                'timeout' => 10
            ]);

            $data = json_decode($response->getBody(), true);

            // Если вакансия найдена и активна
            return isset($data['id']) && !isset($data['archived']);

        } catch (\Exception $e) {
            // Если вакансия не найдена (404) - значит закрыта
            if ($e->getCode() === 404) {
                return false;
            }
            throw $e;
        }
    }

    private function getVacancies(array $params = [])
    {
        try {
            $response = $this->client->get('vacancies', [
                'query' => $params
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            Log::error('HH API Error: ' . $e->getMessage());
            return null;
        }
    }

    private function cleanDescription($description)
    {
        return strip_tags($description);
    }

    public function getStats(): array
    {
        return [
            'total' => HeadHunterVacancy::count(),
            'active' => HeadHunterVacancy::active()->count(),
            'closed' => HeadHunterVacancy::closed()->count(),
            'approved' => HeadHunterVacancy::approved()->count(),
            'ready' => HeadHunterVacancy::readyForPublication()->count(),
            'needs_checking' => HeadHunterVacancy::active()->needsChecking()->count(),
        ];
    }

    public function getAreas()
    {
        try {

        } catch(\Exception $e) {
            Log::error('HH Areas API Error: ' . $e->getMessage());
            return null;
        }
    }

    public function syncVacancies($areaId = 162, $keywords = null)
    {

    }

    private function saveVacancy($item)
    {

    }

    private function convertExperience($hhExperience)
    {

    }

    public function getVacanciesForPublication($limit = 10)
    {

    }
}































