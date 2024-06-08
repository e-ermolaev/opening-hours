<?php

/**
 * Processes an array of working hours for a salon and returns a formatted result.
 *
 * @param array $working_hours An array of working hours for each day of the week.
 * @return array A formatted result containing today's opening and closing times and grouped working hours.
 */
function process_working_hours($working_hours) {
    // Get the current day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
    $current_day = date('w');
    // Get the current time in 'H:i' format
    $current_time = date('H:i');

    // Initialize the resulting array
    $result = [
        'today' => [],
        'working_hours' => []
    ];

    // Array to store grouped working hours
    $grouped_hours = [];
    // Array of day names
    $days_of_week = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Group working hours by opening and closing times
    foreach ($working_hours as $hours) {
        $key = $hours['opening'] . '-' . $hours['closing'];
        if (!isset($grouped_hours[$key])) {
            $grouped_hours[$key] = [];
        }
        $grouped_hours[$key][] = $hours['day'];
    }

    // Format grouped working hours
    foreach ($grouped_hours as $key => $days) {
        // Sort the days
        sort($days);
        
        // Determine consecutive groups of days
        $days_ranges = [];
        $start = $days[0];
        $prev = $days[0];

        for ($i = 1; $i < count($days); $i++) {
            if ($days[$i] == $prev + 1) {
                $prev = $days[$i];
            } else {
                $days_ranges[] = [$start, $prev];
                $start = $days[$i];
                $prev = $days[$i];
            }
        }
        $days_ranges[] = [$start, $prev];

        // Create a label for the days
        $days_label = implode(', ', array_map(function($range) use ($days_of_week) {
            if ($range[0] == $range[1]) {
                return $days_of_week[$range[0]];
            } else {
                return $days_of_week[$range[0]] . '-' . $days_of_week[$range[1]];
            }
        }, $days_ranges));

        // Split the key to get opening and closing times
        list($opening, $closing) = explode('-', $key);

        // Add to the resulting array
        $result['working_hours'][] = [
            'days_label' => $days_label,
            'opening' => $opening,
            'closing' => $closing,
            'label' => $days_label . ' from ' . $opening . ' to ' . $closing
        ];
    }

    // Find the working hours for today
    $today_hours = null;
    foreach ($working_hours as $hours) {
        if ($hours['day'] == $current_day) {
            $today_hours = $hours;
            break;
        }
    }

    // If there are working hours for today
    if ($today_hours) {
        $opening = $today_hours['opening'];
        $closing = $today_hours['closing'];
        $status = $current_time < $closing ? 'open' : 'closed';
        $label = $current_time < $opening ? 'from ' . $opening : 'until ' . $closing;
        $result['today'] = [
            'opening' => $opening,
            'closing' => $closing,
            'status' => $status,
            'label' => $label
        ];
    }

    // If the salon is closed, find the next opening time
    if ($result['today']['status'] === 'closed') {
        $next_opening = null;
        $next_opening_day = null;

        // Loop through the next 7 days to find the next opening time
        for ($i = 1; $i <= 7; $i++) {
            $check_day = ($current_day + $i) % 7;
            foreach ($working_hours as $hours) {
                if ($hours['day'] == $check_day) {
                    $next_opening = $hours['opening'];
                    $next_opening_day = $check_day;
                    break 2;
                }
            }
        }

        // If a next opening time is found, add it to the result
        if ($next_opening && $next_opening_day !== null) {
            $result['today']['next_opening'] = [
                'day' => $days_of_week[$next_opening_day],
                'time' => $next_opening
            ];
        }
    }

    return $result;
}

// Example usage of the function
$working_hours = [
    ['day' => 1, 'opening' => '08:00', 'closing' => '21:00'],
    ['day' => 2, 'opening' => '10:00', 'closing' => '20:00'],
    ['day' => 3, 'opening' => '10:00', 'closing' => '20:00'],
    ['day' => 4, 'opening' => '10:00', 'closing' => '20:00'],
    ['day' => 5, 'opening' => '10:00', 'closing' => '20:00'],
    ['day' => 6, 'opening' => '10:00', 'closing' => '18:00'],
    ['day' => 0, 'opening' => '11:00', 'closing' => '18:00'],
];

print_r(process_working_hours($working_hours));

?>
