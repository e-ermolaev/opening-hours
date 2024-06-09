<?php

/**
 * Processes an array of working hours for a salon and returns a formatted result.
 *
 * @param array $working_hours An array of working hours for each day of the week.
 * @param array $exceptions An array of exceptions for specific dates or recurring dates.
 * @return array A formatted result containing today's opening and closing times, grouped working hours, and exceptions.
 */
function process_working_hours($working_hours, $exceptions) {
    // Get the current day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
    $current_day = date('w');
    // Get the current time in 'H:i' format
    $current_time = date('H:i');
    // Get the current date
    $current_date = date('Y-m-d');

    // Initialize the resulting array
    $result = [
        'today' => [],
        'working_hours' => [],
        'exceptions' => []
    ];

    // Array to store grouped working hours
    $grouped_hours = [];
    // Array of day names
    $days_of_week = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Group working hours by opening and closing times
    foreach ($working_hours as $hours) {
        $key = json_encode($hours['ranges']);
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

        // Decode the JSON key to get ranges
        $ranges = json_decode($key, true);

        // Create a label for the ranges
        $ranges_label = implode(', ', array_map(function($range) {
            return $range['opening'] . '-' . $range['closing'];
        }, $ranges));

        // Add to the resulting array
        $result['working_hours'][] = [
            'days_label' => $days_label,
            'ranges' => $ranges,
            'label' => $days_label . ' from ' . $ranges_label
        ];
    }

    // Check for exceptions for today and upcoming exceptions
    $today_exception = null;
    $upcoming_exceptions = [];
    foreach ($exceptions as $exception_date => $exception_ranges) {
        // Handle recurring exceptions (MM-DD format)
        if (strlen($exception_date) == 5) {
            $exception_date = date('Y') . '-' . $exception_date;
        }
        if ($exception_date == $current_date) {
            $today_exception = $exception_ranges;
        } elseif (strtotime($exception_date) >= strtotime($current_date) && strtotime($exception_date) <= strtotime($current_date . ' + 5 days')) {
            $upcoming_exceptions[$exception_date] = $exception_ranges;
        }
    }
    $result['exceptions'] = $upcoming_exceptions;

    // Helper function to determine if current time is within a range
    function is_time_in_range($start, $end, $current) {
        if ($start < $end) {
            return $current >= $start && $current <= $end;
        } else {
            return $current >= $start || $current <= $end;
        }
    }

    // If there is an exception for today, use it and check the status
    if ($today_exception !== null) {
        $status = 'closed';
        $label = '';
        foreach ($today_exception as $range) {
            $opening = $range['opening'];
            $closing = $range['closing'];
            // Check if the current time is within the range
            if (is_time_in_range($opening, $closing, $current_time)) {
                $status = 'open';
                $label = 'until ' . $closing;
                break;
            } elseif ($current_time < $opening) {
                $status = 'closed';
                $label = 'from ' . $opening;
                break;
            }
        }
        $result['today'] = [
            'opening' => $opening,
            'closing' => $closing,
            'status' => $status,
            'label' => $label
        ];
    } else {
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
            $status = 'closed';
            $label = '';
            foreach ($today_hours['ranges'] as $range) {
                $opening = $range['opening'];
                $closing = $range['closing'];
                // Check if the current time is within the range
                if (is_time_in_range($opening, $closing, $current_time)) {
                    $status = 'open';
                    $label = 'until ' . $closing;
                    break;
                } elseif ($current_time < $opening) {
                    $status = 'closed';
                    $label = 'from ' . $opening;
                    break;
                }
            }

            $result['today'] = [
                'opening' => $opening,
                'closing' => $closing,
                'status' => $status,
                'label' => $label
            ];
        }
    }

    // If the salon is closed, find the next opening time
    if ($result['today']['status'] === 'closed') {
        $next_opening = null;
        $next_opening_day = null;

        // First check today's opening hours for upcoming opening time
        foreach ($today_hours['ranges'] as $range) {
            if ($current_time < $range['opening']) {
                $next_opening = $range['opening'];
                $next_opening_day = $current_day;
                break;
            }
        }

        // If no opening time is found for today, loop through the next 7 days to find the next opening time
        if (!$next_opening) {
            for ($i = 1; $i <= 7; $i++) {
                $check_day = ($current_day + $i) % 7;
                foreach ($working_hours as $hours) {
                    if ($hours['day'] == $check_day) {
                        $next_opening = $hours['ranges'][0]['opening'];
                        $next_opening_day = $check_day;
                        break 2;
                    }
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
    ['day' => 1, 'ranges' => [['opening' => '10:00', 'closing' => '20:00']]],
    ['day' => 2, 'ranges' => [['opening' => '10:00', 'closing' => '20:00']]],
    ['day' => 3, 'ranges' => [['opening' => '10:00', 'closing' => '13:00'],['opening' => '14:00', 'closing' => '20:00']]],
    ['day' => 4, 'ranges' => [['opening' => '10:00', 'closing' => '20:00']]],
    ['day' => 5, 'ranges' => [['opening' => '10:00', 'closing' => '20:00']]],
    ['day' => 6, 'ranges' => [['opening' => '10:00', 'closing' => '18:00']]],
    ['day' => 0, 'ranges' => [['opening' => '10:00', 'closing' => '11:20'],['opening' => '12:00', 'closing' => '18:00']]]
];

$exceptions = [
    '2024-11-11' => [['opening' => '09:00', 'closing' => '12:00']],
    '2024-12-25' => ['message' => 'Merry Christmas!'],
    '01-01'      => ['message' => 'Happy New Year!'],
    '06-09'      => [['opening' => '09:00', 'closing' => '12:00'], ['opening' => '13:00', 'closing' => '18:00'], 'message' => 'Some message for this day']
];

print_r(process_working_hours($working_hours, $exceptions));

?>
