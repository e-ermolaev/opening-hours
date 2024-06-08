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

## Usage

To use the script, provide it with an array of working hours in the specified format. Here is an example:

### Example Working Hours Array

```php
$working_hours = [
    ['day' => 1, 'opening' => '08:00', 'closing' => '21:00'],
    ['day' => 2, 'opening' => '10:00', 'closing' => '20:00'],
    ['day' => 3, 'opening' => '10:00', 'closing' => '20:00'],
    ['day' => 4, 'opening' => '10:00', 'closing' => '20:00'],
    ['day' => 5, 'opening' => '10:00', 'closing' => '20:00'],
    ['day' => 6, 'opening' => '10:00', 'closing' => '18:00'],
    ['day' => 0, 'opening' => '11:00', 'closing' => '18:00'],
];
