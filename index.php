<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Antenna Alignment Calculator</title>
    <style>
        :root {
            --bg-primary: #f4f4f4;
            --bg-secondary: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
            --border-radius: 8px;
            --input-border: #d3d3d3;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .calculator-container {
            background-color: var(--bg-secondary);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
        }

        label {
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }

        input[type="text"], input[type="number"] {
            padding: 0.5rem;
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .results {
            margin-top: 1rem;
            border-top: 1px solid var(--input-border);
            padding-top: 1rem;
        }

        .results h2 {
            color: var(--text-primary);
        }

        .results p {
            color: var(--text-secondary);
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="calculator-container">
    <h1>WiFi Antenna Alignment Calculator</h1>
    <form method="post" action="index.php">
        <!-- Antenna 1 -->
        <div class="form-group">
            <label for="lat1">Latitude Antenna 1:</label>
            <input type="text" name="lat1" value="<?= htmlspecialchars($_POST['lat1'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="lon1">Longitude Antenna 1:</label>
            <input type="text" name="lon1" value="<?= htmlspecialchars($_POST['lon1'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="height1">Height above sea level (meters) Antenna 1:</label>
            <input type="number" step="0.01" name="height1" value="<?= htmlspecialchars($_POST['height1'] ?? '') ?>" required>
        </div>

        <!-- Antenna 2 -->
        <div class="form-group">
            <label for="lat2">Latitude Antenna 2:</label>
            <input type="text" name="lat2" value="<?= htmlspecialchars($_POST['lat2'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="lon2">Longitude Antenna 2:</label>
            <input type="text" name="lon2" value="<?= htmlspecialchars($_POST['lon2'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="height2">Height above sea level (meters) Antenna 2:</label>
            <input type="number" step="0.01" name="height2" value="<?= htmlspecialchars($_POST['height2'] ?? '') ?>" required>
        </div>

        <input type="submit" value="Calculate">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
 /*       function deg2rad($deg) { return $deg * pi() / 180; }
        function rad2deg($rad) { return $rad * 180 / pi(); }
*/
        function calculateDistance($lat1, $lon1, $lat2, $lon2) {
            $earthRadius = 6371000; // meters
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
            $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            return $earthRadius * $c; // distance in meters
        }

        function calculateAzimuth($lat1, $lon1, $lat2, $lon2) {
            $dLon = deg2rad($lon2 - $lon1);
            $y = sin($dLon) * cos(deg2rad($lat2));
            $x = cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos($dLon);
            return fmod(rad2deg(atan2($y, $x)) + 360, 360);
        }

        function calculateElevation($distance, $height1, $height2) {
            return rad2deg(atan2($height2 - $height1, $distance));
        }

        // Gather form data
        $lat1 = $_POST['lat1'];
        $lon1 = $_POST['lon1'];
        $height1 = $_POST['height1'];
        $lat2 = $_POST['lat2'];
        $lon2 = $_POST['lon2'];
        $height2 = $_POST['height2'];

        // Perform calculations
        $distance = calculateDistance($lat1, $lon1, $lat2, $lon2);
        $azimuth1to2 = calculateAzimuth($lat1, $lon1, $lat2, $lon2);
        $azimuth2to1 = calculateAzimuth($lat2, $lon2, $lat1, $lon1); // Reverse direction
        $elevation1to2 = calculateElevation($distance, $height1, $height2);
        $elevation2to1 = calculateElevation($distance, $height2, $height1); // Reverse height difference

// Display results
echo '<div class="results">';

// Displaying original inputs
echo '<h3>Original Inputs:</h3>';
echo '<ul style="list-style-type: none; padding-left: 0;">';
echo '<li><strong>Antenna 1:</strong></li>';
echo '<ul>';
echo '<li>Latitude: ' . htmlspecialchars($lat1) . '</li>';
echo '<li>Longitude: ' . htmlspecialchars($lon1) . '</li>';
echo '<li>Height: ' . htmlspecialchars($height1) . ' meters</li>';
echo '</ul>';
echo '<li><strong>Antenna 2:</strong></li>';
echo '<ul>';
echo '<li>Latitude: ' . htmlspecialchars($lat2) . '</li>';
echo '<li>Longitude: ' . htmlspecialchars($lon2) . '</li>';
echo '<li>Height: ' . htmlspecialchars($height2) . ' meters</li>';
echo '</ul>';
echo '</ul>';

echo '<h2>Results:</h2>';
echo '<strong>Antenna 1 to Antenna 2:</strong><br>';
echo 'Azimuth: ' . number_format($azimuth1to2, 2) . ' degrees<br>';
echo 'Elevation: ' . number_format($elevation1to2, 2) . ' degrees<br>';

echo '<br><strong>Antenna 2 to Antenna 1:</strong><br>';
echo 'Azimuth: ' . number_format($azimuth2to1, 2) . ' degrees<br>';
echo 'Elevation: ' . number_format($elevation2to1, 2) . ' degrees<br>';
        
echo '<br><strong>Distance calculation details:</strong><br>';
echo 'Height Difference: ' . number_format($height2 - $height1, 2) . ' meters<br>';
echo 'Distance: ' . number_format($distance, 2) . ' meters<br>';

echo '<br><strong>Calculation Formulas:</strong><br>';
echo '<pre style="font-family: \'Times New Roman\', serif; text-align: left; line-height: 1.5; margin: 1em 0; padding: 1em; background-color: #f9f9f9; border: 1px solid #e3e3e3; border-radius: var(--border-radius);">';
echo '1. Distance:
   ΔLat = lat2 - lat1
   ΔLon = lon2 - lon1
   a = sin²(ΔLat/2) + cos(lat1) * cos(lat2) * sin²(ΔLon/2)
   c = 2 * atan2(√a, √(1-a))
   Distance = EarthRadius * c

2. Azimuth:
   ΔLon = lon2 - lon1
   y = sin(ΔLon) * cos(lat2)
   x = cos(lat1) * sin(lat2) - sin(lat1) * cos(lat2) * cos(ΔLon)
   Azimuth = (atan2(y, x) * 180/π + 360) % 360

3. Elevation:
   Elevation = atan2(ΔHeight, Distance) * 180/π

</pre>';

echo '<strong>Calculation Steps:</strong><br>';

// Antenna 1 to Antenna 2
echo '<h4>From Antenna 1 to Antenna 2:</h4>';
echo '<ol style="list-style-type: decimal; padding-left: 20px;">';

$dLat_1to2 = deg2rad($lat2 - $lat1);
$dLon_1to2 = deg2rad($lon2 - $lon1);

// Distance Calculation
$a_1to2 = sin($dLat_1to2/2) * sin($dLat_1to2/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon_1to2/2) * sin($dLon_1to2/2);
$c_1to2 = 2 * atan2(sqrt($a_1to2), sqrt(1-$a_1to2));

echo '<li>Convert degrees to radians for calculations</li>';
echo '<li>Compute ΔLat and ΔLon in radians:</li>';
echo '<ul>';
echo '<li>ΔLat = ' . number_format($dLat_1to2, 5) . '</li>';
echo '<li>ΔLon = ' . number_format($dLon_1to2, 5) . '</li>';
echo '</ul>';

echo '<li>Use Haversine formula for distance:</li>';
echo '<ul>';
echo '<li>a = sin²(' . number_format($dLat_1to2/2, 5) . ') + cos(' . number_format(deg2rad($lat1), 5) . ') * cos(' . number_format(deg2rad($lat2), 5) . ') * sin²(' . number_format($dLon_1to2/2, 5) . ') = ' . number_format($a_1to2, 5) . '</li>';
echo '<li>c = 2 * atan2(√' . number_format($a_1to2, 5) . ', √' . number_format(1-$a_1to2, 5) . ') = ' . number_format($c_1to2, 5) . '</li>';
echo '<li>Distance = 6371000 * ' . number_format($c_1to2, 5) . ' = ' . number_format($distance, 2) . ' meters</li>';
echo '</ul>';

// Azimuth Calculation
$y_1to2 = sin($dLon_1to2) * cos(deg2rad($lat2));
$x_1to2 = cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos($dLon_1to2);
echo '<li>Azimuth calculation:</li>';
echo '<ul>';
echo '<li>y = sin(' . number_format($dLon_1to2, 5) . ') * cos(' . number_format(deg2rad($lat2), 5) . ') = ' . number_format($y_1to2, 5) . '</li>';
echo '<li>x = cos(' . number_format(deg2rad($lat1), 5) . ') * sin(' . number_format(deg2rad($lat2), 5) . ') - sin(' . number_format(deg2rad($lat1), 5) . ') * cos(' . number_format(deg2rad($lat2), 5) . ') * cos(' . number_format($dLon_1to2, 5) . ') = ' . number_format($x_1to2, 5) . '</li>';
echo '<li>Azimuth = (atan2(' . number_format($y_1to2, 5) . ', ' . number_format($x_1to2, 5) . ') * 180/π + 360) % 360 = ' . number_format($azimuth1to2, 2) . ' degrees</li>';
echo '</ul>';

// Elevation Calculation
$heightDiff_1to2 = $height2 - $height1;
echo '<li>Elevation calculation:</li>';
echo '<ul>';
echo '<li>Elevation = atan2(' . number_format($heightDiff_1to2, 2) . ', ' . number_format($distance, 2) . ') * 180/π = ' . number_format($elevation1to2, 2) . ' degrees</li>';
echo '</ul>';

echo '</ol>';

// Antenna 2 to Antenna 1
echo '<h4>From Antenna 2 to Antenna 1:</h4>';
echo '<ol style="list-style-type: decimal; padding-left: 20px;">';

$dLat_2to1 = deg2rad($lat1 - $lat2);
$dLon_2to1 = deg2rad($lon1 - $lon2);

// Distance Calculation - This will be the same as above, so we can just reuse $distance

echo '<li>Convert degrees to radians for calculations</li>';
echo '<li>Compute ΔLat and ΔLon in radians:</li>';
echo '<ul>';
echo '<li>ΔLat = ' . number_format($dLat_2to1, 5) . '</li>';
echo '<li>ΔLon = ' . number_format($dLon_2to1, 5) . '</li>';
echo '</ul>';

echo '<li>Use Haversine formula for distance:</li>';
echo '<ul>';
echo '<li>This step is identical to the previous direction since distance is the same for both directions.</li>';
echo '</ul>';

// Azimuth Calculation
$y_2to1 = sin($dLon_2to1) * cos(deg2rad($lat1));
$x_2to1 = cos(deg2rad($lat2)) * sin(deg2rad($lat1)) - sin(deg2rad($lat2)) * cos(deg2rad($lat1)) * cos($dLon_2to1);
echo '<li>Azimuth calculation:</li>';
echo '<ul>';
echo '<li>y = sin(' . number_format($dLon_2to1, 5) . ') * cos(' . number_format(deg2rad($lat1), 5) . ') = ' . number_format($y_2to1, 5) . '</li>';
echo '<li>x = cos(' . number_format(deg2rad($lat2), 5) . ') * sin(' . number_format(deg2rad($lat1), 5) . ') - sin(' . number_format(deg2rad($lat2), 5) . ') * cos(' . number_format(deg2rad($lat1), 5) . ') * cos(' . number_format($dLon_2to1, 5) . ') = ' . number_format($x_2to1, 5) . '</li>';
echo '<li>Azimuth = (atan2(' . number_format($y_2to1, 5) . ', ' . number_format($x_2to1, 5) . ') * 180/π + 360) % 360 = ' . number_format($azimuth2to1, 2) . ' degrees</li>';
echo '</ul>';

// Elevation Calculation
$heightDiff_2to1 = $height1 - $height2;
echo '<li>Elevation calculation:</li>';
echo '<ul>';
echo '<li>Elevation = atan2(' . number_format($heightDiff_2to1, 2) . ', ' . number_format($distance, 2) . ') * 180/π = ' . number_format($elevation2to1, 2) . ' degrees</li>';
echo '</ul>';

echo '</ol>';
echo '</div>';
    }
    ?>
</div>

</body>
</html>