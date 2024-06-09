# Shop Working Hours Processor

This PHP script processes an array of working hours for a salon and returns a formatted result containing today's opening and closing times, as well as grouped working hours for display. It also determines the next opening time if the salon is currently closed.

## Key Features

1. **Current Day and Time Check**:
   - Retrieves the current day of the week and time.
   - Determines the salon's status (open/closed) and generates a label for today’s hours.

2. **Grouping Working Hours**:
   - Groups days with identical opening and closing times.
   - Consolidates consecutive days into a range (e.g., "Mon-Wed" instead of "Mon, Tue, Wed").

3. **Result Formatting**:
   - Produces a readable format for grouped working hours.
   - Includes a label for each group indicating the days and their respective opening and closing times.

4. **Next Opening Time**:
   - If the salon is closed, calculates the next opening time and day.
   - Adds this information to the result for display.

- Groups working hours by days and time ranges.
- Identifies consecutive days and formats them with a range.
- Handles exceptions for specific dates and recurring dates.
- Provides today's status (open/closed) with appropriate labels.
- Indicates the next opening time if the shop is currently closed.
- Adds upcoming exceptions within the next 5 days to the result.

## Usage

To use the script, provide it with an array of working hours in the specified format. Here is an example:

### Example Working Hours Array

```php
// Example usage of the function
$working_hours = [
    ['_type' => '_', 'day' => 1, 'ranges' => [['opening' => '10:00', 'closing' => '20:00']]],
    ['_type' => '_', 'day' => 2, 'ranges' => [['opening' => '10:00', 'closing' => '20:00']]],
    ['_type' => '_', 'day' => 3, 'ranges' => [['opening' => '10:00', 'closing' => '13:00'],['opening' => '14:00', 'closing' => '20:00']]],
    ['_type' => '_', 'day' => 4, 'ranges' => [['opening' => '10:00', 'closing' => '20:00']]],
    ['_type' => '_', 'day' => 5, 'ranges' => [['opening' => '10:00', 'closing' => '20:00']]],
    ['_type' => '_', 'day' => 6, 'ranges' => [['opening' => '10:00', 'closing' => '18:00']]],
    ['_type' => '_', 'day' => 0, 'ranges' => [['opening' => '10:00', 'closing' => '11:20'],['opening' => '12:00', 'closing' => '18:00']]]
];

$exceptions = [
    ['date' => '01-01', 'ranges' => [], 'message' => 'Happy New Year!'],
    ['date' => '2024-06-09', 'ranges' => [['opening' => '09:00', 'closing' => '12:00:30'],['opening' => '13:00', 'closing' => '18:00']], 'message' => 'Some message for this day' ]
];

```

### Expected Output
The function will return an array containing today's opening and closing times and grouped working hours for display. For example:

```php
print_r(process_working_hours($working_hours, $exceptions));
(
    [today] => Array
        (
            [opening] => 10:00
            [closing] => 11:20
            [status] => open
            [label] => until 11:20
        )

    [working_hours] => Array
        (
            [0] => Array
                (
                    [days_label] => Mon-Tue, Thu-Fri
                    [ranges] => Array
                        (
                            [0] => Array
                                (
                                    [opening] => 10:00
                                    [closing] => 20:00
                                )

                        )

                    [label] => Mon-Tue, Thu-Fri from 10:00-20:00
                )

            [1] => Array
                (
                    [days_label] => Wed
                    [ranges] => Array
                        (
                            [0] => Array
                                (
                                    [opening] => 10:00
                                    [closing] => 13:00
                                )

                            [1] => Array
                                (
                                    [opening] => 14:00
                                    [closing] => 20:00
                                )

                        )

                    [label] => Wed from 10:00-13:00, 14:00-20:00
                )

            [2] => Array
                (
                    [days_label] => Sat
                    [ranges] => Array
                        (
                            [0] => Array
                                (
                                    [opening] => 10:00
                                    [closing] => 18:00
                                )

                        )

                    [label] => Sat from 10:00-18:00
                )

            [3] => Array
                (
                    [days_label] => Sun
                    [ranges] => Array
                        (
                            [0] => Array
                                (
                                    [opening] => 10:00
                                    [closing] => 11:20
                                )

                            [1] => Array
                                (
                                    [opening] => 12:00
                                    [closing] => 18:00
                                )

                        )

                    [label] => Sun from 10:00-11:20, 12:00-18:00
                )

        )

    [exceptions] => Array
        (
            [2024-06-10] => Array
                (
                    [0] => Array
                        (
                            [opening] => 09:00
                            [closing] => 12:00
                        )

                    [1] => Array
                        (
                            [opening] => 13:00
                            [closing] => 18:00
                        )

                    [message] => Some message for this day
                )

        )

)
```
