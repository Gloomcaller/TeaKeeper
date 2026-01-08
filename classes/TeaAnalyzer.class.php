<?php
class TeaAnalyzer
{
    private $teas = [];
    private $year;

    public function __construct($teas_data, $year = 2025)
    {
        $this->teas = $teas_data;
        $this->year = $year;
    }
    public function getBrandStats()
    {
        $brands = [];

        foreach ($this->teas as $tea) {
            $brand = $tea['brand'];
            if (!isset($brands[$brand])) {
                $brands[$brand] = 0;
            }
            $brands[$brand]++;
        }

        arsort($brands);
        return $brands;
    }
    public function getFlavorStats()
    {
        $flavors = [];

        foreach ($this->teas as $tea) {
            $flavor = $tea['flavor'];
            if (empty($flavor)) {
                $flavor = 'Unknown';
            }

            if (!isset($flavors[$flavor])) {
                $flavors[$flavor] = 0;
            }
            $flavors[$flavor]++;
        }

        arsort($flavors);
        return $flavors;
    }
    public function getDailyAverages()
    {
        $days_with_tea = [];

        foreach ($this->teas as $tea) {
            $date = $tea['drink_date'];
            if (!isset($days_with_tea[$date])) {
                $days_with_tea[$date] = 0;
            }
            $days_with_tea[$date]++;
        }

        $total_days = count($days_with_tea);
        $total_teas = count($this->teas);

        return [
            'total_days' => $total_days,
            'total_teas' => $total_teas,
            'avg_per_day' => $total_days > 0 ? round($total_teas / $total_days, 2) : 0
        ];
    }
    public function getMostPopularDay()
    {
        $day_counts = [];

        foreach ($this->teas as $tea) {
            $date = $tea['drink_date'];
            if (!isset($day_counts[$date])) {
                $day_counts[$date] = 0;
            }
            $day_counts[$date]++;
        }

        if (empty($day_counts)) {
            return null;
        }

        arsort($day_counts);
        $most_popular = array_key_first($day_counts);

        return [
            'date' => $most_popular,
            'count' => $day_counts[$most_popular]
        ];
    }
    public function getMonthlyStats()
    {
        $months = array_fill(1, 12, 0);

        foreach ($this->teas as $tea) {
            $month = (int) date('m', strtotime($tea['drink_date']));
            if ($month >= 1 && $month <= 12) {
                $months[$month]++;
            }
        }

        return $months;
    }
    public function getTopBrands($limit = 5)
    {
        $brands = $this->getBrandStats();
        return array_slice($brands, 0, $limit, true);
    }
}
?>